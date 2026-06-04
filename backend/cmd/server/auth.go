package main

import (
	"context"
	"database/sql"
	"errors"
	"net/url"
	"strconv"
	"strings"
	"time"

	"github.com/coreos/go-oidc/v3/oidc"
	"github.com/gofiber/fiber/v2"
	"golang.org/x/oauth2"
)

// sqlTime is the layout SQLite's datetime('now') produces/compares against (UTC).
const sqlTime = "2006-01-02 15:04:05"

// Identity is an OIDC identity link stored in user_identities.
type Identity struct {
	ID                int64
	UserID            int64
	Issuer            string
	Subject           string
	PreferredUsername string
	Email             *string
	EmailVerified     bool
}

// oidcClaims is the subset of ID-token claims HKBP consumes. issuer+subject is
// the automatic identity key; preferred_username is the human handle; email is
// optional supporting metadata only.
type oidcClaims struct {
	Subject           string
	PreferredUsername string
	Email             string
	EmailVerified     bool
}

type sessionContext struct {
	UserID   int64
	RoleID   int64
	RoleName string
	SektorID *int64
}

// ensureOIDC lazily performs OIDC discovery so the server boots (and serves
// /health) even when the IdP is briefly unreachable; login/callback retry here.
func (s *Server) ensureOIDC(ctx context.Context) error {
	s.oidcMu.Lock()
	defer s.oidcMu.Unlock()
	if s.oidcVerifier != nil {
		return nil
	}
	if s.cfg.OIDCIssuer == "" {
		return errors.New("OIDC_ISSUER not configured")
	}
	provider, err := oidc.NewProvider(ctx, s.cfg.OIDCIssuer)
	if err != nil {
		return err
	}
	s.oidcProvider = provider
	s.oidcVerifier = provider.Verifier(&oidc.Config{ClientID: s.cfg.OIDCClientID})
	s.oauth2Config = &oauth2.Config{
		ClientID:     s.cfg.OIDCClientID,
		ClientSecret: s.cfg.OIDCClientSecret,
		Endpoint:     provider.Endpoint(),
		RedirectURL:  s.cfg.OIDCRedirectURL,
		Scopes:       s.cfg.OIDCScopes,
	}
	return nil
}

const (
	cookieState = "hkbp_oauth_state"
	cookieNonce = "hkbp_oauth_nonce"
	cookiePKCE  = "hkbp_oauth_pkce"
)

// authLogin starts the OIDC authorization-code+PKCE flow. The backend owns
// state/nonce/PKCE; transient values are kept in short-lived HTTP-only cookies.
func (s *Server) authLogin(c *fiber.Ctx) error {
	if err := s.ensureOIDC(c.Context()); err != nil {
		return c.Status(fiber.StatusServiceUnavailable).JSON(fiber.Map{"error": "identity provider unavailable", "detail": err.Error()})
	}

	state, err := randomToken()
	if err != nil {
		return internalError(c, err)
	}
	nonce, err := randomToken()
	if err != nil {
		return internalError(c, err)
	}
	verifier := oauth2.GenerateVerifier()

	expires := time.Now().Add(10 * time.Minute)
	s.setTransientCookie(c, cookieState, state, expires)
	s.setTransientCookie(c, cookieNonce, nonce, expires)
	s.setTransientCookie(c, cookiePKCE, verifier, expires)

	authURL := s.oauth2Config.AuthCodeURL(state,
		oidc.Nonce(nonce),
		oauth2.S256ChallengeOption(verifier),
	)
	return c.Redirect(authURL, fiber.StatusFound)
}

