# Module: EInvoice (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/EInvoice/Resources/views/**`

## Features migrated

| Feature                      | easyAjax Found | Migrated to Axios | Status |
| ---------------------------- | -------------- | ----------------- | ------ |
| EInvoice settings save script | Yes           | Yes               | Done   |
| EInvoice client modal save    | Yes           | Yes               | Done   |

## API mapping

| Pattern            | Helper                                                         |
| ------------------ | -------------------------------------------------------------- |
| POST serialized    | `apiHttp.postUrlEncoded(url, $('#form').serialize())`          |
| Error handling     | `.catch((err) => $.handleApiFormError(err))`                  |

## Remaining scope

None — no `$.easyAjax` in `Modules/EInvoice/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated `settings/save-script.blade.php` and `client/modal.blade.php` from `$.easyAjax` to `window.apiHttp`.
