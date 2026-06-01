CREATE TABLE IF NOT EXISTS sintua (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL UNIQUE REFERENCES members(id) ON DELETE CASCADE,
    sektor_id   INTEGER NOT NULL REFERENCES sectors(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_sintua_sector ON sintua(sektor_id);
