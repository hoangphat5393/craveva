# Module: Pricing (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Pricing/Resources/views/**`

## Features migrated

| Feature                                 | easyAjax Found | Migrated to Axios | Status |
| --------------------------------------- | -------------- | ----------------- | ------ |
| Tiers create/edit/show/index flows      | Yes            | Yes               | Done   |
| Client pricing create/edit/index flows  | Yes            | Yes               | Done   |
| Company pricing create/edit/index flows | Yes            | Yes               | Done   |
| Volume rules create/edit/index flows    | Yes            | Yes               | Done   |
| Import pricing flow                     | Yes            | Yes               | Done   |
| Client tier inline edit                 | Yes            | Yes               | Done   |

## Notes

- `postForm` is used for `import/index.blade.php` because the import form uploads file input data.

## Remaining scope

None — no `$.easyAjax` in `Modules/Pricing/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Pricing module Blade `$.easyAjax` calls to `window.apiHttp`.
