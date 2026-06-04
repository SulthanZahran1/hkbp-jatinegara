# Route registration for the setup-password-link endpoint

`0001-admin-setup-password-link.patch` adds the handler
`passwordreset.HandleAdminCreateSetupPasswordLink` and its test, validated by
spike `spikes/001-autentico-idp/`. Upstream Auténtico has no such route yet, so
the HKBP image must also register it on the admin router.

Auténtico registers admin routes in its admin mux (the exact file/symbol can
drift between upstream revisions — confirm against the pinned `AUTENTICO_REF`).
Add the route next to the existing `POST /admin/api/users` registration:

```go
// admin API mux, alongside the other /admin/api/users routes
mux.HandleFunc("POST /admin/api/users/{id}/setup-password-link", passwordreset.HandleAdminCreateSetupPasswordLink)
```

If the admin mux uses a router that does not support method+path patterns,
register it with that router's idiom (e.g. chi/gorilla) but keep the path and
method identical:

    POST /admin/api/users/{id}/setup-password-link

`idp/Dockerfile` applies `0001-...patch` and then applies this one-line route
edit via `idp/scripts/register-route.sh`, which locates the admin-users route
block and inserts the registration. If the upstream layout changed and the
script cannot find the anchor, the build fails loudly — update the script or
this note for the new `AUTENTICO_REF` rather than shipping an image without the
route.

## Why this is split out

The handler is the validated, reusable core (it reuses `password_reset_tokens`
and the existing `/oauth2/reset-password` flow — no new setup-code subsystem).
Route wiring is the only integration point that depends on upstream's current
router shape, so it is kept separate and explicit instead of baked into a fragile
unified diff.
