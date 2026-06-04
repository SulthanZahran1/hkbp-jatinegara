# HKBP Jatinegara — Domain Glossary

## Project
Modernizing HKBP Jatinegara's church administration from a legacy PHP stack (Laravel thin client + separate backend API) to **TypeScript (Vue SPA frontend) + Go (Fiber API backend)**.

## Database
**Self-hosted libSQL server** using Turso-compatible drivers. Chosen to keep the database self-hosted while preserving the Turso/libSQL API surface and adequate performance for <10 concurrent users.

## Authentication
**A dedicated lightweight Identity Provider via OIDC** owns authentication credentials and login sessions. The HKBP application treats the authenticated identity as external and keeps only application-specific profile and authorization data locally. The IdP must support admin-managed username/password accounts for users who do not have personal email addresses; Rauthy is no longer assumed because its email requirement does not fit that user base.

Chosen URL contract:

- HKBP app: `https://hkbp.zahranm.cloud`
- IdP issuer: `https://auth.hkbp.zahranm.cloud`
- IdP deployment: separate Dokploy/Traefik app from the HKBP application stack, so identity infrastructure can be restarted and updated independently
- IdP state: provider-owned embedded SQLite/single-file state mounted on a persistent Dokploy volume; volume backups are required
- Primary IdP candidate: Auténtico, validated by spike `spikes/001-autentico-idp/` for no-email username/password users, OIDC/JWKS/token/userinfo/logout surfaces, SQLite state, and a small setup-password-link fork patch.
- OIDC callback: `https://hkbp.zahranm.cloud/api/v1/auth/callback`
- Post-logout redirect: `https://hkbp.zahranm.cloud/login?logged_out=1`
- Pending approval page: `https://hkbp.zahranm.cloud/access-pending`

Session policy:

- HKBP uses backend-mediated OIDC with an HTTP-only `hkbp_session` cookie.
- The cookie contains only an opaque random token; `app_sessions.token_hash` stores the server-side session.
- Protected requests require an active unexpired app session, `users.status = active`, and `app_sessions.session_version == users.session_version`.
- OIDC identity data is separated from the HKBP app user profile in a `user_identities` table, keeping credentials/issuer/subject/optional email claims distinct from local role, sector, status, and username fields.
- HKBP is the provisioning source of truth: create the HKBP `users` row first with `provisioning_status = pending_idp`, call the chosen IdP management API, create the `user_identities` link from the returned stable IdP subject, then mark `provisioning_status = active`. First credential setup must avoid admin-handled temporary passwords: prefer an IdP-provided one-time setup/reset link or token that lets the user set their own password; for MVP, maintain a tiny HKBP-specific Auténtico fork/image if needed to add a minimal admin-generated setup-password-link endpoint, then consider upstreaming later. Normal login requires `users.status = active`, `users.provisioning_status = active`, and a `user_identities` link. The IdP username / `preferred_username` and HKBP `users.username` are forced to stay identical; drift is treated as an admin-visible configuration problem rather than a normal divergent state.
- Unknown IdP identities first create rows in an explicit `access_requests` queue; a normal HKBP `users` row and corresponding `user_identities` row are created atomically only on approval.
- Approved access requests require the user to sign in again through the IdP before receiving a normal HKBP app session; pending sessions do not auto-upgrade into authorized sessions.
- Login uses `(issuer, subject)` as the automatic identity match. If no identity row exists but the IdP username / `preferred_username` matches an existing HKBP handle, the backend creates an admin-reviewed access request with a suggested existing user link instead of silently auto-linking. Verified email, if present, is optional supporting evidence rather than the primary human identifier.
- Access requests use explicit approval modes via `request_type`: `new_user` creates a new HKBP user on approval, `link_existing_user` asks an admin to confirm or change the target existing user before linking the OIDC identity, and `reactivate_user` asks an admin to re-enable a disabled linked user.
- Rejected access requests are retained for audit but may be re-requested after a 2-hour cooldown; repeated attempts inside the cooldown show access denied/pending status instead of creating spam rows.
- Access request discovery is passive-only: new requests appear in the admin dashboard badge/list; no email, push, or real-time notification is required for MVP.
- Admin-disabled users keep their OIDC identity link, but signing in again creates or reopens an access request after the normal 2-hour rejected-request cooldown instead of permanently blocking self re-request.
- Verified email changes for an already-linked `(issuer, subject)`, when the IdP provides email claims, are accepted automatically: update `user_identities.email` / `email_verified`, create an audit-log entry with old/new values, then continue login. Email is optional contact/supporting identity metadata, not the primary account identifier.
- Audit logging uses a generic `audit_logs` table/helper designed for future all-mutation audit, but MVP wiring covers only auth/access events such as request creation/approval/rejection, identity linking, identity email changes, session revocation, and logout.
- Admin-sensitive user changes increment `users.session_version`; simple name or local handle edits do not.
- Logout revokes the HKBP app session, clears the cookie, and redirects through the IdP end-session flow so shared computers cannot silently re-enter via SSO.
- The login page has a single “Sign in with HKBP Account” CTA that calls the backend login endpoint; the Go backend owns OIDC authorization URL construction, state, nonce, callback validation, and session creation. No app-local username/password fallback is exposed in HKBP.
- The old JWT access/refresh-token auth model is removed from the active API contract: no password login, no refresh endpoint, no Bearer auth for the SPA. Cookie-backed DB sessions are the only browser auth mechanism.
- Non-browser automation uses server-side commands/import tools with direct environment/database access, not HTTP API bearer tokens. Personal access tokens and protected HTTP API token auth are out of MVP scope.
- Password policy is simple minimum length for MVP: minimum 8 characters, no mandatory symbol/uppercase composition rules, no forced periodic rotation, rate limiting/account lockout on repeated failures, and admin recovery via setup/reset link.
- Admins may rename usernames, but rename is a coordinated auth-sensitive operation: HKBP calls the IdP management API to rename the IdP username / `preferred_username`, updates `users.username`, writes an audit log, and invalidates sessions. Failed partial renames must be surfaced as drift/reconciliation items.
- IdP state backup is MVP-simple: nightly backup of the IdP persistent volume/SQLite state with retention (target 7–30 days). Restore drills are not required for MVP, but the backup path and restore command must be documented.

