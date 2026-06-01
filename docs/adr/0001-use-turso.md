# ADR 0001: Use Turso as the Primary Database

## Status
Accepted, amended by [ADR 0003](0003-self-host-libsql.md)

## Context
HKBP Jatinegara is being migrated from a legacy PHP/Laravel thin client plus external backend API into a TypeScript SPA and Go JSON API.

Expected concurrency is very small: fewer than 10 active users. The system stores church administration data: members, families, sectors, users, attendance, and monthly offerings.

The legacy backend API (`dbruas-be.hkbpjtn.web.id`) is currently unreachable, and its database schema is not available. The new schema is being designed from the legacy UI/domain forms rather than copied from production.

## Decision
Use **Turso/libSQL-compatible database technology** as the primary database family.

ADR 0003 changes the default runtime from managed Turso Cloud to a self-hosted libSQL server, while preserving Turso/libSQL SDK compatibility.

## Consequences

### Positive
- Very low operational burden.
- SQLite-compatible schema keeps local development simple.
- Good fit for small concurrency and CRUD-heavy admin workflows.
- Easy backup/export story compared with operating a dedicated PostgreSQL instance.
- Go backend can use standard SQL access patterns.

### Negative
- Not ideal for high-write concurrency or complex analytical workloads.
- Requires awareness of SQLite constraints and migration behavior.
- Some PostgreSQL-specific features are unavailable.

## Alternatives Considered

### PostgreSQL
Would be the default for a larger multi-user admin system, but adds operational overhead that is not justified for fewer than 10 concurrent users.

### MySQL/MariaDB
Legacy hosting already uses MySQL, but the goal is to simplify operations and migrate away from the old shared-hosting PHP stack.

### Embedded SQLite Only
Simplest runtime, but Turso provides hosted backups, remote access, and easier deployment without running our own DB server.
