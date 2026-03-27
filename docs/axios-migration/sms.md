# Module: Sms (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Sms/Resources/views/**`

## Features migrated

| Feature                        | easyAjax Found | Migrated to Axios | Status |
| ------------------------------ | -------------- | ----------------- | ------ |
| SMS settings save form         | Yes            | Yes               | Done   |
| Send test message modal submit | Yes            | Yes               | Done   |

## API mapping

| Pattern            | Helper                                                         |
| ------------------ | -------------------------------------------------------------- |
| POST serialized    | `apiHttp.postUrlEncoded(url, $('#form').serialize())`          |
| Error handling     | `.catch((err) => $.handleApiFormError(err))`                  |

## Remaining scope

None — no `$.easyAjax` in `Modules/Sms/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated `sms/index.blade.php` and `sms/test-message.blade.php` from `$.easyAjax` to `window.apiHttp`.
