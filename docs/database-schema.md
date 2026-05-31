# HKBP Jatinegara — Database Schema

## Turso DDL

Run these migrations in order using `turso db shell` or embed in backend migration runner.

### 001_create_sectors.sql
```sql
CREATE TABLE sectors (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL UNIQUE,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

INSERT INTO sectors (name) VALUES
    ('Judika'),
    ('Galatia'),
    ('Kolose'),
    ('Markus'),
    ('Diaspora');
```

### 002_create_roles.sql
```sql
CREATE TABLE roles (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL UNIQUE,
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

INSERT INTO roles (name) VALUES
    ('admin'),
    ('sektor_admin'),
    ('viewer');
```

### 003_create_users.sql
```sql
CREATE TABLE users (
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

CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_sector ON users(sektor_id);
CREATE INDEX idx_users_status ON users(status);
```

### 004_create_families.sql
```sql
CREATE TABLE families (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    sector_id       INTEGER NOT NULL REFERENCES sectors(id),
    head_member_id  INTEGER,
    alamat          TEXT,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_families_sector ON families(sector_id);
```

### 005_create_members.sql
```sql
CREATE TABLE members (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id           INTEGER NOT NULL REFERENCES families(id),
    sector_id           INTEGER NOT NULL REFERENCES sectors(id),
    nama                TEXT NOT NULL,
    marga               TEXT,
    gender              TEXT NOT NULL CHECK (gender IN ('Laki-laki', 'Perempuan')),
    tempat_lahir        TEXT,
    tanggal_lahir       TEXT,
    gol_darah           TEXT CHECK (gol_darah IN ('A', 'B', 'O', 'AB')),
    hubungan_keluarga   TEXT NOT NULL CHECK (hubungan_keluarga IN ('Kepala Keluarga', 'Istri', 'Anak')),
    pendidikan          TEXT CHECK (pendidikan IN ('SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3')),
    pekerjaan           TEXT,
    talenta             TEXT,
    no_hp               TEXT,
    alamat              TEXT,
    provinsi            TEXT,
    kota                TEXT,
    kecamatan           TEXT,
    kelurahan           TEXT,
    kode_pos            TEXT,
    foto_url            TEXT,

    -- Sacramental records
    tgl_baptis          TEXT,
    gereja_baptis       TEXT,
    pendeta_baptis      TEXT,
    tgl_sidi            TEXT,
    gereja_sidi         TEXT,
    pendeta_sidi        TEXT,
    nats_sidi           TEXT,
    tgl_perkawinan      TEXT,
    gereja_perkawinan   TEXT,
    pendeta_perkawinan  TEXT,
    nats_perkawinan     TEXT,

    is_head_of_family   INTEGER NOT NULL DEFAULT 0,
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at          TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_members_family ON members(family_id);
CREATE INDEX idx_members_sector ON members(sector_id);
CREATE INDEX idx_members_nama ON members(nama);
CREATE INDEX idx_members_marga ON members(marga);
CREATE INDEX idx_members_hubungan ON members(hubungan_keluarga);
CREATE INDEX idx_members_sacraments ON members(tgl_baptis, tgl_sidi, tgl_perkawinan);

-- Trigger to enforce exactly one head of family per family
CREATE TRIGGER trg_members_single_head
BEFORE INSERT ON members
WHEN NEW.hubungan_keluarga = 'Kepala Keluarga'
BEGIN
    SELECT CASE
        WHEN EXISTS (
            SELECT 1 FROM members
            WHERE family_id = NEW.family_id AND hubungan_keluarga = 'Kepala Keluarga'
        ) THEN RAISE(ABORT, 'Family already has a head (Kepala Keluarga)')
    END;
END;
```

### 006_create_offerings.sql
```sql
CREATE TABLE offerings (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id   INTEGER NOT NULL REFERENCES families(id),
    sector_id   INTEGER NOT NULL REFERENCES sectors(id),
    amount      INTEGER NOT NULL CHECK (amount > 0),
    month       INTEGER NOT NULL CHECK (month BETWEEN 1 AND 12),
    year        INTEGER NOT NULL,
    notes       TEXT,
    created_by  INTEGER NOT NULL REFERENCES users(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_offerings_sector_month ON offerings(sector_id, year, month);
CREATE INDEX idx_offerings_family ON offerings(family_id);
CREATE INDEX idx_offerings_created_by ON offerings(created_by);
```

### 007_create_sintua.sql
```sql
CREATE TABLE sintua (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL UNIQUE REFERENCES members(id),
    sektor_id   INTEGER NOT NULL REFERENCES sectors(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_sintua_sector ON sintua(sektor_id);
```

### 008_create_attendance.sql
```sql
CREATE TABLE attendance (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL REFERENCES members(id),
    date        TEXT NOT NULL,
    status      TEXT NOT NULL CHECK (status IN ('hadir', 'tidak_hadir', 'izin', 'sakit')),
    seksi       TEXT NOT NULL CHECK (seksi IN ('musik', 'multimedia')),
    created_by  INTEGER NOT NULL REFERENCES users(id),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE UNIQUE INDEX idx_attendance_unique ON attendance(member_id, date, seksi);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_seksi ON attendance(seksi);
```

### 009_create_refresh_tokens.sql
```sql
CREATE TABLE refresh_tokens (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash  TEXT NOT NULL UNIQUE,
    expires_at  TEXT NOT NULL,
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX idx_refresh_tokens_user ON refresh_tokens(user_id);
CREATE INDEX idx_refresh_tokens_expires ON refresh_tokens(expires_at);
```

## Entity Relationship Summary

```
sectors 1──N families
sectors 1──N members
sectors 1──N sintua
sectors 1──N offerings
sectors 1──N users

families 1──N members
families 1──N offerings

members 1──1 sintua (optional)
members 1──N attendance

roles 1──N users

users 1──N offerings (created_by)
users 1──N attendance (created_by)
```

## Notes
- All timestamps are stored as ISO 8601 text strings (SQLite has no native datetime type)
- Turso uses libSQL which is wire-compatible with SQLite
- Photo uploads are stored as files (local disk or object storage); only the URL is in the DB
- Amounts (offerings) are stored as integers in IDR (rupiah) — no floating point