// authCallback validates the IdP response, resolves the identity to an action
// (session, or access request + pending page), and redirects back to the SPA.
func (s *Server) authCallback(c *fiber.Ctx) error {
	if err := s.ensureOIDC(c.Context()); err != nil {
		return c.Status(fiber.StatusServiceUnavailable).JSON(fiber.Map{"error": "identity provider unavailable"})
	}

	if e := c.Query("error"); e != "" {
		return c.Redirect(s.cfg.AccessPendingURL+"?error="+url.QueryEscape(e), fiber.StatusFound)
	}

	state := c.Cookies(cookieState)
	nonce := c.Cookies(cookieNonce)
	verifier := c.Cookies(cookiePKCE)
	s.clearTransientCookies(c)

	if state == "" || c.Query("state") != state {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{"error": "invalid oauth state"})
	}
	code := c.Query("code")
	if code == "" {
		return c.Status(fiber.StatusBadRequest).JSON(fiber.Map{"error": "missing authorization code"})
	}

	token, err := s.oauth2Config.Exchange(c.Context(), code, oauth2.VerifierOption(verifier))
	if err != nil {
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{"error": "token exchange failed", "detail": err.Error()})
	}
	rawIDToken, ok := token.Extra("id_token").(string)
	if !ok || rawIDToken == "" {
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{"error": "missing id_token in token response"})
	}
	idToken, err := s.oidcVerifier.Verify(c.Context(), rawIDToken)
	if err != nil {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid id_token", "detail": err.Error()})
	}
	if nonce == "" || idToken.Nonce != nonce {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "invalid nonce"})
	}

	var raw struct {
		PreferredUsername string `json:"preferred_username"`
		Email             string `json:"email"`
		EmailVerified     bool   `json:"email_verified"`
		Name              string `json:"name"`
	}
	if err := idToken.Claims(&raw); err != nil {
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{"error": "failed to parse claims"})
	}
	claims := oidcClaims{
		Subject:           idToken.Subject,
		PreferredUsername: strings.TrimSpace(raw.PreferredUsername),
		Email:             strings.TrimSpace(raw.Email),
		EmailVerified:     raw.EmailVerified,
	}
	if claims.Subject == "" || claims.PreferredUsername == "" {
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{"error": "id_token missing sub or preferred_username"})
	}

	userID, redirect, err := s.resolveCallback(claims, idToken.Issuer, clientIP(c))
	if err != nil {
		return internalError(c, err)
	}
	if userID > 0 {
		if err := s.createSession(c, userID); err != nil {
			return internalError(c, err)
		}
	}
	return c.Redirect(redirect, fiber.StatusFound)
}

