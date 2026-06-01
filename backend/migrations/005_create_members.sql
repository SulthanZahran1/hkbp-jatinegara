CREATE TABLE IF NOT EXISTS members (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id           INTEGER NOT NULL REFERENCES families(id) ON DELETE CASCADE,
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

CREATE INDEX IF NOT EXISTS idx_members_family ON members(family_id);
CREATE INDEX IF NOT EXISTS idx_members_sector ON members(sector_id);
CREATE INDEX IF NOT EXISTS idx_members_nama ON members(nama);
CREATE INDEX IF NOT EXISTS idx_members_marga ON members(marga);
CREATE INDEX IF NOT EXISTS idx_members_hubungan ON members(hubungan_keluarga);
CREATE INDEX IF NOT EXISTS idx_members_sacraments ON members(tgl_baptis, tgl_sidi, tgl_perkawinan);

CREATE TRIGGER IF NOT EXISTS trg_members_single_head_insert
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

CREATE TRIGGER IF NOT EXISTS trg_members_single_head_update
BEFORE UPDATE ON members
WHEN NEW.hubungan_keluarga = 'Kepala Keluarga'
BEGIN
    SELECT CASE
        WHEN EXISTS (
            SELECT 1 FROM members
            WHERE family_id = NEW.family_id
              AND hubungan_keluarga = 'Kepala Keluarga'
              AND id <> NEW.id
        ) THEN RAISE(ABORT, 'Family already has a head (Kepala Keluarga)')
    END;
END;
