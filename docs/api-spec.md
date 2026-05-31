# HKBP Jatinegara API Specification

Base URL: `http://localhost:8080/api/v1`

## Authentication

### POST /auth/login
Authenticate a user and return JWT tokens.

**Request:**
```json
{
  "username": "string",
  "password": "string"
}
```

**Response (200):**
```json
{
  "access_token": "string",
  "refresh_token": "string",
  "expires_in": 900,
  "user": {
    "id": 1,
    "nama_depan": "string",
    "nama_belakang": "string",
    "username": "string",
    "email": "string",
    "role_id": 1,
    "sektor_id": 1,
    "status": "active"
  }
}
```

**Response (401):** `{"error": "invalid credentials"}`

### POST /auth/refresh
Refresh an expired access token.

**Request:**
```json
{
  "refresh_token": "string"
}
```

**Response (200):**
```json
{
  "access_token": "string",
  "expires_in": 900
}
```

### GET /auth/me
Get current user profile. Requires Bearer token.

**Response (200):**
```json
{
  "id": 1,
  "nama_depan": "string",
  "nama_belakang": "string",
  "username": "string",
  "email": "string",
  "role_id": 1,
  "sektor_id": 1,
  "status": "active",
  "last_access": "2025-01-01T00:00:00Z"
}
```

---

## Sectors

### GET /sectors
List all sectors. Requires auth.

**Response (200):**
```json
{
  "data": [
    {"id": 1, "name": "Judika"},
    {"id": 2, "name": "Galatia"},
    {"id": 3, "name": "Kolose"},
    {"id": 4, "name": "Markus"},
    {"id": 5, "name": "Diaspora"}
  ]
}
```

### POST /sectors
Create a new sector. Requires admin role.

**Request:** `{"name": "string"}`

### PUT /sectors/:id
Update a sector. Requires admin role.

**Request:** `{"name": "string"}`

### DELETE /sectors/:id
Delete a sector. Requires admin role.

---

## Roles

### GET /roles
List all roles. Requires auth.

**Response (200):**
```json
{
  "data": [
    {"id": 1, "name": "admin"},
    {"id": 2, "name": "sektor_admin"},
    {"id": 3, "name": "viewer"}
  ]
}
```

---

## Users

### GET /users
List all users. Requires admin role.

**Query params:** `?sektor_id=1`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "username": "string",
      "email": "string",
      "nama_depan": "string",
      "nama_belakang": "string",
      "role_id": 1,
      "sektor_id": 1,
      "status": "active",
      "last_access": "2025-01-01T00:00:00Z"
    }
  ]
}
```

### POST /users
Create a new user. Requires admin role.

**Request:**
```json
{
  "username": "string",
  "email": "string",
  "password": "string",
  "nama_depan": "string",
  "nama_belakang": "string",
  "role_id": 1,
  "sektor_id": 1
}
```

### PUT /users/:id
Update a user. Requires admin role.

### PUT /users/:id/password
Change user password. Admin can change any; users can change own.

**Request:** `{"password": "string", "new_password": "string"}`

### DELETE /users/:id
Deactivate a user. Requires admin role.

---

## Families

### GET /families
List families. Requires auth.

**Query params:** `?sektor_id=1&page=1&per_page=20`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "sector_id": 1,
      "sector_name": "Judika",
      "head_member_id": 1,
      "head_member_name": "string",
      "alamat": "string",
      "member_count": 4,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

### GET /families/:id
Get family details with all members.

**Response (200):**
```json
{
  "id": 1,
  "sector_id": 1,
  "sector_name": "Judika",
  "head_member_id": 1,
  "alamat": "string",
  "members": [
    {"id": 1, "nama": "string", "marga": "string", "hubungan_keluarga": "Kepala Keluarga", ...},
    {"id": 2, "nama": "string", "marga": "string", "hubungan_keluarga": "Istri", ...},
    {"id": 3, "nama": "string", "marga": "string", "hubungan_keluarga": "Anak", ...}
  ],
  "created_at": "2025-01-01T00:00:00Z"
}
```

### POST /families
Create a new family with its members.

**Request:**
```json
{
  "sector_id": 1,
  "alamat": "string",
  "members": [
    {
      "nama": "string",
      "marga": "string",
      "gender": "Laki-laki",
      "tempat_lahir": "string",
      "tanggal_lahir": "2025-01-01",
      "gol_darah": "A",
      "hubungan_keluarga": "Kepala Keluarga",
      "pendidikan": "S1",
      "pekerjaan": "Pegawai Swasta",
      "talenta": "string",
      "no_hp": "string",
      "provinsi": "DKI Jakarta",
      "kota": "Jakarta Timur",
      "kecamatan": "Jatinegara",
      "kelurahan": "Cipinang Muara",
      "kode_pos": "13450",
      "tgl_baptis": "2025-01-01",
      "gereja_baptis": "string",
      "pendeta_baptis": "string",
      "tgl_sidi": "2025-01-01",
      "gereja_sidi": "string",
      "pendeta_sidi": "string",
      "nats_sidi": "string",
      "tgl_perkawinan": "2025-01-01",
      "gereja_perkawinan": "string",
      "pendeta_perkawinan": "string",
      "nats_perkawinan": "string"
    }
  ]
}
```

### PUT /families/:id
Update family (sector, address).

### DELETE /families/:id
Delete family and cascade delete all its members. Requires admin.

---

## Members

### GET /members
List members. Requires auth.

**Query params:** `?sektor_id=1&family_id=1&hubungan=Kepala+Keluarga&search=nama&page=1&per_page=20`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "family_id": 1,
      "sector_id": 1,
      "sector_name": "Judika",
      "nama": "string",
      "marga": "string",
      "gender": "Laki-laki",
      "tempat_lahir": "string",
      "tanggal_lahir": "2025-01-01",
      "gol_darah": "A",
      "hubungan_keluarga": "Kepala Keluarga",
      "pendidikan": "S1",
      "pekerjaan": "Pegawai Swasta",
      "talenta": "string",
      "no_hp": "string",
      "alamat": "string",
      "provinsi": "DKI Jakarta",
      "kota": "Jakarta Timur",
      "kecamatan": "Jatinegara",
      "kelurahan": "Cipinang Muara",
      "kode_pos": "13450",
      "foto_url": "string",
      "tgl_baptis": "2025-01-01",
      "gereja_baptis": "string",
      "pendeta_baptis": "string",
      "tgl_sidi": "2025-01-01",
      "gereja_sidi": "string",
      "pendeta_sidi": "string",
      "nats_sidi": "string",
      "tgl_perkawinan": "2025-01-01",
      "gereja_perkawinan": "string",
      "pendeta_perkawinan": "string",
      "nats_perkawinan": "string",
      "is_head_of_family": true,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "pagination": {"page": 1, "per_page": 20, "total": 100, "total_pages": 5}
}
```

