# ADR 0003: Self-host libSQL as the default database runtime

## Status
Accepted

## Context
The initial MVP supported managed Turso credentials and fell back to embedded SQLite when those credentials were unavailable. The project now wants to self-host the database while keeping Turso/libSQL compatibility.

## Decision
Run a self-hosted libSQL server using the official `ghcr.io/tursodatabase/libsql-server` Docker image. The Go backend connects over the libSQL remote protocol through `github.com/tursodatabase/libsql-client-go/libsql`.

Local development and single-host production use:

```env
TURSO_URL=http://127.0.0.1:8081
TURSO_AUTH_TOKEN=
```

Managed Turso Cloud remains compatible by replacing those values with a `libsql://...` URL and token.

## Consequences

### Positive
- Keeps the database self-hosted.
- Removes embedded SQLite as the default runtime path.
- Preserves compatibility with Turso/libSQL SDKs and managed Turso Cloud.
- Makes the database a network service, similar operationally to PostgreSQL or MySQL.

### Negative
- Adds Docker and container operations to local and production setup.
- Backups, upgrades, monitoring, and volume recovery become project responsibilities.
- Self-hosted libSQL keeps SQLite/libSQL's single-writer characteristics.
