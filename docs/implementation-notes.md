# HKBP Jatinegara MVP Implementation Notes

## Verified local runtime

The MVP runs against local SQLite when `TURSO_URL` and `TURSO_AUTH_TOKEN` are not configured. Set `DB_PATH` to change the local database path.

Production Turso setup still needs:

```env
TURSO_URL=libsql://...
TURSO_AUTH_TOKEN=...
JWT_SECRET=...
```

## Implementation deviations

- The backend uses custom Fiber middleware with `github.com/golang-jwt/jwt/v5` instead of the build guide's suggested `github.com/gofiber/jwt/v2` wrapper. This keeps role and sector claims explicit while preserving the documented JWT behavior.
- The frontend uses local Vue components and CSS instead of PrimeVue/Naive/Vuetify. The build guide marks a UI library as an implementer choice, and the implemented views remain standard Vue 3 + Vite + TypeScript.
- Frontend type checking runs with `vue-tsc --noEmit` before `vite build` so TypeScript does not emit generated JavaScript beside source files.