Bootstrap policy:

- `BOOTSTRAP_ADMIN_EMAIL` may create the first active admin only while no active admin exists and the OIDC email is verified.
- After any active admin exists, `BOOTSTRAP_ADMIN_EMAIL` is inert and unknown identities become pending access requests.

Implementation status (2026-06-04):

- Implemented in the Go backend (`backend/cmd/server/{auth,provisioning,access_requests,audit}.go`), migration `010_auth_oidc.sql`, the Vue SPA, and the IdP image path under `idp/`. Design detail lives in `docs/AUTH-DESIGN.md` and ADR `docs/adr/0004-oidc-cookie-auth.md`.
- Uses standard Go OIDC libraries: `github.com/coreos/go-oidc/v3` (discovery, JWKS, ID-token verify) + `golang.org/x/oauth2` (authorization-code + PKCE). The old `golang-jwt`/`bcrypt` deps are removed.
- Migration 010 rebuilds `users` (email optional; `password_hash`/`refresh_token` dropped; `provisioning_status` + `session_version` added) with foreign-keys disabled for the migration run, preserving ids and child references.
- The setup-password-link is a small HKBP-specific Auténtico patch vendored at `idp/patches/` (validated by spike 001), applied + route-registered by `idp/Dockerfile`. Documented and image-buildable; not yet upstreamed.
- Username rename is gated by `IDP_SUPPORTS_USERNAME_RENAME` (default false → the endpoint returns blocked/unsupported and audits drift) because the spike did not confirm an upstream Auténtico username-rename API. No silent local-only rename.
- SPA + API are served same-origin in production; local dev uses a Vite `/api` proxy so the `hkbp_session` cookie behaves identically.
- `GET /api/v1/audit-logs` exposes the audit log read-only to admins.

## Architecture

- **Vue SPA** — browser client, communicates with Go backend via JSON API
- **Go API (Fiber)** — single backend serving all business logic and data access
- **Database** — self-hosted libSQL server using Turso-compatible drivers

## Domain Concepts

### Member
An individual registered in the church system. Belongs to a **Family** and a **Sector**. Rich profile including personal details, education, occupation, baptism/confirmation/marriage sacramental records, and contact info.

### Marga
Batak clan/family name. A cultural attribute of a **Member**.

### Family
A household unit within the church. Has a **Head of Family** (Kepala Keluarga), optional spouse (Istri/Suami), and children (Anak). Tracked under a specific **Sector**. Family relationship is modeled as an attribute on **Member** (Kepala Keluarga / Istri / Anak).

### Sector (Sektor)
A geographical or organizational subdivision within the church. Members and families are grouped by sector.

### Elder (Sintua)
A church elder/presbyter. A role assigned to certain members within a sector.

### Profession (Profesi)
A member's occupation, tracked as master data.

### Monthly Offering (Persembahan Bulanan)
Recurring financial contributions tracked per family or per sector, aggregated into monthly reports.

### Attendance / Presence
Tracking of member attendance, currently used by the Music and Multimedia sections (Seksi Musik & Seksi Multimedia).

### User (Pengguna)
A system user authorized to access the HKBP application. The user's login identity is authenticated by the external **Identity Provider**; the HKBP application stores only profile and authorization data such as **role**, **sector**, and status.

### Identity Provider
The external authentication authority that owns credentials, login sessions, and OIDC identity claims for HKBP application users.

### Pending User
An IdP-authenticated identity that has requested HKBP application access but has not yet been approved with a **role**, **sector**, and active status.

### Access Request
An approval workflow for a **Pending User**. Approval assigns the HKBP-owned application profile and authorization fields, while the external identity fields from the **Identity Provider** remain non-editable.

### Role
Access control level assigned to a **User**.

### Service Material (Bahan)
Content for church services, organized by time slot: morning (Pagi), afternoon (Siang), evening (Sore).

## Multi-Tenancy Note
The legacy architecture separates the frontend (Laravel) from the backend (a separate API at `dbruas-be.hkbpjtn.web.id`). The new architecture collapses this into a single Go API + Vue SPA.
