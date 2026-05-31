# Codex `/goal` One-Shot Prompt

Use this file as the exact prompt when starting Codex Goal mode from the repository root.

## Codex version requirement

Goals are available in Codex CLI `0.128.0+`. This machine has been verified with `codex-cli 0.133.0`, and `codex features list` reports:

```text
goals stable true
```

If another machine does not show `/goal` in the slash command list, enable it:

```bash
codex features enable goals
# or add to ~/.codex/config.toml:
# [features]
# goals = true
```

## How to start

From repo root:

```bash
cd ~/hosted_projects/hkbp-jatinegara
codex --search --sandbox workspace-write
```

Then paste this in the interactive Codex composer:

```text
/goal Build the complete HKBP Jatinegara migration MVP described in this repository. Read AGENTS.md, README.md, CONTEXT.md, docs/build-guide.md, docs/database-schema.md, docs/api-spec.md, and docs/adr/*.md before editing. Implement a runnable Go Fiber backend in backend/ and a Vue 3 + Vite + TypeScript frontend in frontend/. Use Turso/libSQL with local SQLite fallback if Turso credentials are unavailable. Implement JWT access+refresh auth, migrations, default admin seeding, CRUD/list endpoints for sectors, roles, users, families with nested members, members, offerings/report, sintua, and attendance, plus the corresponding Vue views, auth store, router guard, API client, and forms. Verify with go test ./..., backend startup or health endpoint, and pnpm build. Stop only when the app is runnable and verified, or when a hard blocker remains; if blocked, report the exact blocker, attempted paths, and what input is needed.
```

## Goal lifecycle cheatsheet

```text
/goal                    # inspect current goal
/goal pause              # pause active goal
/goal resume             # resume paused goal
/goal clear              # clear goal
```

## Recommended stopping condition

Codex should stop only when:

1. backend compiles and tests pass,
2. backend exposes `/health` and auth endpoint works with seeded admin,
3. frontend installs and `pnpm build` passes,
4. docs are updated for any implementation deviations,
5. git status is clean or changes are committed.

## Expected manual setup after build

If Turso credentials are not available during implementation, the app should still run against local SQLite/libSQL. Later production setup will need:

```env
TURSO_URL=libsql://...
TURSO_AUTH_TOKEN=...
JWT_SECRET=...
```
