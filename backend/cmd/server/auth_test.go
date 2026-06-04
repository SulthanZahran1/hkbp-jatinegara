package main

import (
	"database/sql"
	"encoding/json"
	"io"
	"net/http"
	"strings"
	"testing"
	"time"

	"github.com/gofiber/fiber/v2"
	_ "modernc.org/sqlite"
)

func newTestServer(t *testing.T) *Server {
	t.Helper()
	db, err := sql.Open("sqlite", "file:"+t.TempDir()+"/test.db?_pragma=foreign_keys(1)")
	if err != nil {
		t.Fatalf("open db: %v", err)
	}
	db.SetMaxOpenConns(1)
	t.Cleanup(func() { _ = db.Close() })
	if err := runMigrations(db, "../../migrations"); err != nil {
		t.Fatalf("migrations: %v", err)
	}
	cfg := Config{
		OIDCIssuer:       "https://idp.test",
		AppBaseURL:       "https://app.test",
		AccessPendingURL: "https://app.test/access-pending",
		CookieName:       "hkbp_session",
		SessionTTL:       12 * time.Hour,
		RejectedCooldown: 2 * time.Hour,
	}
	return &Server{db: db, cfg: cfg, idp: newIDPClient(cfg)}
}

func roleID(t *testing.T, s *Server, name string) int64 {
	t.Helper()
	var id int64
	if err := s.db.QueryRow("SELECT id FROM roles WHERE name = ?", name).Scan(&id); err != nil {
		t.Fatalf("role %s: %v", name, err)
	}
	return id
}

// createUser inserts a HKBP user and (optionally) a linked identity.
func createUser(t *testing.T, s *Server, username, role, status string, subject string) int64 {
	t.Helper()
	res, err := s.db.Exec(`INSERT INTO users (username, nama_depan, role_id, status, provisioning_status, session_version)
		VALUES (?, ?, ?, ?, 'active', 1)`, username, username, roleID(t, s, role), status)
	if err != nil {
		t.Fatalf("insert user: %v", err)
	}
	id, _ := res.LastInsertId()
	if subject != "" {
		if _, err := s.db.Exec(`INSERT INTO user_identities (user_id, issuer, subject, preferred_username, email, email_verified)
			VALUES (?, ?, ?, ?, NULL, 0)`, id, s.cfg.OIDCIssuer, subject, username); err != nil {
			t.Fatalf("insert identity: %v", err)
		}
	}
	return id
}

func sessionToken(t *testing.T, s *Server, userID int64) string {
	t.Helper()
	raw := "tok-" + strings.ReplaceAll(time.Now().Format("150405.000000000"), ".", "")
	var version int64
	if err := s.db.QueryRow("SELECT session_version FROM users WHERE id = ?", userID).Scan(&version); err != nil {
		t.Fatalf("session_version: %v", err)
	}
	expires := time.Now().UTC().Add(s.cfg.SessionTTL).Format(sqlTime)
	if _, err := s.db.Exec(`INSERT INTO app_sessions (user_id, token_hash, session_version, expires_at)
		VALUES (?, ?, ?, ?)`, userID, hashToken(raw), version, expires); err != nil {
		t.Fatalf("insert session: %v", err)
	}
	return raw
}

func countRows(t *testing.T, s *Server, query string, args ...any) int {
	t.Helper()
	var n int
	if err := s.db.QueryRow(query, args...).Scan(&n); err != nil {
		t.Fatalf("count: %v", err)
	}
	return n
}

func TestResolveCallback_NewUserCreatesAccessRequest(t *testing.T) {
	s := newTestServer(t)
	claims := oidcClaims{Subject: "sub-new", PreferredUsername: "newperson"}

	userID, redirect, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, "1.2.3.4")
	if err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	if userID != 0 {
		t.Fatalf("expected no session (userID 0), got %d", userID)
	}
	if redirect != s.cfg.AccessPendingURL {
		t.Fatalf("expected access-pending redirect, got %s", redirect)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM access_requests WHERE request_type='new_user' AND subject='sub-new'"); got != 1 {
		t.Fatalf("expected 1 new_user request, got %d", got)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM audit_logs WHERE event=?", EventAccessRequestCreated); got != 1 {
		t.Fatalf("expected access_request_created audit, got %d", got)
	}
}

