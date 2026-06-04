# Codex `/goal` One-Shot Prompt — Auténtico OIDC Auth Migration

Use this file as the exact prompt when starting Codex Goal mode from the repository root to implement the HKBP authentication redesign validated by the Auténtico spike.

## How to start

From repo root:

```bash
cd ~/hosted_projects/hkbp-jatinegara
codex --search --sandbox workspace-write
```

Then paste the prompt below into the interactive Codex composer.

## Goal prompt

```text
/goal Implement the HKBP Jatinegara authentication redesign end-to-end, using the decisions in CONTEXT.md and the validated Auténtico spike in spikes/001-autentico-idp/. This is a one-shot implementation goal: keep working until the code, tests, docs, and verification commands are complete, or until a hard blocker remains with exact evidence.

First read, in order: AGENTS.md, CONTEXT.md, spikes/001-autentico-idp/README.md, spikes/001-autentico-idp/autentico-setup-link-spike.patch, docs/build-guide.md, docs/database-schema.md, docs/api-spec.md, docs/implementation-notes.md, README.md, and docs/adr/*.md. IMPORTANT: AGENTS.md and older docs still mention JWT/password auth; treat CONTEXT.md and this goal as the current source of truth for auth. Update stale docs as part of the goal.

High-level target:
- Replace HKBP app-local JWT/password/refresh auth with backend-mediated OIDC against a dedicated lightweight IdP at https://auth.hkbp.zahranm.cloud.
- Primary IdP is Auténtico, validated by spike 001. Build/pin an HKBP-specific Auténtico fork/image patch only if needed for admin-generated setup-password-link.
- HKBP app remains at https://hkbp.zahranm.cloud.
- OIDC callback is https://hkbp.zahranm.cloud/api/v1/auth/callback.
- Post-logout redirect is https://hkbp.zahranm.cloud/login?logged_out=1.
- Pending approval page is https://hkbp.zahranm.cloud/access-pending.

Non-negotiable auth decisions:
- HKBP uses an HTTP-only opaque hkbp_session cookie backed by app_sessions.token_hash. The cookie must not contain JWT claims or user data.
- Protected requests require active unexpired app session, users.status = active, users.provisioning_status = active, app_sessions.session_version == users.session_version, and a user_identities link.
- Remove old SPA Bearer/JWT auth from the active browser contract: no username/password login endpoint for the SPA, no refresh endpoint, no access/refresh token storage in frontend, no axios Bearer-token refresh flow.
- Backend active auth API should be: GET /api/v1/auth/login, GET /api/v1/auth/callback, GET /api/v1/auth/me, POST /api/v1/auth/logout.
- Non-browser automation stays server-side commands/import tools only; do not add personal access tokens or HTTP API bearer tokens for MVP.
- IdP username / preferred_username and HKBP users.username must stay identical. Admin username rename is coordinated across HKBP + IdP, audited, and invalidates sessions; partial failures must surface drift/reconciliation items.
- issuer + subject is the automatic auth identity key. preferred_username is the human/account handle. Email is optional metadata only and may be absent.
- OIDC identity data lives in user_identities, separate from HKBP app profile/authorization fields.
- Unknown IdP identities create explicit access_requests. Approved access requests require re-login; pending sessions must not auto-upgrade.
- Access request request_type values: new_user, link_existing_user, reactivate_user.
- Rejected access requests are retained and can be re-requested after a 2-hour cooldown.
- Access request discovery is passive-only via admin dashboard badge/list; no email/push/realtime notification for MVP.
- Disabled linked users can re-request via reactivate_user after the same 2-hour cooldown.
- Verified email changes, if email claims exist, auto-update user_identities email fields and write audit log; email never becomes primary identifier.
- Generic audit_logs table/helper should support future all-mutation audit, but MVP wiring only needs auth/access events.
- Password policy: minimum 8 characters, no mandatory uppercase/symbol rules, no forced periodic rotation, rate limiting/account lockout on repeated failures, admin recovery via setup/reset link.
- IdP state backup is nightly persistent-volume/SQLite backup with 7-30 day retention; restore drill not required for MVP, but document backup path and restore command.

Auténtico work:
- Create or vendor a minimal HKBP Auténtico fork/image path in the repository docs/scripts as appropriate. Do not make a large unrelated fork.
- Apply the minimum setup-link concept from spikes/001-autentico-idp/autentico-setup-link-spike.patch: admin-auth endpoint POST /admin/api/users/{id}/setup-password-link returns a one-time setup/reset URL using existing password_reset_tokens and /oauth2/reset-password flow, instead of emailing it or showing a temporary password.
- The HKBP provisioning flow may create the IdP account with a random high-entropy unusable bootstrap password only as a technical necessity, but HKBP must never store or display that password. The user receives only the setup/reset URL or QR and chooses their own password.
- Confirm Auténtico supports users without personal email by configuring email hidden/optional. Do not introduce fake/synthetic email as a requirement.
- Configure Auténtico SQLite state on a persistent volume. Use AUTENTICO_DB_FILE_PATH or documented equivalent.
- Add Docker/Dokploy-compatible deployment artifact(s) for auth.hkbp.zahranm.cloud if the repo structure supports deployment docs/config. Keep secrets in env examples only.

HKBP backend work:
- Add/modify DB migrations for app_sessions, user_identities, access_requests, audit_logs, users.provisioning_status, users.session_version, and any necessary auth/provisioning fields. Preserve existing domain tables unless migration requires auth FK changes.
- Implement OIDC config via env variables: issuer URL, client ID, client secret if confidential, redirect URL, post-logout redirect, cookie settings, session TTL, IdP admin API URL/token/credentials, and setup-link behavior. Use .env.example only; do not commit secrets.
- Implement backend-mediated OIDC login: generate state/nonce/PKCE as needed, store transient state securely server-side or signed/HTTP-only cookie, redirect to IdP authorization URL.
- Implement callback validation: state, nonce, issuer, audience, signature/JWKS, expiry, code exchange, and claim extraction. Prefer standard Go OIDC libraries where appropriate.
- Callback logic:
  1. If issuer+subject matches active provisioned user_identity and user is active, create app session and redirect to app.
  2. If no subject match but preferred_username matches an existing HKBP user without/with mismatched identity, create link_existing_user access request, not silent auto-link.
  3. If verified email exists and matches, store as optional supporting evidence only.
  4. If no match, create new_user access request.
  5. If linked user is disabled, create/reopen reactivate_user request after cooldown.
- Implement GET /api/v1/auth/me using cookie session only.
- Implement POST /api/v1/auth/logout: revoke local session, clear cookie, write audit, redirect or return IdP end-session URL/flow so shared computers cannot silently re-enter via SSO.
- Implement HKBP admin provisioning flow: create users row first with provisioning_status = pending_idp, call IdP management API to create matching username account, receive stable subject, create user_identities, mark provisioning_status = active, generate setup/reset link, return/show it once. On IdP failure, mark failed_idp and support retry.
- Implement admin username rename as coordinated IdP rename + users.username update + audit + session invalidation. If IdP rename API is missing, implement the backend surface as blocked/unsupported with clear error and docs, not silent local-only rename.
- Implement access request admin list/badge, approve/reject/reopen flows for new_user, link_existing_user, reactivate_user. Approval must be transactional. Approval does not create an active browser session; user must sign in again.
- Implement generic audit helper/table and wire auth/access events: access_request_created, access_request_approved, access_request_rejected, access_request_reopened, identity_linked, identity_email_changed, user_reactivated, username_changed, session_revoked, logout.
- Implement rate limiting/account lockout if this belongs in HKBP; otherwise document Auténtico as owner and configure/verify it there.

HKBP frontend work:
- Replace login page with a single CTA: “Sign in with HKBP Account”. It calls GET /api/v1/auth/login and lets backend redirect to IdP.
- Remove access/refresh-token storage and Bearer auth interceptor. Frontend API client should use cookie credentials.
- Implement router guard using GET /api/v1/auth/me.
- Implement /access-pending and disabled/denied messaging as needed.
- Implement admin UI for provisioning users, showing setup link/QR once, retrying failed_idp, viewing access requests badge/list, approving/rejecting/reopening requests, reactivation requests, and coordinated username rename if in current admin scope.
- Keep UI mobile-friendly and simple for church admins.

Docs to update:
- AGENTS.md must no longer instruct future agents to build JWT auth as the target.
- CONTEXT.md should remain the canonical glossary/decision summary; update only for implementation facts or discovered deviations.
- docs/api-spec.md must describe the new cookie/OIDC auth API and remove JWT/refresh active contract.
- docs/database-schema.md must include new auth/session/identity/access/audit/provisioning schema.
- docs/build-guide.md and README.md must describe Auténtico/IdP setup, env vars, local dev, backup path, and auth verification.
- Add a concise AUTH-DESIGN.md or equivalent if useful, but do not duplicate huge stale blocks.

Testing and verification requirements:
- Add unit tests for session cookie auth, callback matching decisions, access request cooldown, provisioning status transitions, audit logging, and username rename/drift handling where feasible.
- Add integration tests or handler tests for auth endpoints and admin provisioning surfaces where feasible.
- Run backend formatting/lint equivalent, go mod tidy, and go test ./... in backend.
- Run frontend install/build and typecheck/test if configured.
- If Auténtico fork code is included locally, run the focused Auténtico tests from the spike plus the new setup-link tests.
- Start the backend and verify /health.
- Exercise at least one auth-adjacent flow with real commands or tests. If full browser OIDC cannot be completed locally, state exactly which part was verified by automated tests and which env/client setup remains.
- Do not claim success without real command output. If a tool/network/dependency blocks verification, try one reasonable alternative and then document the blocker precisely.

Implementation discipline:
- Read existing code before editing. Do not overwrite unrelated features.
- Keep secrets out of git. Use env examples and docs placeholders.
- Prefer small, coherent commits if possible; for one-shot /goal, a single final commit is acceptable only after verification.
- Do not leave long-running servers/processes running.
- Do not stop at stubs. The deliverable is a runnable, tested auth migration or a precise blocker report.

Final report must include:
- Summary of files changed and features built.
- Exact verification commands run and their results.
- Any remaining manual deployment/env setup.
- Whether Auténtico patch was implemented locally, upstreamed, or only documented.
- Any deviations from CONTEXT.md and why.
```

## Recommended stopping condition

The goal should stop only when:

1. stale JWT instructions/docs are updated,
2. database schema/docs reflect `app_sessions`, `user_identities`, `access_requests`, `audit_logs`, and provisioning status,
3. backend OIDC/cookie session code is implemented and tested,
4. frontend login/token handling is migrated to cookie/OIDC flow,
5. Auténtico setup-link patch path is implemented or precisely blocked,
6. backend tests pass,
7. frontend build passes,
8. local verification evidence is captured in the final report.

## Notes from spike 001

- Auténtico supports no-email users when `ProfileFieldEmail = hidden`.
- Admin create user exists at `POST /admin/api/users` and returns a stable user ID.
- OIDC discovery/JWKS/token/userinfo/logout focused tests passed.
- E2E tests require admin/account UI dist assets to be built first.
- Setup-link endpoint is not upstream yet, but a small throwaway patch passed tests by reusing password reset tokens.
