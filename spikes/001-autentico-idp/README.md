# 001: Auténtico as lightweight HKBP IdP

## Question
Can Auténtico serve as HKBP's lightweight OIDC identity provider with username/password accounts, no personal email requirement, admin provisioning from HKBP, logout, SQLite-backed state, and a small no-temp-password setup-link patch?

## Acceptance criteria
- Username/password users can be created without email when profile email is hidden/optional.
- Admin API exists for creating users and returns a stable IdP subject/user ID.
- OIDC discovery/JWKS/token/userinfo surfaces exist and tests pass.
- RP-initiated logout/end-session flow exists and tests pass.
- Password reset token machinery can be reused for admin-generated setup links without implementing a separate setup-code system.
- SQLite state is used and can be volume-backed.
- Patch surface for HKBP-specific setup link is small enough to maintain as an MVP fork/image.

## Evidence log

### 2026-06-04 evidence

Source inspected/cloned at:

```txt
/tmp/autentico-spike
```

Focused tests run:

```txt
go test ./pkg/user -run 'TestHKBPSpike|TestCreateUser|TestCreatePasskeyUser' -count=1 -v
# PASS: username/password users can be created without email when ProfileFieldEmail=hidden

make admin-ui-build-fast account-ui-build-fast
# PASS: UI dist assets built; required before e2e package compiles

go test ./pkg/wellknown ./pkg/key ./pkg/token ./pkg/userinfo ./pkg/session -run 'Test' -count=1 -v
# PASS: discovery/JWKS/token/userinfo/session/logout package tests passed

go test ./tests/e2e -run 'Test.*(AuthCode|RP|ClientCredentials|UserLifecycle|Signup)' -count=1 -v
# PASS: selected e2e flows passed after building UI dist assets

go test ./pkg/passwordreset ./pkg/user -run 'TestHKBPSpike|TestCreateUser' -count=1 -v
# PASS: throwaway setup-link patch test passed
```

Findings:

- Auténtico already has `ProfileFieldEmail = hidden`, and `UserCreateRequest.Email` is optional.
- Admin user creation endpoint exists at `POST /admin/api/users`; response includes stable `id`, usable as OIDC subject for HKBP `user_identities.subject`.
- OIDC discovery, JWKS, token generation, userinfo, authorization-code/PKCE tests, and RP-initiated logout tests exist and passed in focused test runs.
- SQLite is first-class via `AUTENTICO_DB_FILE_PATH` and `modernc.org/sqlite`; suitable for a Dokploy persistent volume.
- Current admin create-user path still requires a password. For HKBP, the smallest no-temp-password extension is an admin-auth setup-link endpoint that creates a password reset token and returns the existing `/oauth2/reset-password?token=...` URL.
- A throwaway implementation of `POST /admin/api/users/{id}/setup-password-link` was prototyped in `/tmp/autentico-spike/pkg/passwordreset/admin_setup_link.go`; test `TestHKBPSpike_CreateSetupPasswordLinkForNoEmailUser` passed.

## Verdict: VALIDATED

### What worked
- No-personal-email username/password accounts are feasible.
- Auténtico has the needed OIDC/logout/admin foundation.
- The no-temp-password setup-link requirement is a small provider-side patch, not a new auth subsystem.

### What didn't / constraints
- E2E tests fail to compile until account/admin UI dist assets are built because `pkg/account/embed.go` expects `dist` files.
- Setup-link endpoint does not appear to exist upstream yet; MVP needs a tiny fork/image or upstream contribution.

### Recommendation for real build
- Proceed with Auténtico fork spike-to-build path.
- Keep the fork patch limited to admin-generated setup/reset link and any needed API response compatibility.
- Pin upstream commit/version and document the patch diff.
