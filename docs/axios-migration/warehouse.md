# Module: Warehouse (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed (N/A — no `$.easyAjax` in module views)

## Scope

`Modules/Warehouse/**` — routes, controllers, Blade assets as used by the module.

## Features

| Feature                                                              | easyAjax Found | Migrated to Axios | Status                                                           |
| -------------------------------------------------------------------- | -------------- | ----------------- | ---------------------------------------------------------------- |
| All Blade views (`index`, `create`, `edit`, `stock/*`, `transfer/*`) | No             | N/A               | Done — uses full form POST / SweetAlert v1 submit, no `easyAjax` |

## API mapping

- Traditional HTML forms (`route('warehouse.*')`, `warehouse.stock.*`, `warehouse.transfer.*`) and `document.getElementById('delete-warehouse-' + id).submit()` for delete.

## Notes

- `rg 'easyAjax' Modules/Warehouse` returns no matches; nothing to migrate for axios track.
- If JS is added later (Vue/API), use `window.apiHttp` from `public/js/main.js`.

## Changes log

- 2026-03-25 — Confirmed no `$.easyAjax` in `Modules/Warehouse`; tracker closed as N/A.