// resolveCallback applies the documented matching decisions transactionally and
// returns (sessionUserID, redirectURL). userID == 0 means "no session; redirect
// to the access-pending page". It is the testable core of the callback.
func (s *Server) resolveCallback(claims oidcClaims, issuer, ip string) (int64, string, error) {
	appHome := s.cfg.AppBaseURL + "/"

	tx, err := s.db.Begin()
	if err != nil {
		return 0, "", err
	}
	committed := false
	defer func() {
		if !committed {
			_ = tx.Rollback()
		}
	}()

	// 1. (issuer, subject) matches an existing identity.
	identity, err := identityBySubjectTx(tx, issuer, claims.Subject)
	switch {
	case err == nil:
		user, uerr := userAuthByIDTx(tx, identity.UserID)
		if uerr != nil {
			return 0, "", uerr
		}
		if user.Status == "active" && user.ProvisioningStatus == "active" {
			// Verified email change auto-updates identity metadata (never identity key).
			if claims.EmailVerified && claims.Email != "" && !equalStrPtr(identity.Email, claims.Email) {
				if _, e := tx.Exec(`UPDATE user_identities SET email = ?, email_verified = 1, updated_at = datetime('now') WHERE id = ?`,
					claims.Email, identity.ID); e != nil {
					return 0, "", e
				}
				s.audit(tx, EventIdentityEmailChanged, &identity.UserID, "user_identity", strconv.FormatInt(identity.ID, 10),
					map[string]any{"old": derefStr(identity.Email), "new": claims.Email}, ip)
			}
			if _, e := tx.Exec("UPDATE users SET last_access = datetime('now') WHERE id = ?", user.ID); e != nil {
				return 0, "", e
			}
			s.audit(tx, EventLogin, &user.ID, "user", strconv.FormatInt(user.ID, 10), map[string]any{"issuer": issuer}, ip)
			if e := tx.Commit(); e != nil {
				return 0, "", e
			}
			committed = true
			return user.ID, appHome, nil
		}
		// Linked but disabled → reactivate_user request (cooldown applies).
		if user.Status != "active" {
			if _, e := s.ensureAccessRequestTx(tx, "reactivate_user", issuer, claims, &user.ID, ip); e != nil {
				return 0, "", e
			}
		}
		if e := tx.Commit(); e != nil {
			return 0, "", e
		}
		committed = true
		return 0, s.cfg.AccessPendingURL, nil
	case !errors.Is(err, sql.ErrNoRows):
		return 0, "", err
	}

	// 2. Bootstrap: promote the configured admin email to the first active admin
	//    only while no active admin exists and the email is verified.
	if s.cfg.BootstrapAdminEmail != "" && claims.EmailVerified && strings.EqualFold(claims.Email, s.cfg.BootstrapAdminEmail) {
		var activeAdmins int
		if e := tx.QueryRow(`SELECT COUNT(1) FROM users u JOIN roles r ON r.id = u.role_id
			WHERE r.name = 'admin' AND u.status = 'active' AND u.provisioning_status = 'active'`).Scan(&activeAdmins); e != nil {
			return 0, "", e
		}
		if activeAdmins == 0 {
			userID, e := bootstrapAdminTx(tx, issuer, claims)
			if e != nil {
				return 0, "", e
			}
			s.audit(tx, EventIdentityLinked, &userID, "user", strconv.FormatInt(userID, 10),
				map[string]any{"bootstrap": true, "issuer": issuer}, ip)
			s.audit(tx, EventLogin, &userID, "user", strconv.FormatInt(userID, 10),
				map[string]any{"issuer": issuer, "bootstrap": true}, ip)
			if e := tx.Commit(); e != nil {
				return 0, "", e
			}
			committed = true
			return userID, appHome, nil
		}
	}

	// 3. preferred_username matches an existing HKBP user → link_existing_user
	//    (admin reviews before linking; never silent auto-link).
	var existingUserID int64
	uerr := tx.QueryRow("SELECT id FROM users WHERE username = ?", claims.PreferredUsername).Scan(&existingUserID)
	switch {
	case uerr == nil:
		if _, e := s.ensureAccessRequestTx(tx, "link_existing_user", issuer, claims, &existingUserID, ip); e != nil {
			return 0, "", e
		}
	case errors.Is(uerr, sql.ErrNoRows):
		// 4. No match anywhere → new_user request.
		if _, e := s.ensureAccessRequestTx(tx, "new_user", issuer, claims, nil, ip); e != nil {
			return 0, "", e
		}
	default:
		return 0, "", uerr
	}

	if e := tx.Commit(); e != nil {
		return 0, "", e
	}
	committed = true
	return 0, s.cfg.AccessPendingURL, nil
}

// bootstrapAdminTx creates the first active admin user + identity from the
// authenticated bootstrap identity.
func bootstrapAdminTx(tx *sql.Tx, issuer string, claims oidcClaims) (int64, error) {
	var roleID int64
	if err := tx.QueryRow("SELECT id FROM roles WHERE name = 'admin'").Scan(&roleID); err != nil {
		return 0, err
	}
	res, err := tx.Exec(`INSERT INTO users (username, email, nama_depan, role_id, status, provisioning_status, session_version)
		VALUES (?, ?, ?, ?, 'active', 'active', 1)`,
		claims.PreferredUsername, nullable(claims.Email), claims.PreferredUsername, roleID)
	if err != nil {
		return 0, err
	}
	userID, _ := res.LastInsertId()
	if _, err := tx.Exec(`INSERT INTO user_identities (user_id, issuer, subject, preferred_username, email, email_verified)
		VALUES (?, ?, ?, ?, ?, ?)`,
		userID, issuer, claims.Subject, claims.PreferredUsername, nullable(claims.Email), boolInt(claims.EmailVerified)); err != nil {
		return 0, err
	}
	return userID, nil
}