func TestResolveCallback_LinkExistingUser(t *testing.T) {
	s := newTestServer(t)
	existing := createUser(t, s, "amang", "viewer", "active", "") // no identity yet
	claims := oidcClaims{Subject: "sub-amang", PreferredUsername: "amang"}

	userID, redirect, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, "")
	if err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	if userID != 0 || redirect != s.cfg.AccessPendingURL {
		t.Fatalf("expected pending, got user=%d redirect=%s", userID, redirect)
	}
	var reqType string
	var suggested sql.NullInt64
	if err := s.db.QueryRow("SELECT request_type, suggested_user_id FROM access_requests WHERE subject='sub-amang'").Scan(&reqType, &suggested); err != nil {
		t.Fatalf("query request: %v", err)
	}
	if reqType != "link_existing_user" {
		t.Fatalf("expected link_existing_user, got %s", reqType)
	}
	if !suggested.Valid || suggested.Int64 != existing {
		t.Fatalf("expected suggested_user_id=%d, got %v", existing, suggested)
	}
}

func TestResolveCallback_ActiveIdentityLogsIn(t *testing.T) {
	s := newTestServer(t)
	uid := createUser(t, s, "ruth", "viewer", "active", "sub-ruth")
	claims := oidcClaims{Subject: "sub-ruth", PreferredUsername: "ruth"}

	userID, redirect, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, "")
	if err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	if userID != uid {
		t.Fatalf("expected session for user %d, got %d", uid, userID)
	}
	if !strings.HasSuffix(redirect, "/") {
		t.Fatalf("expected app redirect, got %s", redirect)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM access_requests"); got != 0 {
		t.Fatalf("expected no access requests for known identity, got %d", got)
	}
}

func TestResolveCallback_VerifiedEmailChangeAudited(t *testing.T) {
	s := newTestServer(t)
	uid := createUser(t, s, "ester", "viewer", "active", "sub-ester")
	_, _ = s.db.Exec("UPDATE user_identities SET email='old@x.test', email_verified=1 WHERE user_id=?", uid)
	claims := oidcClaims{Subject: "sub-ester", PreferredUsername: "ester", Email: "new@x.test", EmailVerified: true}

	if _, _, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, ""); err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	var email string
	if err := s.db.QueryRow("SELECT email FROM user_identities WHERE user_id=?", uid).Scan(&email); err != nil {
		t.Fatalf("query: %v", err)
	}
	if email != "new@x.test" {
		t.Fatalf("expected email updated, got %s", email)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM audit_logs WHERE event=?", EventIdentityEmailChanged); got != 1 {
		t.Fatalf("expected identity_email_changed audit, got %d", got)
	}
}

func TestResolveCallback_DisabledUserReactivate(t *testing.T) {
	s := newTestServer(t)
	createUser(t, s, "obed", "viewer", "inactive", "sub-obed")
	claims := oidcClaims{Subject: "sub-obed", PreferredUsername: "obed"}

	userID, redirect, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, "")
	if err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	if userID != 0 || redirect != s.cfg.AccessPendingURL {
		t.Fatalf("disabled user must not get a session: user=%d redirect=%s", userID, redirect)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM access_requests WHERE request_type='reactivate_user' AND subject='sub-obed'"); got != 1 {
		t.Fatalf("expected reactivate_user request, got %d", got)
	}
}

func TestResolveCallback_BootstrapAdmin(t *testing.T) {
	s := newTestServer(t)
	s.cfg.BootstrapAdminEmail = "boss@hkbp.test"
	claims := oidcClaims{Subject: "sub-boss", PreferredUsername: "boss", Email: "boss@hkbp.test", EmailVerified: true}

	userID, redirect, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, "")
	if err != nil {
		t.Fatalf("resolveCallback: %v", err)
	}
	if userID == 0 {
		t.Fatal("expected bootstrap admin to get a session")
	}
	if !strings.HasSuffix(redirect, "/") {
		t.Fatalf("expected app redirect, got %s", redirect)
	}
	var role string
	if err := s.db.QueryRow(`SELECT r.name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?`, userID).Scan(&role); err != nil {
		t.Fatalf("query role: %v", err)
	}
	if role != "admin" {
		t.Fatalf("expected admin role, got %s", role)
	}

	// Second unknown identity must NOT bootstrap once an active admin exists.
	other := oidcClaims{Subject: "sub-other", PreferredUsername: "other", Email: "boss@hkbp.test", EmailVerified: true}
	uid2, _, err := s.resolveCallback(other, s.cfg.OIDCIssuer, "")
	if err != nil {
		t.Fatalf("resolveCallback 2: %v", err)
	}
	if uid2 != 0 {
		t.Fatal("bootstrap must be inert once an active admin exists")
	}
}

