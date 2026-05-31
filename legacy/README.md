# Legacy Reference

This directory contains a trimmed reference copy of the legacy PHP applications used to infer the new Vue + Go domain model.

## Included

- `absen/` — Attendance/presence frontend and chunk upload handler reference.
- `aplikasidbruas/` — Laravel thin-client source, especially controllers/routes/views.

## Intentionally excluded

To keep Codex context focused and avoid unsafe legacy artifacts, the following were removed from the current tree:

- Large zipped backup: `legacy/aplikasidbruas/testing lara jtn.zip`
- Vendor/admin template bulk: `legacy/aplikasidbruas/public/adminlte/`
- Suspicious legacy file manager: `legacy/aplikasidbruas/app/Console/bo.php`
- Original-host obfuscated `cek.php` was never added to this repo.

Use `resources/views/content/*.blade.php`, `routes/web.php`, and the controllers as the migration reference.
