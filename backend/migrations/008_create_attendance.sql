CREATE TABLE IF NOT EXISTS attendance (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL REFERENCES members(id) ON DELETE CASCADE,
    date        TEXT NOT NULL,
    status      TEXT NOT NULL CHECK (status IN ('hadir', 'tidak_hadir', 'izin', 'sakit')),
    seksi       TEXT NOT NULL CHECK (seksi IN ('musik', 'multimedia')),
    created_by  INTEGER NOT NULL REFERENCES users(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_attendance_unique ON attendance(member_id, date, seksi);
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);
CREATE INDEX IF NOT EXISTS idx_attendance_seksi ON attendance(seksi);
