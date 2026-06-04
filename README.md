# HKBP Jatinegara — Church Management System

Modernizing HKBP Jatinegara's church administration from a legacy PHP stack to a modern stack.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Vue 3 + Vite + TypeScript |
| Backend | Go + Fiber v2 |
| Database | Self-hosted Turso-compatible libSQL server |
| Auth | Backend-mediated OIDC (Auténtico IdP) + opaque `hkbp_session` cookie |
| Deployment | Dockerfile app image for Dokploy; `docker-compose.yml` for local libSQL; `idp/` for the IdP |

## Decisions Made

- ✅ **Architecture:** Vue SPA + Go JSON API (Fiber)
- ✅ **DB:** Self-hosted libSQL server, compatible with Turso SDKs
- ✅ **Auth:** Backend-mediated OIDC against a dedicated IdP (Auténtico); HTTP-only opaque cookie sessions; no app-local passwords/JWTs. See [`docs/AUTH-DESIGN.md`](docs/AUTH-DESIGN.md).
- ✅ **Frontend:** Vue 3 + Vite + TypeScript
- ✅ **Schema:** Clean design from legacy UI

## Directory Structure

```
hkbp-jatinegara/
├── backend/                    # Go Fiber API
│   ├── cmd/
│   │   └── server/
│   │       └── main.go         # Entry point
│   ├── internal/
│   │   ├── config/             # Configuration loading
│   │   ├── database/           # DB connection, migrations
│   │   ├── middleware/         # Auth, CORS, logging
│   │   ├── models/             # Data models / structs
│   │   ├── handlers/           # HTTP handlers (per resource)
│   │   ├── services/           # Business logic layer
│   │   └── auth/               # OIDC login/callback, cookie sessions
│   ├── migrations/             # SQL migration files
│   ├── go.mod
│   ├── go.sum
│   └── .env.example
├── frontend/                   # Vue 3 + Vite SPA
│   ├── src/
│   │   ├── api/                # Axios API client
│   │   ├── assets/             # Static assets
│   │   ├── components/         # Reusable components
│   │   ├── composables/        # Vue composables
│   │   ├── layouts/            # App layouts
│   │   ├── router/             # Vue Router config
│   │   ├── stores/             # Pinia stores
│   │   ├── types/              # TypeScript interfaces
│   │   ├── utils/              # Utility functions
│   │   ├── views/              # Page components
│   │   ├── App.vue
│   │   └── main.ts
│   ├── index.html
│   ├── vite.config.ts
│   ├── tsconfig.json
│   ├── package.json
│   └── .env.example
├── legacy/                     # Original PHP source (reference only)
│   ├── absen/
│   └── aplikasidbruas/
├── idp/                        # HKBP Auténtico IdP image + setup-link patch + Dokploy deploy
├── docs/
│   ├── adr/                    # Architecture Decision Records
│   ├── api-spec.md             # API specification
│   └── AUTH-DESIGN.md          # OIDC + cookie session design
├── CONTEXT.md                  # Domain glossary
├── .gitignore
└── README.md
```

## Setup

### Prerequisites
- Go 1.22+
- Node.js 20+
- pnpm (or npm)
- Docker + Docker Compose

### Database
```bash
docker compose up -d turso
```

The self-hosted libSQL server listens on `http://127.0.0.1:8081` and stores data in the `turso-data` Docker volume.

### Identity Provider (Auténtico)
Authentication is delegated to a dedicated IdP. For local dev point the backend
at any reachable Auténtico instance, or deploy the bundled image (see
[`idp/README.md`](idp/README.md)). The server still starts without a reachable
IdP — `/health` works and `/api/v1/auth/login` returns 503 until OIDC discovery
succeeds.

### Backend
```bash
cd backend
cp .env.example .env
# Keep TURSO_URL=http://127.0.0.1:8081 for self-hosted libSQL.
# Set OIDC_ISSUER / OIDC_CLIENT_ID / OIDC_CLIENT_SECRET, APP_BASE_URL,
# IDP_ADMIN_BASE_URL / IDP_ADMIN_TOKEN, and BOOTSTRAP_ADMIN_EMAIL.
go mod tidy
go run cmd/server/main.go
```

### Frontend
```bash
cd frontend
pnpm install
pnpm dev   # /api is proxied to the Go backend so the session cookie is same-origin
```

### Production Docker image

The root `Dockerfile` builds the Vue SPA with `VITE_API_BASE_URL=/api/v1`, builds the Go API, and serves both from one container on port `8080`.

```bash
docker build -t hkbp-jatinegara .
docker run --rm -p 8080:8080 \
  -e TURSO_URL=http://host.docker.internal:8081 \
  -e OIDC_ISSUER=https://auth.hkbp.zahranm.cloud \
  -e OIDC_CLIENT_ID=hkbp-app -e OIDC_CLIENT_SECRET=... \
  -e APP_BASE_URL=https://hkbp.zahranm.cloud \
  -e IDP_ADMIN_TOKEN=... \
  hkbp-jatinegara
```

For Dokploy, configure the app service as Dockerfile build type. `SQLITE_PATH` is the simplest single-container option; for managed Turso/libSQL, omit `SQLITE_PATH` and set `TURSO_URL` / `TURSO_AUTH_TOKEN` instead. Full auth/IdP variables are documented in `backend/.env.example` and `docs/AUTH-DESIGN.md`.

```env
PORT=8080
SQLITE_PATH=/app/data/hkbp-jatinegara.db
TURSO_URL=
TURSO_AUTH_TOKEN=
OIDC_ISSUER=https://auth.hkbp.zahranm.cloud
OIDC_CLIENT_ID=hkbp-app
OIDC_CLIENT_SECRET=<oidc-client-secret>
APP_BASE_URL=https://hkbp.zahranm.cloud
IDP_ADMIN_BASE_URL=https://auth.hkbp.zahranm.cloud
IDP_ADMIN_TOKEN=<idp-admin-api-token>
BOOTSTRAP_ADMIN_EMAIL=<first-admin-verified-email>
STATIC_DIR=/app/frontend/dist
UPLOAD_DIR=/app/uploads
```

## Legacy Reference
The original PHP source code is in `legacy/` for reference during migration:
- `legacy/absen/` — Attendance/presence app (MDB Bootstrap)
- `legacy/aplikasidbruas/` — Laravel 10 thin client (proxied to external API)