func TestRejectThenReloginRespectsCooldown(t *testing.T) {
	s := newTestServer(t)
	claims := oidcClaims{Subject: "sub-cool", PreferredUsername: "cooluser"}

	// First login → one new_user request.
	if _, _, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, ""); err != nil {
		t.Fatalf("login 1: %v", err)
	}
	// Reject it now.
	if _, err := s.db.Exec("UPDATE access_requests SET status='rejected', decided_at=datetime('now') WHERE subject='sub-cool'"); err != nil {
		t.Fatalf("reject: %v", err)
	}
	// Re-login inside cooldown → no new row.
	if _, _, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, ""); err != nil {
		t.Fatalf("login 2: %v", err)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM access_requests WHERE subject='sub-cool'"); got != 1 {
		t.Fatalf("cooldown should suppress new rows; got %d", got)
	}
	// Move the rejection past the cooldown → new row allowed.
	if _, err := s.db.Exec("UPDATE access_requests SET decided_at=datetime('now','-3 hours') WHERE subject='sub-cool'"); err != nil {
		t.Fatalf("age out: %v", err)
	}
	if _, _, err := s.resolveCallback(claims, s.cfg.OIDCIssuer, ""); err != nil {
		t.Fatalf("login 3: %v", err)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM access_requests WHERE subject='sub-cool'"); got != 2 {
		t.Fatalf("expected a fresh request after cooldown; got %d", got)
	}
}

func TestWithinCooldown(t *testing.T) {
	now := time.Now().UTC()
	cases := []struct {
		name string
		val  sql.NullString
		want bool
	}{
		{"null", sql.NullString{}, false},
		{"recent", sql.NullString{String: now.Add(-30 * time.Minute).Format(sqlTime), Valid: true}, true},
		{"old", sql.NullString{String: now.Add(-3 * time.Hour).Format(sqlTime), Valid: true}, false},
	}
	for _, tc := range cases {
		if got := withinCooldown(tc.val, 2*time.Hour); got != tc.want {
			t.Errorf("%s: withinCooldown=%v want %v", tc.name, got, tc.want)
		}
	}
}

func TestAuthenticateSession(t *testing.T) {
	s := newTestServer(t)
	uid := createUser(t, s, "sari", "viewer", "active", "sub-sari")
	raw := sessionToken(t, s, uid)

	if _, err := s.authenticateSession(raw); err != nil {
		t.Fatalf("valid session should authenticate: %v", err)
	}

	// Unknown token.
	if _, err := s.authenticateSession("nope"); err == nil {
		t.Fatal("unknown token must fail")
	}

	// session_version drift (admin-sensitive change) invalidates the session.
	if _, err := s.db.Exec("UPDATE users SET session_version = session_version + 1 WHERE id=?", uid); err != nil {
		t.Fatalf("bump: %v", err)
	}
	if _, err := s.authenticateSession(raw); err == nil {
		t.Fatal("stale session_version must fail")
	}
}

