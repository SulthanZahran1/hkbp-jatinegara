# ADR 0002: Vue 3 + Go Fiber + Turso Architecture

## Status
Accepted

## Context
HKBP Jatinegara's legacy system consists of a Laravel thin client (server-rendered Blade templates) that proxies all data through a separate PHP backend API at `dbruas-be.hkbpjtn.web.id`. The frontend also includes a standalone HTML attendance app (`absen/`) and the backend api is currently down.

The migration targets a modern, maintainable stack suitable for <10 concurrent users, with the ability to serve both web and future mobile clients.

## Decision

### Frontend: Vue 3 + TypeScript + Vite
- **Vue 3** — Composition API for clean component logic, mature ecosystem for admin UIs
- **TypeScript** — Type safety across the full stack
- **Vite** — Fast HMR, native ESM, no webpack complexity
- **Pinia** for state management (not covered in grilling, but the natural Vue 3 choice)
- **Vue Router** for SPA routing
- Admin UI library TBD by implementer (PrimeVue recommended)

### Backend: Go + Fiber v2
- **Fiber** — Express-like, fast, with built-in middleware (CORS, logger, recover)
- **standard layout** — `cmd/server/main.go`, `internal/` packages
- **database/sql** with `libsql/client` for Turso connectivity
- **golang-jwt** library for JWT handling

### Database: Turso (libSQL/SQLite)
- SQLite-compatible schema, edge-distributed reads
- Single-file migrations, easy local dev with `turso dev`
- Go driver: `github.com/tursodatabase/libsql-client-go`

### Auth: JWT
- Short-lived access tokens (15 min) + long-lived refresh tokens (7 days)
- Refresh tokens stored hashed in `refresh_tokens` table
- No session store needed — stateless auth

## Consequences
- Clean separation: one Go process serves JSON, one Vue SPA renders UI
- Single codebase to understand and deploy
- Easy to add mobile clients later (same JSON API)
- SQLite scales fine for <10 concurrent users
- Legacy PHP code remains as reference in `legacy/` directory
