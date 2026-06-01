CREATE TABLE IF NOT EXISTS families (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    sector_id       INTEGER NOT NULL REFERENCES sectors(id),
    head_member_id  INTEGER,
    alamat          TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_families_sector ON families(sector_id);
