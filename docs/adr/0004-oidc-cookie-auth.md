# 0004 — Backend-mediated OIDC with opaque cookie sessions

## Status
Accepted (supersedes the app-local JWT/password auth in ADR-era docs).

## Context
The original design used app-local username/password auth with JWT access +
refresh tokens and SPA Bearer storage. HKBP needs:

- credentials and login sessions owned by a dedicated IdP, not the app;
- support for users without personal email (admin-managed username/password);
- no temporary passwords — users set their own via a one-time link;
- an approval workflow for unknown identities;
- simple operations for non-technical church admins.

Spike `spikes/001-autentico-idp/` validated Auténtico as the IdP (no-email
users, OIDC/JWKS/token/userinfo/logout, SQLite state, and a small setup-link
patch).

## Decision
- The Go backend is the OIDC relying party (authorization-code + PKCE). The SPA
  never talks to the IdP and holds no tokens.
- Browser sessions are an HTTP-only opaque `hkbp_session` cookie backed by
  `app_sessions.token_hash`. No JWT/claims in the cookie.
- Remove password login, refresh endpoint/tokens, and SPA Bearer auth from the
  active contract.
- OIDC identity (`issuer`, `subject`, `preferred_username`, optional email) lives
  in `user_identities`, separate from HKBP profile/authorization fields.
- Unknown/unlinked/disabled identities create `access_requests`
  (`new_user` / `link_existing_user` / `reactivate_user`); approval requires
  re-login. Rejected/disabled re-requests honor a 2h cooldown.
- HKBP provisions users (HKBP row → IdP account → identity link → setup link),
  using a random unusable bootstrap password it never stores or shows.
- Username rename is IdP-coordinated and session-invalidating; if the IdP has no
  rename API it is reported as blocked (no silent local rename / drift).
- First admin via `BOOTSTRAP_ADMIN_EMAIL` on first verified-email login; inert
  afterwards.

## Consequences
- New deps: `github.com/coreos/go-oidc/v3`, `golang.org/x/oauth2`. Dropped:
  `golang-jwt`, `bcrypt`.
- `users` recreated (migration 010): email optional, password/refresh removed,
  `provisioning_status` + `session_version` added; new `app_sessions`,
  `user_identities`, `access_requests`, `audit_logs` tables.
- A small HKBP-specific Auténtico fork/image is maintained for the
  setup-password-link endpoint (`idp/`); consider upstreaming later.
- SPA and API are served same-origin in prod (and proxied in dev) so the session
  cookie works without cross-site cookie complexity.
- Full browser OIDC requires a running IdP; the decision logic, session
  validation, cooldown, and rename handling are covered by Go unit/integration
  tests so the flow is verifiable without a live IdP.

See `docs/AUTH-DESIGN.md` and `CONTEXT.md` for the full design.
