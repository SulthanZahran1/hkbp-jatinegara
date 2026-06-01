# HKBP Jatinegara MVP Implementation Notes

## Verified local runtime

The MVP runs against a self-hosted Turso-compatible libSQL server by default.

Start the database with:

```bash
docker compose up -d turso
```

Then run the backend with:

```env
TURSO_URL=http://127.0.0.1:8081
TURSO_AUTH_TOKEN=
JWT_SECRET=...
```

Managed Turso Cloud can still use `libsql://...` plus `TURSO_AUTH_TOKEN`.

## Implementation deviations

- The backend uses custom Fiber middleware with `github.com/golang-jwt/jwt/v5` instead of the build guide's suggested `github.com/gofiber/jwt/v2` wrapper. This keeps role and sector claims explicit while preserving the documented JWT behavior.
- The frontend uses local Vue components and CSS instead of PrimeVue/Naive/Vuetify. The build guide marks a UI library as an implementer choice, and the implemented views remain standard Vue 3 + Vite + TypeScript.
- Frontend type checking runs with `vue-tsc --noEmit` before `vite build` so TypeScript does not emit generated JavaScript beside source files.
