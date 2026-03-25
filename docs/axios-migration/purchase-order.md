# Area: Purchase order (Purchase module)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Purchase/Resources/views/purchase-order/**` (index, show, `ajax/create`, `ajax/edit`, `ajax/overview`, `ajax/files`).

## Features

| Feature                                                                                                     | easyAjax Found | Migrated to Axios | Status |
| ----------------------------------------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| index — quick action, delete, send order, bank status, delivery status GET                                  | Yes            | Yes               | Done   |
| show — ajax tabs                                                                                            | Yes            | Yes               | Done   |
| ajax/create — category GET, add item GET, store, currency/bank GET, vendor currency GET                     | Yes            | Yes               | Done   |
| ajax/edit — category GET, delete line image GET, add item GET, update, currency/vendor GET                  | Yes            | Yes               | Done   |
| ajax/overview — send, reminder GET, cancel invoice GET, delete PO, toggle shipping GET, delete invoice file | Yes            | Yes               | Done   |
| ajax/files — delete file                                                                                    | Yes            | Yes               | Done   |

## API mapping

| Pattern                   | Helper                                           |
| ------------------------- | ------------------------------------------------ |
| GET JSON / HTML fragments | `apiHttp.get` + `params`                         |
| POST form (`serialize`)   | `apiHttp.postUrlEncoded`                         |
| DELETE                    | `apiHttp.delete`                                 |
| Redirect after save       | `redirectUrl` or `action === 'redirect'` + `url` |

## Notes

- Index quick action still uses `route('bankaccounts.apply_quick_action')` (pre-existing).
- Overview reuses invoice routes (`payment_reminder`, `update_status`, etc.) for linked invoice UI — pre-existing.

## Changes log

- 2026-03-25 — Migrated all `$.easyAjax` in `purchase-order` views to `window.apiHttp`; block UI + Swal on error; PO create/update redirect resolution aligned with `Reply::redirect` / `redirectUrl`.
