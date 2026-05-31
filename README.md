# HKBP Jatinegara — Church Management System

Modernizing HKBP Jatinegara's church administration from a legacy PHP stack to a modern stack.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Vue 3 + Vite + TypeScript |
| Backend | Go + Fiber v2 |
| Database | Turso (libSQL/SQLite) |
| Auth | JWT (access + refresh tokens) |
| HTTP Client | Axios (Vue) |

## Decisions Made

- ✅ **Architecture:** Vue SPA + Go JSON API (Fiber)
- ✅ **DB:** Turso (libSQL/SQLite)
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
- Turso CLI (for local dev)

### Backend
```bash
cd backend
cp .env.example .env
# Edit .env with Turso database URL and JWT secret
go mod tidy
go run cmd/server/main.go
```

### Frontend
```bash
cd frontend
pnpm install
pnpm dev
```

## Legacy Reference
The original PHP source code is in `legacy/` for reference during migration:
- `legacy/absen/` — Attendance/presence app (MDB Bootstrap)
- `legacy/aplikasidbruas/` — Laravel 10 thin client (proxied to external API)
