package main

import (
	"bytes"
	"context"
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strconv"
	"strings"
	"sync"
	"time"

	"github.com/gofiber/fiber/v2"
)

// idpClient talks to the IdP (Auténtico) management API for provisioning,
// one-time setup links, and username renames. Auténtico's admin API is protected
// by an admin OIDC access token (audience autentico-admin), so the client mints
// one via the password grant on the admin client and caches it. A static
// IDP_ADMIN_TOKEN, if set, takes precedence (externally-managed token).
// See docs/AUTH-DESIGN.md.
type idpClient struct {
	baseURL string
	http    *http.Client

	// Admin OIDC token (password grant on the admin client).
	tokenURL      string
	adminClientID string
	adminUsername string
	adminPassword string
	staticToken   string

	mu       sync.Mutex
	token    string
	tokenExp time.Time
}

func newIDPClient(cfg Config) *idpClient {
	tokenURL := cfg.IdPTokenURL
	if tokenURL == "" && cfg.OIDCIssuer != "" {
		tokenURL = cfg.OIDCIssuer + "/token"
	}
	return &idpClient{
		baseURL:       cfg.IdPAdminBaseURL,
		http:          &http.Client{Timeout: 15 * time.Second},
		tokenURL:      tokenURL,
		adminClientID: cfg.IdPAdminClientID,
		adminUsername: cfg.IdPAdminUsername,
		adminPassword: cfg.IdPAdminPassword,
		staticToken:   cfg.IdPAdminToken,
	}
}

// configured reports whether the admin API can be called: a base URL plus either
// a static token or admin credentials to mint one.
func (c *idpClient) configured() bool {
	if c.baseURL == "" {
		return false
	}
	if c.staticToken != "" {
		return true
	}
	return c.adminUsername != "" && c.adminPassword != "" && c.tokenURL != ""
}

// adminToken returns a valid admin bearer token, minting and caching one via the
// password grant when needed. A static token, if configured, is used as-is.
func (c *idpClient) adminToken(ctx context.Context) (string, error) {
	if c.staticToken != "" {
		return c.staticToken, nil
	}
	c.mu.Lock()
	defer c.mu.Unlock()
	if c.token != "" && time.Now().Before(c.tokenExp) {
		return c.token, nil
	}
	form := url.Values{}
	form.Set("grant_type", "password")
	form.Set("client_id", c.adminClientID)
	form.Set("username", c.adminUsername)
	form.Set("password", c.adminPassword)
	form.Set("scope", "openid")
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, c.tokenURL, strings.NewReader(form.Encode()))
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	req.Header.Set("Accept", "application/json")
	resp, err := c.http.Do(req)
	if err != nil {
		return "", err
	}
	defer resp.Body.Close()
	raw, _ := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return "", fmt.Errorf("idp admin token: status %d: %s", resp.StatusCode, strings.TrimSpace(string(raw)))
	}
	var tr struct {
		AccessToken string `json:"access_token"`
		ExpiresIn   int    `json:"expires_in"`
	}
	if err := json.Unmarshal(raw, &tr); err != nil {
		return "", err
	}
	if tr.AccessToken == "" {
		return "", errors.New("idp admin token: empty access_token")
	}
	ttl := tr.ExpiresIn
	if ttl <= 0 {
		ttl = 300
	}
	c.token = tr.AccessToken
	// Refresh a little early to avoid using a token that expires mid-request.
	c.tokenExp = time.Now().Add(time.Duration(ttl)*time.Second - 30*time.Second)
	return c.token, nil
}

func (c *idpClient) do(ctx context.Context, method, path string, body any) (map[string]any, error) {
	var reader io.Reader
	if body != nil {
		b, err := json.Marshal(body)
		if err != nil {
			return nil, err
		}
		reader = bytes.NewReader(b)
	}
	req, err := http.NewRequestWithContext(ctx, method, c.baseURL+path, reader)
	if err != nil {
		return nil, err
	}
	req.Header.Set("Accept", "application/json")
	if body != nil {
		req.Header.Set("Content-Type", "application/json")
	}
	token, err := c.adminToken(ctx)
	if err != nil {
		return nil, fmt.Errorf("idp admin auth: %w", err)
	}
	if token != "" {
		req.Header.Set("Authorization", "Bearer "+token)
	}
	resp, err := c.http.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()
	raw, _ := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	if resp.StatusCode == http.StatusNotFound || resp.StatusCode == http.StatusMethodNotAllowed || resp.StatusCode == http.StatusNotImplemented {
		return nil, errUnsupported
	}
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return nil, fmt.Errorf("idp %s %s: status %d: %s", method, path, resp.StatusCode, strings.TrimSpace(string(raw)))
	}
	out := map[string]any{}
	if len(raw) > 0 {
		_ = json.Unmarshal(raw, &out)
	}
	return out, nil
}

