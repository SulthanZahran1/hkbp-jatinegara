# HKBP Jatinegara — Build Guide

One-shot implementation guide for the next session. Follow this order to build everything.

---

## Phase 1: Backend Scaffolding

### 1.1 Initialize Go module
```bash
cd backend
go mod init github.com/SulthanZahran1/hkbp-jatinegara/backend
```

### 1.2 Install dependencies
```bash
go get github.com/gofiber/fiber/v2
go get github.com/gofiber/jwt/v2
go get github.com/golang-jwt/jwt/v5
go get github.com/tursodatabase/libsql-client-go
go get github.com/mattn/go-sqlite3  # for local dev
go get golang.org/x/crypto/bcrypt
go get github.com/joho/godotenv
```

### 1.3 File structure to create

```
backend/
├── cmd/server/main.go              # Entry point — Fiber app, routes, start
├── internal/
│   ├── config/config.go            # Load .env, return Config struct
│   ├── database/database.go        # Turso connection pool
│   ├── database/migrations.go      # Migration runner (exec SQL files)
│   ├── middleware/auth.go          # JWT middleware (skip login/refresh)
│   ├── middleware/role.go          # Role-based access middleware
│   ├── models/
│   │   ├── user.go                 # User struct + DB methods
│   │   ├── sector.go               # Sector struct + DB methods
│   │   ├── role.go                 # Role struct + DB methods
│   │   ├── family.go               # Family struct + DB methods (incl. nested members)
│   │   ├── member.go               # Member struct + DB methods
│   │   ├── offering.go             # Offering struct + DB methods
│   │   ├── sintua.go               # Sintua (elder) struct + DB methods
│   │   └── attendance.go           # Attendance struct + DB methods
│   ├── handlers/
│   │   ├── auth.go                 # login, refresh, me
│   │   ├── users.go                # CRUD users
│   │   ├── sectors.go              # CRUD sectors
│   │   ├── roles.go                # List roles
│   │   ├── families.go            # CRUD families (with nested members)
│   │   ├── members.go             # CRUD members + photo upload
│   │   ├── offerings.go           # CRUD offerings + report
│   │   ├── sintua.go              # CRUD sintua
│   │   └── attendance.go          # CRUD attendance
│   ├── services/
│   │   ├── auth_service.go        # JWT generate/validate, password hashing
│   │   └── upload_service.go      # File upload handling
│   └── auth/
│       └── jwt.go                  # JWT helper functions (not middleware)
├── migrations/                     # SQL files (from docs/database-schema.md)
│   ├── 001_create_sectors.sql
│   ├── 002_create_roles.sql
│   ├── 003_create_users.sql
│   ├── 004_create_families.sql
│   ├── 005_create_members.sql
│   ├── 006_create_offerings.sql
│   ├── 007_create_sintua.sql
│   ├── 008_create_attendance.sql
│   └── 009_create_refresh_tokens.sql
├── uploads/                        # Photo upload directory
├── .env.example
├── .gitkeep
├── go.mod
└── go.sum
```

### 1.4 Key implementation notes

**main.go** — Standard Fiber bootstrap:
```go
// 1. Load config
// 2. Init DB
// 3. Run migrations
// 4. Create Fiber app
// 5. Setup middleware (CORS, Logger, Recover)
// 6. Setup routes (see API spec)
// 7. Seed admin user if none exists
// 8. Listen on PORT (default 8080)
```

**Auth middleware** — Use `github.com/gofiber/jwt/v2`:
- Skip `/api/v1/auth/login` and `/api/v1/auth/refresh`
- Extract user_id from JWT claims, set in context

**Password hashing** — `bcrypt` with cost 12

**JWT claims**:
```go
type Claims struct {
    UserID   uint   `json:"user_id"`
    RoleID   uint   `json:"role_id"`
    SectorID *uint  `json:"sektor_id"`
    jwt.RegisteredClaims
}
```

**Migration runner** — Read files from `migrations/` directory, execute in order, track applied migrations in a `_migrations` table.

**Seeder** — On first run, create default admin user:
- username: `admin`, password: `admin123` (user should change immediately)
- role: admin, sector: null (super admin)

---

## Phase 2: Frontend Scaffolding

### 2.1 Create Vue project
```bash
cd frontend
pnpm create vite . --template vue-ts
pnpm install
```

### 2.2 Install dependencies
```bash
pnpm add vue-router@4 pinia axios
# UI library — pick one:
pnpm add primevue          # recommended — good form/table/dialog components
# or: pnpm add naive-ui    # also excellent for admin
# or: pnpm add vuetify     # Material Design
```

### 2.3 File structure to create

