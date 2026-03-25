# Area: Client (core app)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`resources/views/clients/**` — index, show, ajax tabs (invoices, estimates, orders, projects, tickets, notes, documents, …), contacts, notes, GDPR, import, category modals.

## Features

| Feature                                                                        | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------------------------------------------ | -------------- | ----------------- | ------ |
| Client CRUD (create/edit modals, contacts create/edit)                         | Yes            | Yes               | Done   |
| Index: filters, quick actions, approve, delete w/ finance check, status        | Yes            | Yes               | Done   |
| Show: ajax tab load                                                            | Yes            | Yes               | Done   |
| Tab partials: DataTables actions (delete, quick action, send, reminders, etc.) | Yes            | Yes               | Done   |
| Notes: list + verified note modal (403/404/500 handling preserved)             | Yes            | Yes               | Done   |
| Import, GDPR consent, file edit modal                                          | Yes            | Yes               | Done   |
| Category / subcategory modals                                                  | Yes            | Yes               | Done   |

## Notes

- **`clients.finance_count`** delete flow: `apiHttp.get` with `{ params: { _token } }` (query string).
- **`getNoteDetail`** (notes ajax + notes index): `postUrlEncoded` + `catch` branches for HTTP 403 / 404 / 500 on modal content (same UX as legacy `error` callback).
- **Multipart** client/contact forms: `apiHttp.postForm` on `#save-client-data-form` / `#save-data-form` as in Product migration.

## Changes log

- **2026-03-25** — Replaced all `$.easyAjax` under `resources/views/clients/**/*.blade.php` with `window.apiHttp`. No remaining `easyAjax` in this tree.