var errUnsupported = errors.New("idp endpoint not supported")

// CreateUser provisions an IdP account and returns its stable id (the OIDC
// subject). password is a throwaway high-entropy bootstrap secret; the user
// never sees it and sets their own password via the setup link.
func (c *idpClient) CreateUser(ctx context.Context, username, password, email string) (string, error) {
	payload := map[string]any{"username": username, "password": password}
	if email != "" {
		payload["email"] = email
	}
	out, err := c.do(ctx, http.MethodPost, "/admin/api/users", payload)
	if err != nil {
		return "", err
	}
	if id := digString(out, "id"); id != "" {
		return id, nil
	}
	return "", fmt.Errorf("idp create user: response missing user id: %v", out)
}

func (c *idpClient) SetupPasswordLink(ctx context.Context, subject string) (string, string, error) {
	out, err := c.do(ctx, http.MethodPost, "/admin/api/users/"+subject+"/setup-password-link", nil)
	if err != nil {
		return "", "", err
	}
	setupURL := digString(out, "setup_url")
	expiresAt := digString(out, "expires_at")
	if setupURL == "" {
		return "", "", fmt.Errorf("idp setup-password-link: response missing setup_url: %v", out)
	}
	return setupURL, expiresAt, nil
}

func (c *idpClient) RenameUser(ctx context.Context, subject, newUsername string) error {
	_, err := c.do(ctx, http.MethodPatch, "/admin/api/users/"+subject, map[string]any{"username": newUsername})
	return err
}

// digString pulls a string field that may live at the top level or nested under
// a "data" envelope (Auténtico wraps responses as {"data": {...}}).
func digString(m map[string]any, key string) string {
	if v, ok := m[key]; ok {
		return coerceString(v)
	}
	if data, ok := m["data"].(map[string]any); ok {
		if v, ok := data[key]; ok {
			return coerceString(v)
		}
	}
	return ""
}

func coerceString(v any) string {
	switch t := v.(type) {
	case string:
		return t
	case float64:
		return strconv.FormatInt(int64(t), 10)
	default:
		return ""
	}
}

type provisionResult struct {
	Subject   string
	SetupURL  string
	ExpiresAt string
}

type provisionUserRequest struct {
	Username     string `json:"username"`
	Email        string `json:"email"`
	NamaDepan    string `json:"nama_depan"`
	NamaBelakang string `json:"nama_belakang"`
	RoleID       int64  `json:"role_id"`
	SektorID     *int64 `json:"sektor_id"`
}

// provisionUser creates the HKBP users row first (pending_idp), then the matching
// IdP account, links the returned subject, marks the user active, and returns a
// one-time setup link. On IdP failure the row is marked failed_idp for retry.
func (s *Server) provisionUser(c *fiber.Ctx) error {
	var req provisionUserRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	username := strings.TrimSpace(req.Username)
	namaDepan := strings.TrimSpace(req.NamaDepan)
	if username == "" || namaDepan == "" || req.RoleID == 0 {
		return badRequest(c, "username, nama_depan, and role_id are required")
	}
	if !s.idp.configured() {
		return c.Status(fiber.StatusNotImplemented).JSON(fiber.Map{
			"error": "IdP admin API not configured; set IDP_ADMIN_BASE_URL and IDP_ADMIN_TOKEN"})
	}
	actor := localUserPtr(c)
	ip := clientIP(c)
	email := strings.TrimSpace(req.Email)

	res, err := s.db.Exec(`INSERT INTO users (username, email, nama_depan, nama_belakang, role_id, sektor_id, status, provisioning_status, session_version)
		VALUES (?, ?, ?, ?, ?, ?, 'active', 'pending_idp', 1)`,
		username, nullable(email), namaDepan, nilIfEmpty(req.NamaBelakang), req.RoleID, req.SektorID)
	if err != nil {
		return badRequest(c, err.Error())
	}
	userID, _ := res.LastInsertId()

	result, perr := s.provisionIdP(c.Context(), userID, username, email)
	if perr != nil {
		_, _ = s.db.Exec("UPDATE users SET provisioning_status = 'failed_idp', updated_at = datetime('now') WHERE id = ?", userID)
		s.audit(s.db, EventUserProvisioned, actor, "user", strconv.FormatInt(userID, 10),
			map[string]any{"status": "failed_idp", "error": perr.Error()}, ip)
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{
			"error":  "IdP provisioning failed; user marked failed_idp and can be retried",
			"detail": perr.Error(), "user_id": userID, "provisioning_status": "failed_idp"})
	}

	s.audit(s.db, EventUserProvisioned, actor, "user", strconv.FormatInt(userID, 10),
		map[string]any{"status": "active", "subject": result.Subject}, ip)
	s.audit(s.db, EventSetupLinkCreated, actor, "user", strconv.FormatInt(userID, 10), nil, ip)
	return c.Status(fiber.StatusCreated).JSON(fiber.Map{
		"user_id":             userID,
		"provisioning_status": "active",
		"setup_url":           result.SetupURL,
		"expires_at":          result.ExpiresAt,
	})
}

