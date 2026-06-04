# AUTH-DESIGN — HKBP Jatinegara

Backend-mediated OIDC with opaque cookie sessions. This is the implementation
companion to the auth decisions in `CONTEXT.md` (the canonical decision summary).

## Roles

- **HKBP app** — `https://hkbp.zahranm.cloud` (Vue SPA + Go API, same origin).
- **IdP (Auténtico)** — `https://auth.hkbp.zahranm.cloud` (OIDC OP, owns
  credentials + login sessions). Deployed separately; see `idp/`.

The Go backend is the OIDC relying party. The SPA never talks to the IdP
directly and never holds tokens.

## URL contract

| Purpose | URL |
|---|---|
| OIDC issuer | `https://auth.hkbp.zahranm.cloud` |
| OIDC callback | `https://hkbp.zahranm.cloud/api/v1/auth/callback` |
| Post-logout redirect | `https://hkbp.zahranm.cloud/login?logged_out=1` |
| Pending approval page | `https://hkbp.zahranm.cloud/access-pending` |

## Browser auth contract

- Single cookie `hkbp_session`: HTTP-only, `SameSite=Lax`, `Secure` in prod,
  value is an opaque random token. **No JWT claims or user data in the cookie.**
- `app_sessions.token_hash` stores `SHA-256(token)` server-side.
- A protected request is authorized only when **all** hold:
  - active, unexpired, unrevoked `app_sessions` row;
  - `users.status = 'active'`;
  - `users.provisioning_status = 'active'`;
  - `app_sessions.session_version == users.session_version`;
  - a `user_identities` row exists for the user.
- Removed from the active contract: password login, refresh endpoint/tokens, SPA
  Bearer auth, frontend token storage.

## Active auth API

| Method | Path | Notes |
|---|---|---|
| GET | `/api/v1/auth/login` | Generates state/nonce/PKCE (kept in short-lived HTTP-only cookies), 302 → IdP authorize URL. 503 if the IdP is unreachable. |
| GET | `/api/v1/auth/callback` | Validates state, exchanges code (PKCE), verifies ID token (JWKS, issuer, audience, expiry, nonce), resolves the identity, mints a session on success, redirects to the SPA or the pending page. |
| GET | `/api/v1/auth/me` | Current user from the cookie session only. 401 if unauthenticated. |
| POST | `/api/v1/auth/logout` | Revokes the session, clears the cookie, audits, returns the IdP end-session URL + post-logout redirect. |

## Callback decision logic

Given verified claims `(issuer, subject, preferred_username, email,
email_verified)`:

1. **`(issuer, subject)` matches an active provisioned identity** → update
   identity email if a verified change is present (audit `identity_email_changed`),
   bump `last_access`, mint session, redirect to the app.
2. **Identity matches but the user is disabled** → create/reopen a
   `reactivate_user` access request (subject to cooldown), redirect to
   access-pending.
3. **No identity, bootstrap applies** — `BOOTSTRAP_ADMIN_EMAIL` set, no active
   admin exists, email verified and matching → create the first active admin +
   identity, mint session. Inert once any active admin exists.
4. **No identity, `preferred_username` matches an existing HKBP user** → create a
   `link_existing_user` access request with `suggested_user_id` (never silent
   auto-link), redirect to access-pending.
5. **No match anywhere** → create a `new_user` access request, redirect to
   access-pending.

`(issuer, subject)` is the automatic identity key. `preferred_username` is the
human handle and is forced to equal `users.username`. Email is optional
supporting metadata and may be absent.

## Access requests

- `request_type ∈ {new_user, link_existing_user, reactivate_user}`.
- Discovery is passive: admin dashboard badge/list (`GET /api/v1/access-requests`
  returns `counts.pending`). No email/push/realtime for MVP.
- Approval is transactional and **does not** create a session — the user must
  sign in again. `new_user` creates the HKBP user + identity (admin supplies
  role/sektor/nama); `link_existing_user` links the identity to the chosen
  existing user; `reactivate_user` re-enables the user (bumps `session_version`).
- Rejected requests are retained; re-requests inside the 2h cooldown
  (`ACCESS_REQUEST_COOLDOWN`) are suppressed (no spam rows) and the user sees the
  pending/denied page. Disabled users re-request via `reactivate_user` under the
  same cooldown.

## Provisioning (HKBP is the source of truth)

`POST /api/v1/users`:

1. Insert `users` row with `provisioning_status = 'pending_idp'`.
2. Call the IdP admin API to create the matching username account with a random
   **unusable** bootstrap password (never stored or displayed).
3. On success: store the returned stable subject in `user_identities`, set
   `provisioning_status = 'active'`, and return a one-time setup link.
4. On failure: set `provisioning_status = 'failed_idp'`; retry via
   `POST /api/v1/users/:id/retry-provisioning`.

First credential setup uses the IdP's one-time **setup/reset link** (no
admin-handled temporary passwords); the user chooses their own password. The link
is produced by the HKBP-specific Auténtico patch (`idp/patches/0001-...`),
reusing `password_reset_tokens` and `/oauth2/reset-password`.

`POST /api/v1/users/:id/setup-link` regenerates a setup link for a provisioned
user.

## Username rename (coordinated, auth-sensitive)

`POST /api/v1/users/:id/rename`. IdP `preferred_username` and `users.username`
must stay identical. The backend renames the IdP username first, then updates
`users.username` + `user_identities.preferred_username`, bumps `session_version`,
and revokes sessions (audit `username_changed`). If the IdP has no rename API
(`IDP_SUPPORTS_USERNAME_RENAME=false`, the default) or the IdP call fails, the
local username is **not** changed; the request is reported as blocked/unsupported
and a drift item is audited (`username_change_blocked`). No silent local-only
rename.

## Sessions & invalidation

- TTL `SESSION_TTL` (default 12h). Logout revokes the row and routes through the
  IdP end-session flow so shared computers can't silently re-enter via SSO.
- Admin-sensitive changes increment `users.session_version` (role/status change,
  disable, reactivate, rename), which immediately invalidates existing cookies.
  Simple profile edits (nama) do not.

## Audit

Generic `audit_logs` table + helper (`s.audit`). MVP wires: `login`, `logout`,
`session_revoked`, `access_request_created/approved/rejected/reopened`,
`identity_linked`, `identity_email_changed`, `user_reactivated`,
`username_changed`, `username_change_blocked`, `user_provisioned`,
`setup_link_created`. Read-only via `GET /api/v1/audit-logs` (admin).

## Configuration

See `backend/.env.example`. Key vars: `OIDC_ISSUER`, `OIDC_CLIENT_ID`,
`OIDC_CLIENT_SECRET`, `OIDC_REDIRECT_URL`, `APP_BASE_URL`,
`POST_LOGOUT_REDIRECT_URL`, `ACCESS_PENDING_URL`, `SESSION_*`,
`IDP_ADMIN_BASE_URL`, `IDP_ADMIN_TOKEN`, `IDP_SUPPORTS_USERNAME_RENAME`,
`BOOTSTRAP_ADMIN_EMAIL`, `ACCESS_REQUEST_COOLDOWN`.

## IdP state backup

Nightly SQLite `.backup` of the IdP volume with 7–30 day retention (sidecar in
`idp/docker-compose.yml`). Backup path and restore command are documented in
`idp/README.md`. Restore drill not required for MVP.

## Non-browser automation

Server-side commands/import tools with direct DB/env access only. No personal
access tokens or HTTP API bearer tokens in MVP scope.
