-- Auth redesign: backend-mediated OIDC + opaque cookie sessions.
-- Credentials and login sessions are owned by the external IdP; HKBP keeps only
-- profile/authorization data plus an opaque server-side session and the OIDC
-- identity link. See CONTEXT.md and docs/AUTH-DESIGN.md.
--
-- This migration recreates `users` to make email/password optional (the IdP owns
-- credentials and may have no email) and to add provisioning + session-version
-- columns. The migration runner disables foreign_keys for the duration so the
-- parent table can be rebuilt while child tables (offerings, attendance) keep
-- their rows; ids are preserved so existing references stay valid.

CREATE TABLE users_new (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    username            TEXT NOT NULL UNIQUE,
    email               TEXT,
    nama_depan          TEXT NOT NULL,
    nama_belakang       TEXT,
    role_id             INTEGER NOT NULL REFERENCES roles(id),
    sektor_id           INTEGER REFERENCES sectors(id),
    status              TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    provisioning_status TEXT NOT NULL DEFAULT 'active' CHECK (provisioning_status IN ('pending_idp', 'active', 'failed_idp')),
    session_version     INTEGER NOT NULL DEFAULT 1,
    last_access         TEXT,
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at          TEXT NOT NULL DEFAULT (datetime('now'))
);

INSERT INTO users_new (id, username, email, nama_depan, nama_belakang, role_id, sektor_id, status, provisioning_status, session_version, last_access, created_at, updated_at)
SELECT id, username, email, nama_depan, nama_belakang, role_id, sektor_id, status, 'active', 1, last_access, created_at, updated_at
FROM users;

DROP TABLE users;

ALTER TABLE users_new RENAME TO users;

CREATE INDEX IF NOT EXISTS idx_users_role ON users(role_id);
CREATE INDEX IF NOT EXISTS idx_users_sector ON users(sektor_id);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_provisioning ON users(provisioning_status);

-- Old app-local JWT refresh tokens are replaced by opaque cookie sessions.
DROP TABLE IF EXISTS refresh_tokens;

-- Opaque server-side sessions. The browser holds only an HTTP-only cookie whose
-- value hashes to token_hash; the cookie never carries JWT claims or user data.
CREATE TABLE IF NOT EXISTS app_sessions (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash      TEXT NOT NULL UNIQUE,
    session_version INTEGER NOT NULL,
    user_agent      TEXT,
    ip              TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    expires_at      TEXT NOT NULL,
    revoked_at      TEXT
);

CREATE INDEX IF NOT EXISTS idx_app_sessions_user ON app_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_app_sessions_expires ON app_sessions(expires_at);

-- OIDC identity link, kept separate from HKBP profile/authorization fields.
-- (issuer, subject) is the stable automatic identity key; preferred_username is
-- the human handle and is forced to match users.username; email is optional.
CREATE TABLE IF NOT EXISTS user_identities (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id            INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    issuer             TEXT NOT NULL,
    subject            TEXT NOT NULL,
    preferred_username TEXT NOT NULL,
    email              TEXT,
    email_verified     INTEGER NOT NULL DEFAULT 0,
    created_at         TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at         TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (issuer, subject)
);

CREATE INDEX IF NOT EXISTS idx_user_identities_user ON user_identities(user_id);
CREATE INDEX IF NOT EXISTS idx_user_identities_username ON user_identities(preferred_username);

-- Approval queue for unknown / unlinked / disabled IdP identities.
CREATE TABLE IF NOT EXISTS access_requests (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    request_type       TEXT NOT NULL CHECK (request_type IN ('new_user', 'link_existing_user', 'reactivate_user')),
    status             TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    issuer             TEXT NOT NULL,
    subject            TEXT NOT NULL,
    preferred_username TEXT NOT NULL,
    email              TEXT,
    email_verified     INTEGER NOT NULL DEFAULT 0,
    suggested_user_id  INTEGER REFERENCES users(id),
    target_user_id     INTEGER REFERENCES users(id),
    decided_by         INTEGER REFERENCES users(id),
    decided_at         TEXT,
    decision_note      TEXT,
    created_at         TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at         TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_access_requests_status ON access_requests(status);
CREATE INDEX IF NOT EXISTS idx_access_requests_identity ON access_requests(issuer, subject);

-- Generic audit table; MVP wires only auth/access events but the shape supports
-- future all-mutation auditing.
CREATE TABLE IF NOT EXISTS audit_logs (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    event         TEXT NOT NULL,
    actor_user_id INTEGER REFERENCES users(id),
    target_type   TEXT,
    target_id     TEXT,
    detail        TEXT,
    ip            TEXT,
    created_at    TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_audit_logs_event ON audit_logs(event);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created ON audit_logs(created_at);