// provisionIdP creates the IdP account, links the identity, marks the user
// active, and fetches a one-time setup link. The bootstrap password is random
// and never stored or returned.
func (s *Server) provisionIdP(ctx context.Context, userID int64, username, email string) (provisionResult, error) {
	var result provisionResult
	bootstrap, err := randomToken()
	if err != nil {
		return result, err
	}
	subject, err := s.idp.CreateUser(ctx, username, bootstrap, email)
	if err != nil {
		return result, err
	}
	result.Subject = subject

	tx, err := s.db.Begin()
	if err != nil {
		return result, err
	}
	if _, err := tx.Exec(`INSERT INTO user_identities (user_id, issuer, subject, preferred_username, email, email_verified)
		VALUES (?, ?, ?, ?, ?, 0)
		ON CONFLICT(issuer, subject) DO UPDATE SET user_id = excluded.user_id, preferred_username = excluded.preferred_username, updated_at = datetime('now')`,
		userID, s.cfg.OIDCIssuer, subject, username, nullable(email)); err != nil {
		_ = tx.Rollback()
		return result, err
	}
	if _, err := tx.Exec("UPDATE users SET provisioning_status = 'active', updated_at = datetime('now') WHERE id = ?", userID); err != nil {
		_ = tx.Rollback()
		return result, err
	}
	if err := tx.Commit(); err != nil {
		return result, err
	}

	// Provisioned successfully; a failed setup-link is non-fatal (it can be
	// regenerated via POST /users/:id/setup-link).
	setupURL, expiresAt, linkErr := s.idp.SetupPasswordLink(ctx, subject)
	if linkErr == nil {
		result.SetupURL = setupURL
		result.ExpiresAt = expiresAt
	}
	return result, nil
}

func (s *Server) createUserSetupLink(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	if !s.idp.configured() {
		return c.Status(fiber.StatusNotImplemented).JSON(fiber.Map{"error": "IdP admin API not configured"})
	}
	var subject string
	if err := s.db.QueryRow("SELECT subject FROM user_identities WHERE user_id = ? AND issuer = ?", id, s.cfg.OIDCIssuer).Scan(&subject); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return badRequest(c, "user has no linked IdP identity; provision or link first")
		}
		return internalError(c, err)
	}
	setupURL, expiresAt, err := s.idp.SetupPasswordLink(c.Context(), subject)
	if err != nil {
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{"error": "failed to create setup link", "detail": err.Error()})
	}
	s.audit(s.db, EventSetupLinkCreated, localUserPtr(c), "user", strconv.FormatInt(id, 10), nil, clientIP(c))
	return c.JSON(fiber.Map{"setup_url": setupURL, "expires_at": expiresAt})
}

func (s *Server) retryProvisioning(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	if !s.idp.configured() {
		return c.Status(fiber.StatusNotImplemented).JSON(fiber.Map{"error": "IdP admin API not configured"})
	}
	var username, provStatus string
	var email sql.NullString
	if err := s.db.QueryRow("SELECT username, email, provisioning_status FROM users WHERE id = ?", id).Scan(&username, &email, &provStatus); err != nil {
		if errors.Is(err, sql.ErrNoRows) {
			return notFound(c, "user not found")
		}
		return internalError(c, err)
	}
	if provStatus == "active" {
		return badRequest(c, "user is already provisioned")
	}

	result, perr := s.provisionIdP(c.Context(), id, username, email.String)
	if perr != nil {
		_, _ = s.db.Exec("UPDATE users SET provisioning_status = 'failed_idp', updated_at = datetime('now') WHERE id = ?", id)
		s.audit(s.db, EventUserProvisioned, localUserPtr(c), "user", strconv.FormatInt(id, 10),
			map[string]any{"status": "failed_idp", "error": perr.Error(), "retry": true}, clientIP(c))
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{
			"error": "IdP provisioning retry failed", "detail": perr.Error(), "provisioning_status": "failed_idp"})
	}
	s.audit(s.db, EventUserProvisioned, localUserPtr(c), "user", strconv.FormatInt(id, 10),
		map[string]any{"status": "active", "retry": true}, clientIP(c))
	return c.JSON(fiber.Map{"user_id": id, "provisioning_status": "active",
		"setup_url": result.SetupURL, "expires_at": result.ExpiresAt})
}