### GET /members/:id
Get single member details.

### PUT /members/:id
Update a member's data.

### POST /members/:id/foto
Upload member photo (multipart form). Returns `{"foto_url": "string"}`.

### DELETE /members/:id
Remove a member from a family. Cannot remove the head of family without reassigning.

---

## Offerings

### GET /offerings
List monthly offerings. Requires auth.

**Query params:** `?sektor_id=1&family_id=1&month=1&year=2025&page=1&per_page=20`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "family_id": 1,
      "sector_id": 1,
      "sector_name": "Judika",
      "family_head_name": "string",
      "amount": 100000,
      "month": 1,
      "year": 2025,
      "notes": "string",
      "created_by": 1,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "pagination": {...}
}
```

### POST /offerings
Add offering entry.

**Request:**
```json
{
  "family_id": 1,
  "amount": 100000,
  "month": 1,
  "year": 2025,
  "notes": "string"
}
```

### GET /offerings/report
Monthly offering report with totals per sector.

**Query params:** `?month=1&year=2025&sektor_id=1`

**Response (200):**
```json
{
  "total": 5000000,
  "by_sector": [
    {"sektor_id": 1, "sektor_name": "Judika", "total": 1000000, "family_count": 10}
  ],
  "entries": [...]
}
```

### DELETE /offerings/:id
Delete an offering entry. Requires admin.

---

## Sintua (Elders)

### GET /sintua
List elders. Requires auth.

**Query params:** `?sektor_id=1`

**Response (200):**
```json
{
  "data": [
    {"id": 1, "member_id": 1, "member_name": "string", "sektor_id": 1, "sektor_name": "Judika"}
  ]
}
```

### POST /sintua
Assign elder status to a member.

**Request:** `{"member_id": 1}`

### DELETE /sintua/:id
Remove elder status.

---

## Attendance

### GET /attendance
List attendance records.

**Query params:** `?date=2025-01-01&seksi=musik`

**Response (200):**
```json
{
  "data": [
    {"id": 1, "member_id": 1, "member_name": "string", "date": "2025-01-01", "status": "hadir", "seksi": "musik"}
  ]
}
```

### POST /attendance
Record attendance.

**Request:** `{"member_id": 1, "date": "2025-01-01", "status": "hadir", "seksi": "musik"}`

---

## Error Response Format

All errors follow this shape:
```json
{
  "error": "human-readable message",
  "code": "VALIDATION_ERROR",
  "details": {"field": "error message"}
}
```

## HTTP Status Codes
- 200 — Success
- 201 — Created
- 400 — Bad request / validation error
- 401 — Unauthorized
- 403 — Forbidden (wrong role)
- 404 — Not found
- 500 — Internal server error