func TestAuthenticateSession_Gates(t *testing.T) {
	s := newTestServer(t)

	// No identity link → not authenticatable even with a valid session row.
	noIdentity := createUser(t, s, "noid", "viewer", "active", "")
	if _, err := s.authenticateSession(sessionToken(t, s, noIdentity)); err == nil {
		t.Fatal("user without identity link must not authenticate")
	}

	// pending_idp provisioning → blocked.
	pending := createUser(t, s, "pend", "viewer", "active", "sub-pend")
	if _, err := s.db.Exec("UPDATE users SET provisioning_status='pending_idp' WHERE id=?", pending); err != nil {
		t.Fatalf("set pending: %v", err)
	}
	if _, err := s.authenticateSession(sessionToken(t, s, pending)); err == nil {
		t.Fatal("pending_idp user must not authenticate")
	}

	// disabled user → blocked.
	disabled := createUser(t, s, "dis", "viewer", "inactive", "sub-dis")
	if _, err := s.authenticateSession(sessionToken(t, s, disabled)); err == nil {
		t.Fatal("inactive user must not authenticate")
	}

	// revoked session → blocked.
	active := createUser(t, s, "act", "viewer", "active", "sub-act")
	tok := sessionToken(t, s, active)
	if _, err := s.db.Exec("UPDATE app_sessions SET revoked_at=datetime('now') WHERE token_hash=?", hashToken(tok)); err != nil {
		t.Fatalf("revoke: %v", err)
	}
	if _, err := s.authenticateSession(tok); err == nil {
		t.Fatal("revoked session must not authenticate")
	}
}

// --- HTTP integration tests over the real Fiber router ---

func newTestApp(t *testing.T, s *Server) *fiber.App {
	t.Helper()
	app := fiber.New()
	s.registerRoutes(app)
	return app
}

func TestMeRequiresCookie(t *testing.T) {
	s := newTestServer(t)
	app := newTestApp(t, s)

	req, _ := http.NewRequest(http.MethodGet, "/api/v1/auth/me", nil)
	resp, err := app.Test(req)
	if err != nil {
		t.Fatalf("test request: %v", err)
	}
	if resp.StatusCode != http.StatusUnauthorized {
		t.Fatalf("expected 401 without cookie, got %d", resp.StatusCode)
	}

	uid := createUser(t, s, "lidya", "admin", "active", "sub-lidya")
	raw := sessionToken(t, s, uid)
	req2, _ := http.NewRequest(http.MethodGet, "/api/v1/auth/me", nil)
	req2.AddCookie(&http.Cookie{Name: s.cfg.CookieName, Value: raw})
	resp2, err := app.Test(req2)
	if err != nil {
		t.Fatalf("test request 2: %v", err)
	}
	if resp2.StatusCode != http.StatusOK {
		t.Fatalf("expected 200 with cookie, got %d", resp2.StatusCode)
	}
	body, _ := io.ReadAll(resp2.Body)
	var me map[string]any
	if err := json.Unmarshal(body, &me); err != nil {
		t.Fatalf("decode me: %v", err)
	}
	if me["username"] != "lidya" {
		t.Fatalf("expected username lidya, got %v", me["username"])
	}
}

func TestRenameBlockedWhenIdPUnsupported(t *testing.T) {
	s := newTestServer(t)
	app := newTestApp(t, s)

	admin := createUser(t, s, "admin", "admin", "active", "sub-admin")
	target := createUser(t, s, "target", "viewer", "active", "sub-target")
	raw := sessionToken(t, s, admin)

	req, _ := http.NewRequest(http.MethodPost, "/api/v1/users/"+itoa(target)+"/rename",
		strings.NewReader(`{"username":"renamed"}`))
	req.Header.Set("Content-Type", "application/json")
	req.AddCookie(&http.Cookie{Name: s.cfg.CookieName, Value: raw})
	resp, err := app.Test(req)
	if err != nil {
		t.Fatalf("test request: %v", err)
	}
	if resp.StatusCode != http.StatusNotImplemented {
		t.Fatalf("expected 501 (rename unsupported), got %d", resp.StatusCode)
	}
	// Local username must be unchanged and a drift audit recorded.
	var username string
	if err := s.db.QueryRow("SELECT username FROM users WHERE id=?", target).Scan(&username); err != nil {
		t.Fatalf("query: %v", err)
	}
	if username != "target" {
		t.Fatalf("username must not change on blocked rename, got %s", username)
	}
	if got := countRows(t, s, "SELECT COUNT(1) FROM audit_logs WHERE event=?", EventUsernameChangeBlocked); got != 1 {
		t.Fatalf("expected username_change_blocked audit, got %d", got)
	}
}

func itoa(v int64) string {
	return strings.TrimSpace(jsonNumber(v))
}

