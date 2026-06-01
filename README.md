# HKBP Jatinegara — Church Management System

Modernizing HKBP Jatinegara's church administration from a legacy PHP stack to a modern stack.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Vue 3 + Vite + TypeScript |
| Backend | Go + Fiber v2 |
| Database | Self-hosted Turso-compatible libSQL server |
| Auth | JWT (access + refresh tokens) |
| Deployment | Dockerfile app image for Dokploy; `docker-compose.yml` for local libSQL |

## Decisions Made

- ✅ **Architecture:** Vue SPA + Go JSON API (Fiber)
- ✅ **DB:** Self-hosted libSQL server, compatible with Turso SDKs
- ✅ **Auth:** JWT access + refresh tokens
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
│   │   └── auth/               # JWT generation/validation
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
├── docs/
│   ├── adr/                    # Architecture Decision Records
│   └── api-spec.md             # API specification
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

### Backend
```bash
cd backend
cp .env.example .env
# Keep TURSO_URL=http://127.0.0.1:8081 for self-hosted libSQL, then set JWT_SECRET
go mod tidy
go run cmd/server/main.go
```

### Frontend
```bash
cd frontend
pnpm install
pnpm dev
```

### Production Docker image

The root `Dockerfile` builds the Vue SPA with `VITE_API_BASE_URL=/api/v1`, builds the Go API, and serves both from one container on port `8080`.

```bash
docker build -t hkbp-jatinegara .
docker run --rm -p 8080:8080 \
  -e TURSO_URL=http://host.docker.internal:8081 \
  -e JWT_SECRET=change-me \
  hkbp-jatinegara
```

For Dokploy, configure the app service as Dockerfile build type and set at least:

```env
PORT=8080
TURSO_URL=http://<libsql-service>:8080
TURSO_AUTH_TOKEN=
JWT_SECRET=<random-secret>
CORS_ORIGIN=https://hkbp.zahranm.cloud
STATIC_DIR=/app/frontend/dist
UPLOAD_DIR=/app/uploads
```

## Legacy Reference
The original PHP source code is in `legacy/` for reference during migration:
- `legacy/absen/` — Attendance/presence app (MDB Bootstrap)
- `legacy/aplikasidbruas/` — Laravel 10 thin client (proxied to external API)