// createSession mints an opaque server-side session and sets the HTTP-only
// cookie. The cookie value is random; only its SHA-256 hash is stored.
func (s *Server) createSession(c *fiber.Ctx, userID int64) error {
	raw, err := randomToken()
	if err != nil {
		return err
	}
	var version int64
	if err := s.db.QueryRow("SELECT session_version FROM users WHERE id = ?", userID).Scan(&version); err != nil {
		return err
	}
	expires := time.Now().UTC().Add(s.cfg.SessionTTL)
	if _, err := s.db.Exec(`INSERT INTO app_sessions (user_id, token_hash, session_version, user_agent, ip, expires_at)
		VALUES (?, ?, ?, ?, ?, ?)`,
		userID, hashToken(raw), version, nullable(c.Get("User-Agent")), nullable(c.IP()), expires.Format(sqlTime)); err != nil {
		return err
	}
	s.setSessionCookie(c, raw, expires)
	return nil
}

// authenticateSession validates a raw cookie token against the full protected
// request contract: live unexpired unrevoked session, active user, active
// provisioning, matching session_version, and an existing identity link.
func (s *Server) authenticateSession(raw string) (sessionContext, error) {
	var sc sessionContext
	var sektor sql.NullInt64
	err := s.db.QueryRow(`SELECT u.id, u.role_id, r.name, u.sektor_id
		FROM app_sessions a
		JOIN users u ON u.id = a.user_id
		JOIN roles r ON r.id = u.role_id
		WHERE a.token_hash = ?
		  AND a.revoked_at IS NULL
		  AND a.expires_at > datetime('now')
		  AND a.session_version = u.session_version
		  AND u.status = 'active'
		  AND u.provisioning_status = 'active'
		  AND EXISTS (SELECT 1 FROM user_identities ui WHERE ui.user_id = u.id)`,
		hashToken(raw)).Scan(&sc.UserID, &sc.RoleID, &sc.RoleName, &sektor)
	if err != nil {
		return sc, err
	}
	sc.SektorID = int64Ptr(sektor)
	return sc, nil
}

func (s *Server) authMiddleware(c *fiber.Ctx) error {
	raw := c.Cookies(s.cfg.CookieName)
	if raw == "" {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "not authenticated"})
	}
	sc, err := s.authenticateSession(raw)
	if err != nil {
		return c.Status(fiber.StatusUnauthorized).JSON(fiber.Map{"error": "session invalid or expired"})
	}
	c.Locals("user_id", sc.UserID)
	c.Locals("role_id", sc.RoleID)
	c.Locals("role", sc.RoleName)
	if sc.SektorID != nil {
		c.Locals("sektor_id", *sc.SektorID)
	}
	return c.Next()
}

// authLogout revokes the local session, clears the cookie, audits, and returns
// the IdP end-session URL so SSO can't silently re-enter on a shared computer.
func (s *Server) authLogout(c *fiber.Ctx) error {
	raw := c.Cookies(s.cfg.CookieName)
	var userID int64
	if raw != "" {
		_ = s.db.QueryRow("SELECT user_id FROM app_sessions WHERE token_hash = ?", hashToken(raw)).Scan(&userID)
		_, _ = s.db.Exec("UPDATE app_sessions SET revoked_at = datetime('now') WHERE token_hash = ? AND revoked_at IS NULL", hashToken(raw))
	}
	s.clearSessionCookie(c)
	var actor *int64
	if userID > 0 {
		actor = &userID
		s.audit(s.db, EventLogout, actor, "user", strconv.FormatInt(userID, 10), nil, clientIP(c))
	}
	return c.JSON(fiber.Map{
		"logout_url":           s.endSessionURL(),
		"post_logout_redirect": s.cfg.PostLogoutRedirectURL,
	})
}