func jsonNumber(v int64) string {
	b, _ := json.Marshal(v)
	return string(b)
}

func TestMigration010PreservesExistingUsers(t *testing.T) {
	db, err := sql.Open("sqlite", "file:"+t.TempDir()+"/mig.db?_pragma=foreign_keys(1)")
	if err != nil {
		t.Fatalf("open: %v", err)
	}
	db.SetMaxOpenConns(1)
	t.Cleanup(func() { _ = db.Close() })

	// Old-world schema fragment: roles + the pre-OIDC users table (email NOT NULL
	// UNIQUE, password_hash) + a child table with a FK to users.
	setup := []string{
		`CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, created_at TEXT NOT NULL DEFAULT (datetime('now')))`,
		`CREATE TABLE sectors (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE)`,
		`INSERT INTO roles (name) VALUES ('admin')`,
		`CREATE TABLE users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			username TEXT NOT NULL UNIQUE,
			email TEXT NOT NULL UNIQUE,
			password_hash TEXT NOT NULL,
			nama_depan TEXT NOT NULL,
			nama_belakang TEXT,
			role_id INTEGER NOT NULL REFERENCES roles(id),
			sektor_id INTEGER REFERENCES sectors(id),
			status TEXT NOT NULL DEFAULT 'active',
			refresh_token TEXT,
			last_access TEXT,
			created_at TEXT NOT NULL DEFAULT (datetime('now')),
			updated_at TEXT NOT NULL DEFAULT (datetime('now')))`,
		`CREATE TABLE refresh_tokens (id INTEGER PRIMARY KEY, user_id INTEGER REFERENCES users(id))`,
		`CREATE TABLE offerings (id INTEGER PRIMARY KEY AUTOINCREMENT, created_by INTEGER NOT NULL REFERENCES users(id))`,
		`INSERT INTO users (username, email, password_hash, nama_depan, role_id) VALUES ('admin','admin@x.test','hash','Admin',1)`,
		`INSERT INTO offerings (created_by) VALUES (1)`,
		`CREATE TABLE _migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, applied_at TEXT NOT NULL DEFAULT (datetime('now')))`,
		`INSERT INTO _migrations (name) VALUES
			('001_create_sectors.sql'),('002_create_roles.sql'),('003_create_users.sql'),
			('004_create_families.sql'),('005_create_members.sql'),('006_create_offerings.sql'),
			('007_create_sintua.sql'),('008_create_attendance.sql'),('009_create_refresh_tokens.sql')`,
	}
	for _, stmt := range setup {
		if _, err := db.Exec(stmt); err != nil {
			t.Fatalf("setup %q: %v", stmt, err)
		}
	}

	// Apply migration 010 through the real runner.
	if err := runMigrations(db, "../../migrations"); err != nil {
		t.Fatalf("runMigrations: %v", err)
	}

	// Existing admin preserved with new columns defaulted.
	var username, provisioning string
	var version int64
	if err := db.QueryRow("SELECT username, provisioning_status, session_version FROM users WHERE id=1").Scan(&username, &provisioning, &version); err != nil {
		t.Fatalf("query user: %v", err)
	}
	if username != "admin" || provisioning != "active" || version != 1 {
		t.Fatalf("unexpected migrated row: %s/%s/%d", username, provisioning, version)
	}
	// Child FK reference still resolves.
	var createdBy int64
	if err := db.QueryRow("SELECT created_by FROM offerings WHERE id=1").Scan(&createdBy); err != nil {
		t.Fatalf("query offering: %v", err)
	}
	if createdBy != 1 {
		t.Fatalf("offering FK lost: %d", createdBy)
	}
	// New auth tables exist.
	for _, tbl := range []string{"app_sessions", "user_identities", "access_requests", "audit_logs"} {
		if _, err := db.Exec("SELECT 1 FROM " + tbl + " LIMIT 1"); err != nil {
			t.Fatalf("expected table %s: %v", tbl, err)
		}
	}
	// password_hash column is gone.
	if _, err := db.Exec("SELECT password_hash FROM users LIMIT 1"); err == nil {
		t.Fatal("expected password_hash column to be dropped")
	}
}
