# HKBP Jatinegara — Church Management System

Migrating from legacy PHP (Laravel + standalone) to **TypeScript (frontend) + Go (backend)**.

## Architecture

- **Frontend:** TypeScript (TBD — React, Vue, or Svelte)
- **Backend:** Go
- **Database:** TBD

## Modules

- Attendance / Presence tracking
- Member & family data management
- Sector management
- Elder (Sintua) management
- Monthly offerings
- Reports
- User management & authentication

---

## Decisions Made

- ✅ **Architecture:** TS SPA + Go JSON API
- ✅ **DB:** Turso (libSQL/SQLite)
- ✅ **Auth:** JWT access + refresh tokens
- ✅ **Go framework:** Fiber
- ✅ **Frontend:** Vue + Vite

*Architecture decisions are being documented via the grilling process.*
