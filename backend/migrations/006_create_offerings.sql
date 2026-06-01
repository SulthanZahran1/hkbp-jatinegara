CREATE TABLE IF NOT EXISTS offerings (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id   INTEGER NOT NULL REFERENCES families(id) ON DELETE CASCADE,
    sector_id   INTEGER NOT NULL REFERENCES sectors(id),
    amount      INTEGER NOT NULL CHECK (amount > 0),
    month       INTEGER NOT NULL CHECK (month BETWEEN 1 AND 12),
    year        INTEGER NOT NULL,
    notes       TEXT,
    created_by  INTEGER NOT NULL REFERENCES users(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_offerings_sector_month ON offerings(sector_id, year, month);
CREATE INDEX IF NOT EXISTS idx_offerings_family ON offerings(family_id);
CREATE INDEX IF NOT EXISTS idx_offerings_created_by ON offerings(created_by);