type renameRequest struct {
	Username string `json:"username"`
}

// renameUser is an auth-sensitive coordinated operation: rename the IdP
// preferred_username first, then update users.username + user_identities, bump
// session_version, and revoke sessions. If the IdP has no rename API, the local
// username is NOT changed; the request is reported as blocked/unsupported and a
// drift item is audited.
func (s *Server) renameUser(c *fiber.Ctx) error {
	id, err := paramID(c)
	if err != nil {
		return badRequest(c, "invalid user id")
	}
	var req renameRequest
	if err := c.BodyParser(&req); err != nil {
		return badRequest(c, "invalid request body")
	}
	newUsername := strings.TrimSpace(req.Username)
	if newUsername == "" {
		return badRequest(c, "username is required")
	}
	actor := localUserPtr(c)
	ip := clientIP(c)

	var oldUsername, subject string
	err = s.db.QueryRow(`SELECT u.username, ui.subject FROM users u
		JOIN user_identities ui ON ui.user_id = u.id AND ui.issuer = ?
		WHERE u.id = ?`, s.cfg.OIDCIssuer, id).Scan(&oldUsername, &subject)
	if errors.Is(err, sql.ErrNoRows) {
		return badRequest(c, "user not found or has no linked IdP identity")
	}
	if err != nil {
		return internalError(c, err)
	}
	if oldUsername == newUsername {
		return c.JSON(fiber.Map{"id": id, "username": newUsername, "status": "unchanged"})
	}

	if !s.cfg.IdPSupportsUsernameRename {
		s.audit(s.db, EventUsernameChangeBlocked, actor, "user", strconv.FormatInt(id, 10),
			map[string]any{"old": oldUsername, "requested": newUsername, "reason": "idp_rename_unsupported"}, ip)
		return c.Status(fiber.StatusNotImplemented).JSON(fiber.Map{
			"error":  "username rename is blocked: the IdP does not expose a supported username rename API",
			"detail": "Set IDP_SUPPORTS_USERNAME_RENAME=true only after confirming the IdP admin rename endpoint exists. Local-only rename is intentionally disallowed to avoid IdP/HKBP drift.",
		})
	}

	if !s.idp.configured() {
		return c.Status(fiber.StatusNotImplemented).JSON(fiber.Map{"error": "IdP admin API not configured"})
	}
	if err := s.idp.RenameUser(c.Context(), subject, newUsername); err != nil {
		s.audit(s.db, EventUsernameChangeBlocked, actor, "user", strconv.FormatInt(id, 10),
			map[string]any{"old": oldUsername, "requested": newUsername, "reason": "idp_rename_failed", "error": err.Error()}, ip)
		return c.Status(fiber.StatusBadGateway).JSON(fiber.Map{
			"error":  "IdP username rename failed; local username left unchanged to avoid drift",
			"detail": err.Error(),
		})
	}

	tx, err := s.db.Begin()
	if err != nil {
		return internalError(c, err)
	}
	if _, err := tx.Exec("UPDATE users SET username = ?, session_version = session_version + 1, updated_at = datetime('now') WHERE id = ?", newUsername, id); err != nil {
		_ = tx.Rollback()
		return badRequest(c, err.Error())
	}
	if _, err := tx.Exec("UPDATE user_identities SET preferred_username = ?, updated_at = datetime('now') WHERE user_id = ? AND issuer = ?", newUsername, id, s.cfg.OIDCIssuer); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	if _, err := tx.Exec("UPDATE app_sessions SET revoked_at = datetime('now') WHERE user_id = ? AND revoked_at IS NULL", id); err != nil {
		_ = tx.Rollback()
		return internalError(c, err)
	}
	s.audit(tx, EventUsernameChanged, actor, "user", strconv.FormatInt(id, 10),
		map[string]any{"old": oldUsername, "new": newUsername}, ip)
	if err := tx.Commit(); err != nil {
		return internalError(c, err)
	}
	return c.JSON(fiber.Map{"id": id, "username": newUsername, "status": "renamed"})
}
