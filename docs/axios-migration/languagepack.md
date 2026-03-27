# Module: LanguagePack (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/LanguagePack/Resources/views/**`

## Features migrated

| Feature                   | easyAjax Found | Migrated to Axios | Status |
| ------------------------- | -------------- | ----------------- | ------ |
| Publish single language   | Yes            | Yes               | Done   |
| Publish all languages     | Yes            | Yes               | Done   |
| Sync language keys        | Yes            | Yes               | Done   |

## API mapping

| Pattern            | Helper                                                         |
| ------------------ | -------------------------------------------------------------- |
| POST JSON/form data | `apiHttp.postUrlEncoded(url, data)`                            |
| Error handling      | `.catch((err) => $.handleApiFormError(err))`                  |

## Remaining scope

None — no `$.easyAjax` in `Modules/LanguagePack/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated `script.blade.php` (publish, publish-all, sync-keys) from `$.easyAjax` to `window.apiHttp`.