```
frontend/
├── src/
│   ├── api/
│   │   ├── client.ts            # Axios instance with interceptors (JWT attach, refresh)
│   │   ├── auth.ts              # login(), refresh(), me()
│   │   ├── sectors.ts           # sector CRUD calls
│   │   ├── users.ts             # user CRUD calls
│   │   ├── families.ts          # family CRUD calls
│   │   ├── members.ts           # member CRUD calls
│   │   ├── offerings.ts         # offering CRUD calls
│   │   ├── sintua.ts            # sintua CRUD calls
│   │   └── attendance.ts        # attendance CRUD calls
│   ├── assets/
│   │   └── logo.webp
│   ├── components/
│   │   ├── AppLayout.vue        # Sidebar + navbar + router-view
│   │   ├── Sidebar.vue           # Navigation menu (collapsible)
│   │   ├── Navbar.vue            # Top bar (user info, logout)
│   │   ├── DataTable.vue         # Reusable table wrapper (sort, filter, export)
│   │   ├── FormField.vue         # Reusable form field wrapper
│   │   └── Pagination.vue        # Pagination component
│   ├── composables/
│   │   ├── useAuth.ts            # Login/logout, token management, user state
│   │   └── usePagination.ts      # Pagination logic
│   ├── layouts/
│   │   └── DefaultLayout.vue     # Layout with sidebar + navbar
│   ├── router/
│   │   └── index.ts              # Vue Router config with auth guards
│   ├── stores/
│   │   ├── auth.ts               # Pinia auth store
│   │   └── app.ts                # App-wide state (sidebar collapsed, etc.)
│   ├── types/
│   │   ├── api.ts                # API response types
│   │   ├── user.ts               # User type
│   │   ├── sector.ts             # Sector type
│   │   ├── family.ts             # Family type
│   │   ├── member.ts             # Member type
│   │   ├── offering.ts           # Offering type
│   │   ├── sintua.ts             # Sintua type
│   │   └── attendance.ts         # Attendance type
│   ├── utils/
│   │   └── formatters.ts         # Date, currency, name formatters
│   ├── views/
│   │   ├── LoginView.vue         # Login page
│   │   ├── DashboardView.vue     # Dashboard (stats overview)
│   │   ├── sectors/
│   │   │   └── SectorList.vue    # Sector CRUD page
│   │   ├── users/
│   │   │   ├── UserList.vue      # User management page
│   │   │   └── UserForm.vue      # User create/edit form (modal)
│   │   ├── families/
│   │   │   ├── FamilyList.vue    # Family listing with search/filter
│   │   │   ├── FamilyDetail.vue  # Single family view with members
│   │   │   └── FamilyForm.vue    # Family + members create/edit form
│   │   ├── members/
│   │   │   └── MemberList.vue    # Members search/listing
│   │   ├── offerings/
│   │   │   ├── OfferingList.vue  # Monthly offerings entry/list
│   │   │   └── OfferingReport.vue # Monthly report with totals
│   │   ├── sintua/
│   │   │   └── SintuaList.vue    # Elders management
│   │   └── attendance/
│   │       └── AttendanceView.vue # Attendance recording/viewing
│   ├── App.vue
│   └── main.ts
├── index.html
├── vite.config.ts
├── tsconfig.json
├── tsconfig.node.json
├── package.json
└── .env.example
```

### 2.4 Key implementation notes

**Axios interceptor** (`client.ts`):
```typescript
// Request: attach Bearer token from localStorage
// Response 401: attempt refresh, retry original request
// On refresh failure: redirect to login
```

**Auth store** (`stores/auth.ts`):
```typescript
// State: accessToken, refreshToken, user
// Actions: login(), logout(), refreshToken()
// Getters: isAuthenticated, isAdmin, currentUser
```

**Router guard** (`router/index.ts`):
```typescript
// beforeEach: check isAuthenticated, redirect to /login if not
// Role-based: admin routes have meta.roles=['admin']
```

**API client files** follow this pattern:
```typescript
// Example: sectors.ts
import client from './client';
import type { Sector } from '@/types/sector';

export const getSectors = () => client.get<Sector[]>('/sectors');
export const createSector = (data: { name: string }) => client.post('/sectors', data);
export const updateSector = (id: number, data: { name: string }) => client.put(`/sectors/${id}`, data);
export const deleteSector = (id: number) => client.delete(`/sectors/${id}`);
```

### 2.5 Views to create (in order)

1. **LoginView** — Simple login form (username + password), calls auth store login
2. **DashboardView** — Cards showing: total members, families, sectors, this month's offerings total
3. **SectorList** — Table with CRUD (admin only for create/delete)
4. **UserList** — Table with create/edit/delete (admin only)
5. **FamilyList** — Table with filter by sector, search by family head name
6. **FamilyForm** — Complex form: family details + dynamic member cards (head, spouse, children)
7. **FamilyDetail** — Read-only view of family with all members
8. **MemberList** — Searchable member directory across all sectors
9. **OfferingList** — Table to add/view monthly offerings per family
10. **OfferingReport** — Month/year picker + totals per sector
11. **SintuaList** — Table to assign/remove elder status
12. **AttendanceView** — Date picker + member checklist by seksi

---

## Phase 3: Integration

### 3.1 Environment setup
1. Create Turso database: `turso db create hkbp-jatinegara`
2. Copy backend `.env.example` → `.env`, fill in values
3. Copy frontend `.env.example` → `.env`
4. Run migrations against Turso: `turso db shell hkbp-jatinegara < migrations/001_*.sql` (etc.)

### 3.2 Run locally
```bash
# Terminal 1 — Backend
cd backend && go run cmd/server/main.go

# Terminal 2 — Frontend
cd frontend && pnpm dev
```

### 3.3 Verify
1. Visit `http://localhost:5173`
2. Login with admin/admin123
3. Create a sector, a user, a family with members
4. Verify offerings and attendance work

---

## Legacy Data Import

To import existing data from production:
1. If the `dbruas-be` API comes back online, build a one-time import script
2. Map the API response shapes to the new schema
3. Run as a Go service or direct SQL insert

The legacy PHP code in `legacy/` documents the original forms and domain model — use it as the source of truth for any field-level decisions.
