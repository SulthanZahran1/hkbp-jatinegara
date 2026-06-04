# HKBP Jatinegara API Specification

Base URL: `http://localhost:8080/api/v1`

## Authentication

Backend-mediated OIDC against the IdP (Auténtico). The browser holds only the
HTTP-only opaque `hkbp_session` cookie — **no JWT, no Bearer tokens, no password
login, no refresh endpoint**. All authenticated requests send the cookie
(`withCredentials`). See `docs/AUTH-DESIGN.md` for the full design.

### GET /auth/login
Starts the OIDC authorization-code + PKCE flow. The backend generates
state/nonce/PKCE (stored in short-lived HTTP-only cookies) and **302-redirects**
to the IdP authorize URL. This is a full-page browser navigation, not an XHR.

**Response (302):** `Location: <idp authorize url>`
**Response (503):** `{"error": "identity provider unavailable"}` if OIDC discovery fails.

### GET /auth/callback
IdP redirect target. Validates `state`, exchanges the code (PKCE), verifies the
ID token (JWKS, issuer, audience, expiry, nonce), resolves the identity, and:

- on success → sets `hkbp_session` cookie and **302** to the app;
- otherwise (unknown/unlinked/disabled identity) → creates an access request and
  **302** to `/access-pending`.

### GET /auth/me
Current user profile from the cookie session only.

**Response (200):**
```json
{
  "id": 1,
  "username": "string",
  "nama_depan": "string",
  "nama_belakang": "string",
  "email": "string | null",
  "role_id": 1,
  "role_name": "admin",
  "sektor_id": 1,
  "status": "active",
  "provisioning_status": "active",
  "last_access": "2025-01-01 00:00:00",
  "identity": {
    "issuer": "https://auth.hkbp.zahranm.cloud",
    "preferred_username": "string",
    "email": "string | null",
    "email_verified": true
  }
}
```

**Response (401):** `{"error": "not authenticated"}`

### POST /auth/logout
Revokes the server session, clears the cookie, writes an audit entry, and returns
the IdP end-session URL so the SPA can complete RP-initiated logout (preventing
silent SSO re-entry on shared computers).

**Response (200):**
```json
{
  "logout_url": "https://auth.hkbp.zahranm.cloud/...?post_logout_redirect_uri=...",
  "post_logout_redirect": "https://hkbp.zahranm.cloud/login?logged_out=1"
}
```

## Access Requests (admin)

Unknown/unlinked/disabled IdP identities create access requests. Discovery is
passive via the admin dashboard. Approval never creates a session; the user must
sign in again. `request_type ∈ {new_user, link_existing_user, reactivate_user}`.

### GET /access-requests
List requests. Optional `?status=pending|approved|rejected`.

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "request_type": "new_user",
      "status": "pending",
      "preferred_username": "string",
      "email": "string | null",
      "email_verified": false,
      "suggested_user_id": null,
      "suggested_username": null,
      "target_user_id": null,
      "created_at": "2026-06-04 00:00:00"
    }
  ],
  "counts": { "pending": 1 }
}
```

### POST /access-requests/:id/approve
Admin only, transactional. Body depends on `request_type`:
- `new_user`: `{"role_id": 1, "sektor_id": 1, "nama_depan": "string", "nama_belakang": "string"}` (`role_id` required).
- `link_existing_user`: `{"target_user_id": 1}` (defaults to `suggested_user_id`).
- `reactivate_user`: no body required.

**Response (200):** `{"id": 1, "status": "approved", "user_id": 5, "message": "..."}`

### POST /access-requests/:id/reject
Admin only. Body: `{"note": "string"}` (optional). **Response (200):** `{"id": 1, "status": "rejected"}`

### POST /access-requests/:id/reopen
Admin only. Returns a decided request to pending. **Response (200):** `{"id": 1, "status": "pending"}`

## Audit Logs (admin)

### GET /audit-logs
Read-only audit log. Optional `?event=` and pagination (`page`, `per_page`).
**Response (200):** `{"data": [{"id", "event", "actor_username", "target_type", "target_id", "detail", "ip", "created_at"}], "pagination": {...}}`

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
      "email": "string | null",
      "nama_depan": "string",
      "nama_belakang": "string",
      "role_id": 1,
      "role_name": "admin",
      "sektor_id": 1,
      "status": "active",
      "provisioning_status": "active",
      "has_identity": true,
      "preferred_username": "string",
      "last_access": "2026-01-01 00:00:00"
    }
  ]
}
```

### POST /users
Provision a new user. Requires admin role. Creates the HKBP row first
(`provisioning_status = pending_idp`), creates the matching IdP account with a
random unusable bootstrap password (never stored/displayed), links the returned
subject, marks the user active, and returns a one-time setup link. Credentials
are owned by the IdP — there is **no** password field.

**Request:**
```json
{
  "username": "string",
  "email": "string (optional)",
  "nama_depan": "string",
  "nama_belakang": "string (optional)",
  "role_id": 1,
  "sektor_id": 1
}
```

**Response (201):** `{"user_id": 5, "provisioning_status": "active", "setup_url": "https://auth.../oauth2/reset-password?token=...", "expires_at": "..."}`
**Response (502):** `{"error": "IdP provisioning failed...", "provisioning_status": "failed_idp"}` (retryable)
**Response (501):** `{"error": "IdP admin API not configured..."}`

### PUT /users/:id
Update HKBP-owned profile/authorization fields. Requires admin role. Username and
credentials are not editable here. Changing role/status/sektor is auth-sensitive
and bumps `session_version` (revokes sessions).

**Request:** `{"email": "string | null", "nama_depan": "string", "nama_belakang": "string | null", "role_id": 1, "sektor_id": 1, "status": "active"}`

### POST /users/:id/setup-link
Regenerate a one-time setup/reset link for a provisioned user. Requires admin.
**Response (200):** `{"setup_url": "...", "expires_at": "..."}`

### POST /users/:id/retry-provisioning
Retry IdP provisioning for a `pending_idp`/`failed_idp` user. Requires admin.
**Response (200):** `{"user_id": 5, "provisioning_status": "active", "setup_url": "..."}`

### POST /users/:id/rename
Coordinated IdP + HKBP username rename. Requires admin. Renames the IdP
`preferred_username` first, then updates `users.username`, bumps `session_version`
and revokes sessions. If the IdP rename API is unsupported/unavailable, the local
username is left unchanged and the request is reported as blocked.

**Request:** `{"username": "string"}`
**Response (200):** `{"id": 5, "username": "newname", "status": "renamed"}`
**Response (501):** `{"error": "username rename is blocked..."}` (IdP rename unsupported)

### DELETE /users/:id
Disable a user (`status = inactive`, bumps `session_version`, revokes sessions).
The OIDC identity link is retained for later `reactivate_user`. Requires admin role.

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
