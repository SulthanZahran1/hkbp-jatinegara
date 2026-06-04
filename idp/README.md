# HKBP IdP — Auténtico (with setup-password-link patch)

HKBP authentication is delegated to a dedicated lightweight OIDC Identity
Provider: **Auténtico**, validated by spike `spikes/001-autentico-idp/`. This
directory is the HKBP-specific image/fork path: pinned upstream + one minimal
patch that adds an admin-generated **setup-password-link** so users set their
own password (no admin-handled temporary passwords, no email send).

## What the patch does

`patches/0001-admin-setup-password-link.patch` adds
`POST /admin/api/users/{id}/setup-password-link`, which reuses Auténtico's
existing `password_reset_tokens` and `/oauth2/reset-password` flow to return a
one-time setup/reset URL. It is the validated spike patch promoted to the build.
`patches/0002-register-setup-link-route.md` + `scripts/register-route.sh` wire
the route (the only step that depends on upstream's current router shape).

The HKBP backend calls this endpoint during provisioning and from
`POST /api/v1/users/:id/setup-link`. HKBP never stores or displays the bootstrap
password it sends when creating the IdP account.

## Build

```bash
# From idp/. Pin AUTENTICO_REF to a reviewed commit before deploying.
docker build --build-arg AUTENTICO_REF=<commit-sha> -t hkbp-autentico:latest .
```

The build clones upstream, applies the patch, registers the route, runs the
focused Auténtico tests (`pkg/passwordreset`, `pkg/user`), builds the admin and
account UI dist assets, and compiles the binary. The resolved upstream commit is
recorded at `/app/UPSTREAM_COMMIT` in the image.

## Deploy (auth.hkbp.zahranm.cloud)

Separate Dokploy/Traefik app from the HKBP application stack. Routing/TLS is via
the external Traefik on the shared `web` network.

```bash
cp .env.example .env   # fill in secrets
docker compose up -d --build
```

Key configuration (`.env`):

- `AUTENTICO_APP_URL=https://auth.hkbp.zahranm.cloud` — must equal the HKBP
  backend `OIDC_ISSUER`.
- `AUTENTICO_PROFILE_FIELD_EMAIL=hidden` — enables no-personal-email
  username/password users (confirmed in spike).
- `AUTENTICO_DB_FILE_PATH=/app/data/autentico.db` — SQLite on the `idp-data`
  volume.
- HKBP OIDC client (`HKBP_OIDC_CLIENT_ID`/`_SECRET`/`_REDIRECT_URI`) — must match
  the HKBP backend `OIDC_CLIENT_ID`/`OIDC_CLIENT_SECRET`/`OIDC_REDIRECT_URL`.
- `AUTENTICO_ADMIN_API_TOKEN` — the HKBP backend's `IDP_ADMIN_TOKEN`.

## Backup & restore

The `idp-backup` sidecar runs a nightly `sqlite3 .backup` of the live DB to the
`idp-backups` volume and prunes files older than `IDP_BACKUP_RETENTION_DAYS`
(default 14; target 7–30).

Manual backup:

```bash
docker exec hkbp-idp sqlite3 /app/data/autentico.db ".backup '/app/data/manual-$(date +%F).db'"
```

Restore (IdP stopped):

```bash
docker compose stop idp
docker run --rm -v idp_idp-data:/data -v idp_idp-backups:/backups alpine:3.20 \
  sh -c "cp /backups/autentico-<timestamp>.db /data/autentico.db"
docker compose start idp
```

A restore drill is not required for MVP, but the path and command above are the
documented recovery procedure.

## Maintenance

Keep the fork limited to the setup-link endpoint. To update upstream: bump
`AUTENTICO_REF`, rebuild, and re-run the build (it re-applies the patch and
re-runs the focused tests). If `register-route.sh` can no longer find its anchor,
update it and `patches/0002-register-setup-link-route.md` for the new revision.
Consider upstreaming the setup-link endpoint to drop the fork entirely.
