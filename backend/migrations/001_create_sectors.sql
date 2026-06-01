CREATE TABLE IF NOT EXISTS sectors (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL UNIQUE,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

INSERT OR IGNORE INTO sectors (name) VALUES
    ('Judika'),
    ('Galatia'),
    ('Kolose'),
    ('Markus'),
    ('Diaspora');
