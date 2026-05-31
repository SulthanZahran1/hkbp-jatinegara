# AGENTS.md — HKBP Jatinegara

This repository is intended for autonomous Codex implementation, including long-running `/goal` mode.

## Mission
Build a complete migration of the legacy HKBP Jatinegara church management system from PHP/Laravel + standalone attendance pages into:

- **Frontend:** Vue 3 + Vite + TypeScript SPA
- **Backend:** Go + Fiber v2 JSON API
- **Database:** Turso (libSQL/SQLite)
- **Auth:** JWT access + refresh tokens

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
- Default admin user is seeded if no users exist.
- Auth endpoints work:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/refresh`
  - `GET /api/v1/auth/me`
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
- Passwords use bcrypt.
- Refresh tokens are stored hashed.

### Frontend
- `frontend/` Vue 3 + Vite + TypeScript app installs and builds.
- Login page works against backend.
- Auth store stores access/refresh tokens and current user.
- Axios interceptor attaches Bearer token and refreshes on 401.
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
curl -s -X POST http://localhost:8080/api/v1/auth/login \
  -H 'content-type: application/json' \
  -d '{"username":"admin","password":"admin123"}'
```

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
- Any remaining manual setup (especially Turso credentials)
- The exact admin bootstrap credentials
- Any deviations from docs and why
