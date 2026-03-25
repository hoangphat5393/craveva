# Area: Inventory (cross-cutting)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed (purchase inventory + product inventory adjustment UI)

## Scope

There is no single `resources/views/inventory/` tree in this project. **Inventory** was treated as:

- **Purchase inventory** — `Modules/Purchase/Resources/views/purchase-inventory/**` (list, show/tabs, create, import, files).
- **Product inventory adjustment (modal)** — `Modules/Purchase/Resources/views/purchase-products/ajax/update_inventory.blade.php`.
- Stock / cart behaviour on **products** — already covered under **product.md** (`resources/views/products/**`).
- **Warehouse** module — no `$.easyAjax` in `Modules/Warehouse/**` at time of scan; track under **warehouse.md** when that area is migrated.

## Features

| Feature                                                                | easyAjax Found | Migrated to Axios | Status |
| ---------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| Purchase inventory — index (quick action, status, row delete)          | Yes            | Yes               | Done   |
| Purchase inventory — show (ajax tabs, status, delete)                  | Yes            | Yes               | Done   |
| Purchase inventory — ajax/create (save, category GET, adjust line GET) | Yes            | Yes               | Done   |
| Purchase inventory — ajax/import                                       | Yes            | Yes               | Done   |
| Purchase inventory — ajax/files (layout GET, file delete)              | Yes            | Yes               | Done   |
| Purchase products — update inventory modal                             | Yes            | Yes               | Done   |

## API mapping

| Pattern                                                   | Helper                                                         |
| --------------------------------------------------------- | -------------------------------------------------------------- |
| GET JSON (tabs, layout, sub-categories, adjust line item) | `apiHttp.get(url, { params })`                                 |
| POST form fields (no file)                                | `apiHttp.postUrlEncoded(url, $('#form').serialize())`          |
| POST multipart (import Excel)                             | `apiHttp.postForm(url, formElement)`                           |
| Laravel destroy                                           | `apiHttp.delete(url, csrfToken)`                               |
| POST status change                                        | `apiHttp.postUrlEncoded` with `_token`, `_method=POST`, fields |

## Notes

- `pnpm run production` after JS-related edits so `public/js/main.js` includes `apiHttp`.
- Remaining `$.easyAjax` elsewhere in `Modules/Purchase/**` (orders, bills, vendors, etc.) belong to other migration tracks, not this inventory milestone.

## Changes log

- 2026-03-25 — Migrated `purchase-inventory` views (`index`, `show`, `ajax/create`, `ajax/import`, `ajax/files`) and `purchase-products/ajax/update_inventory.blade.php` to `window.apiHttp`; block UI + Swal on error.
