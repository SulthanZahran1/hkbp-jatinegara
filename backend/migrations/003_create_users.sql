CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    username        TEXT NOT NULL UNIQUE,
    email           TEXT NOT NULL UNIQUE,
    password_hash   TEXT NOT NULL,
    nama_depan      TEXT NOT NULL,
    nama_belakang   TEXT,
    role_id         INTEGER NOT NULL REFERENCES roles(id),
    sektor_id       INTEGER REFERENCES sectors(id),
    status          TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    refresh_token   TEXT,
    last_access     TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_users_role ON users(role_id);
CREATE INDEX IF NOT EXISTS idx_users_sector ON users(sektor_id);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
