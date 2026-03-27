# Area: Purchase module views

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed (all views under `Modules/Purchase/Resources/views/**`)

## Scope

`Modules/Purchase/Resources/views/**`

## Features

| Feature                                                                             | easyAjax Found | Migrated to Axios | Status |
| ----------------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| Vendor index (`vendors/index.blade.php`) quick action + row delete                  | Yes            | Yes               | Done   |
| Vendor show (`vendors/show.blade.php`) ajax tab load                                | Yes            | Yes               | Done   |
| Vendor create/edit (`vendors/ajax/create.blade.php`, `vendors/ajax/edit.blade.php`) | Yes            | Yes               | Done   |
| Contacts (`vendors/contacts/*.blade.php`, `vendors/ajax/contacts.blade.php`)        | Yes            | Yes               | Done   |
| Notes (`vendors/notes/*.blade.php`, `vendors/ajax/notes.blade.php`)                 | Yes            | Yes               | Done   |
| Overview (`vendors/ajax/overview.blade.php`)                                        | Yes            | Yes               | Done   |
| Purchase orders tab (`vendors/ajax/purchase-orders.blade.php`)                      | Yes            | Yes               | Done   |
| Bills tab (`vendors/ajax/bills.blade.php`)                                          | Yes            | Yes               | Done   |
| Payments tab (`vendors/ajax/payments.blade.php`)                                    | Yes            | Yes               | Done   |
| Category modal (`vendors/ajax/create_category.blade.php`)                           | Yes            | Yes               | Done   |
| Vendor credits (`vendor-credits/**`)                                                | Yes            | Yes               | Done   |
| Vendor payments (`vendor-payments/**`)                                              | Yes            | Yes               | Done   |
| Bills (`bills/**`)                                                                  | Yes            | Yes               | Done   |
| Purchase products (`purchase-products/**`)                                          | Yes            | Yes               | Done   |
| Purchase settings (`purchase-settings/**`)                                          | Yes            | Yes               | Done   |
| Purchase reports (`reports/index.blade.php`)                                        | Yes            | Yes               | Done   |

## API mapping

| Pattern                 | Helper                                                |
| ----------------------- | ----------------------------------------------------- |
| POST form (`serialize`) | `apiHttp.postUrlEncoded(url, $('#form').serialize())` |
| POST plain object       | `apiHttp.postUrlEncoded(url, { ... })`                |
| GET ajax tab/status     | `apiHttp.get(url, { params })`                        |
| DELETE row              | `apiHttp.delete(url, csrfToken)`                      |

## Notes

- Existing UX behavior is preserved: `$.easyBlockUI` / `$.easyUnblockUI`, SweetAlert confirm flows, and datatable redraws.
- Error handling is normalized through `$.handleApiFormError(err)`.

## Changes log

- 2026-03-27 — Migrated all `$.easyAjax` usages in `Modules/Purchase/Resources/views/vendors/**` to `window.apiHttp` and rebuilt assets.
- 2026-03-27 — Follow-up wave migrated `Modules/Purchase/Resources/views/vendor-credits/**` and `Modules/Purchase/Resources/views/vendor-payments/**` to `window.apiHttp` (kept same UX flow for blockUI, redirects, and datatable refresh).
- 2026-03-27 — Final wave migrated all remaining Purchase views (`bills/**`, `purchase-products/**`, `purchase-settings/**`, `reports/index.blade.php`, and modal reasons) so `Modules/Purchase/Resources/views/**` no longer contains `$.easyAjax`.
