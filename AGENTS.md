# AGENTS.md — HKBP Jatinegara

This repository is intended for autonomous Codex implementation, including long-running `/goal` mode.

> **Auth note (current source of truth):** Authentication has migrated from the
> original app-local JWT/password model to **backend-mediated OIDC against a
> dedicated IdP (Auténtico) with an opaque `hkbp_session` cookie**. `CONTEXT.md`,
> `docs/AUTH-DESIGN.md`, and `docs/api-spec.md` are authoritative for auth. Do
> NOT reintroduce password login, refresh tokens, or SPA Bearer auth. Sections
> below that still describe JWT are historical context only.

## Mission
Build a complete migration of the legacy HKBP Jatinegara church management system from PHP/Laravel + standalone attendance pages into:

- **Frontend:** Vue 3 + Vite + TypeScript SPA
- **Backend:** Go + Fiber v2 JSON API
- **Database:** Turso (libSQL/SQLite)
- **Auth:** Backend-mediated OIDC (Auténtico IdP) + opaque `hkbp_session` cookie. No app-local JWT/passwords.

## Required reading before coding
Read these files first, in this order:

1. `README.md` — repository overview and target structure
2. `CONTEXT.md` — domain glossary and terminology
3. `docs/build-guide.md` — exact implementation sequence and file structure
4. `docs/database-schema.md` — Turso schema/DDL and data model
5. `docs/api-spec.md` — endpoint contract and response shapes
6. `docs/adr/0001-use-turso.md` and `docs/adr/0002-vue-go-architecture.md` — architecture rationale
7. `legacy/aplikasidbruas/resources/views/content/*.blade.php` — legacy UI field reference, especially family/member forms
8. `legacy/absen/index.html` and `legacy/absen/chunk_upload.php` — attendance reference

## Critical constraints

- Preserve the documented domain terms. If you need a new concept, add it to `CONTEXT.md`.
- Do not copy legacy malware/webshell files into the new app. Known suspicious files from the original host were intentionally excluded from the current tree:
  - root `cek.php` on the original host
  - `legacy/aplikasidbruas/app/Console/bo.php`
- Do not commit secrets. Use `.env.example` only.
- Keep generated dependencies out of git (`node_modules`, Go build outputs, local DB files).
- Commit source/docs only.
- Prefer small, coherent commits if working interactively. For one-shot `/goal`, a single implementation commit is acceptable if verified.

## Implementation expectations

The one-shot build should produce a runnable app, not just stubs.

Minimum viable completion means:

### Backend
- `backend/` Go module initializes successfully.
- Fiber server starts on configured port.
- Migration runner applies all SQL migrations.
- First admin is created via OIDC login + `BOOTSTRAP_ADMIN_EMAIL` (no seeded password admin).
- Auth endpoints work (backend-mediated OIDC + cookie session):
  - `GET /api/v1/auth/login`
  - `GET /api/v1/auth/callback`
  - `GET /api/v1/auth/me`
  - `POST /api/v1/auth/logout`
- CRUD/list endpoints exist for:
  - sectors
  - roles
  - users
  - families with nested members
  - members
  - offerings + report
  - sintua
  - attendance
- Role middleware protects admin-only routes.
- Credentials are owned by the IdP; HKBP stores no password hashes.
- Sessions are opaque cookies backed by `app_sessions.token_hash` (stored hashed).

### Frontend
- `frontend/` Vue 3 + Vite + TypeScript app installs and builds.
- Login page is a single "Sign in with HKBP Account" CTA → `GET /api/v1/auth/login`.
- Auth store holds only the current user (resolved via `GET /api/v1/auth/me`); no tokens in the browser.
- API client uses cookie credentials (`withCredentials`); no Bearer/refresh interceptor.
- Authenticated layout has sidebar/nav.
- Views exist for:
  - dashboard
  - sectors
  - users
  - families
  - family detail/form
  - member list
  - offerings
  - offering report
  - sintua
  - attendance
- Forms cover the fields in `docs/api-spec.md` / `docs/database-schema.md`.

## Verification commands

Run these before declaring success:

```bash
# Backend
cd backend
go mod tidy
go test ./...
go run cmd/server/main.go
```

In another shell, verify at least:

```bash
curl -s http://localhost:8080/health
# Unauthenticated probe returns 401 (cookie session required).
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/api/v1/auth/me
# With OIDC_ISSUER set, /auth/login 302-redirects to the IdP authorize URL.
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8080/api/v1/auth/login
```

Full browser OIDC requires a running IdP; the callback decision logic, cookie
session validation, cooldown, and rename-drift handling are covered by
`go test ./...` in `backend/`.

```bash
# Frontend
cd frontend
pnpm install
pnpm build
```

If Turso credentials are unavailable, use local SQLite/libSQL fallback for tests and document the exact Turso env vars still needed.

## Reporting expectations

When done, report:

- What was built
- What commands were run
- Test/build results
- Any remaining manual setup (Turso credentials, IdP deployment, OIDC client config)
- The bootstrap admin path (`BOOTSTRAP_ADMIN_EMAIL` + first OIDC login)
- Any deviations from docs and why
