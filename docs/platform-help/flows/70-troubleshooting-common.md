# Troubleshooting — access and UI

## 403 / missing menu

| Cause                         | Fix                                                     |
| ----------------------------- | ------------------------------------------------------- |
| Module off in Module Settings | Enable for role                                         |
| Package lacks module          | Upgrade package or allow module                         |
| Missing permission            | Role & Permissions                                      |
| Routes not registered         | `php artisan route:clear`; ensure module enabled in app |

## AJAX validation

- Red toast but field not highlighted: see [REFERENCE/UI-CONVENTIONS.md](REFERENCE/UI-CONVENTIONS.md) — need `handleApiFormError` and `.is-invalid`.
- Orphan server errors: toast only, no field mapping.

## Right modal

- Inline `<script>` in injected HTML may not run — use bundled `custom.js` or `@push('scripts')` after `custom.js`.
- After modal open, call module init (e.g. product UOM helper) if section present.

## Bootstrap-select in tables

- Menu clipped: `data-container="body"` or `selectpicker({ container: 'body' })`.

## Environment

- Hostnames ending in `*.test` are **local dev** (Herd/Valet), not remote production unless DNS says otherwise.

## AI order API

- Use `POST /api/integrations/orders` (REST). Configure integration credentials in company settings. Overview: [REFERENCE/ERP-SYSTEM-OVERVIEW.md](../REFERENCE/ERP-SYSTEM-OVERVIEW.md).