// endSessionURL builds the RP-initiated logout URL from discovery metadata, or
// falls back to the post-logout redirect if the IdP exposes no end-session
// endpoint.
func (s *Server) endSessionURL() string {
	if s.oidcProvider == nil {
		if err := s.ensureOIDC(context.Background()); err != nil {
			return s.cfg.PostLogoutRedirectURL
		}
	}
	if s.oidcProvider == nil {
		return s.cfg.PostLogoutRedirectURL
	}
	var meta struct {
		EndSession string `json:"end_session_endpoint"`
	}
	if err := s.oidcProvider.Claims(&meta); err != nil || meta.EndSession == "" {
		return s.cfg.PostLogoutRedirectURL
	}
	u, err := url.Parse(meta.EndSession)
	if err != nil {
		return s.cfg.PostLogoutRedirectURL
	}
	q := u.Query()
	q.Set("post_logout_redirect_uri", s.cfg.PostLogoutRedirectURL)
	if s.cfg.OIDCClientID != "" {
		q.Set("client_id", s.cfg.OIDCClientID)
	}
	u.RawQuery = q.Encode()
	return u.String()
}

func (s *Server) identityForUser(userID int64) (*Identity, error) {
	row := s.db.QueryRow(`SELECT id, user_id, issuer, subject, preferred_username, email, email_verified
		FROM user_identities WHERE user_id = ?`, userID)
	return scanIdentity(row)
}

func identityBySubjectTx(tx *sql.Tx, issuer, subject string) (*Identity, error) {
	row := tx.QueryRow(`SELECT id, user_id, issuer, subject, preferred_username, email, email_verified
		FROM user_identities WHERE issuer = ? AND subject = ?`, issuer, subject)
	return scanIdentity(row)
}

func scanIdentity(row scanner) (*Identity, error) {
	var id Identity
	var email sql.NullString
	var verified int
	if err := row.Scan(&id.ID, &id.UserID, &id.Issuer, &id.Subject, &id.PreferredUsername, &email, &verified); err != nil {
		return nil, err
	}
	id.Email = stringPtr(email)
	id.EmailVerified = verified == 1
	return &id, nil
}

func userAuthByIDTx(tx *sql.Tx, id int64) (AuthUser, error) {
	return scanAuthUser(tx.QueryRow(`SELECT u.id, u.username, u.email, u.nama_depan,
		u.nama_belakang, u.role_id, r.name, u.sektor_id, u.status, u.provisioning_status, u.session_version, u.last_access
		FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?`, id))
}

func (s *Server) setSessionCookie(c *fiber.Ctx, value string, expires time.Time) {
	c.Cookie(&fiber.Cookie{
		Name:     s.cfg.CookieName,
		Value:    value,
		Path:     "/",
		Domain:   s.cfg.CookieDomain,
		Expires:  expires,
		HTTPOnly: true,
		Secure:   s.cfg.CookieSecure,
		SameSite: "Lax",
	})
}

func (s *Server) clearSessionCookie(c *fiber.Ctx) {
	c.Cookie(&fiber.Cookie{
		Name:     s.cfg.CookieName,
		Value:    "",
		Path:     "/",
		Domain:   s.cfg.CookieDomain,
		Expires:  time.Now().Add(-time.Hour),
		HTTPOnly: true,
		Secure:   s.cfg.CookieSecure,
		SameSite: "Lax",
	})
}

func (s *Server) setTransientCookie(c *fiber.Ctx, name, value string, expires time.Time) {
	c.Cookie(&fiber.Cookie{
		Name:     name,
		Value:    value,
		Path:     "/",
		Expires:  expires,
		HTTPOnly: true,
		Secure:   s.cfg.CookieSecure,
		SameSite: "Lax",
	})
}

func (s *Server) clearTransientCookies(c *fiber.Ctx) {
	for _, name := range []string{cookieState, cookieNonce, cookiePKCE} {
		c.Cookie(&fiber.Cookie{
			Name:     name,
			Value:    "",
			Path:     "/",
			Expires:  time.Now().Add(-time.Hour),
			HTTPOnly: true,
			Secure:   s.cfg.CookieSecure,
			SameSite: "Lax",
		})
	}
}

func equalStrPtr(p *string, v string) bool {
	return p != nil && *p == v
}

func derefStr(p *string) string {
	if p == nil {
		return ""
	}
	return *p
}
