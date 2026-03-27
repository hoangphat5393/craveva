# Module: Asset (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Asset/Resources/views/**`

## Features migrated

| Feature                                      | easyAjax Found | Migrated to Axios | Status |
| -------------------------------------------- | -------------- | ----------------- | ------ |
| Asset index/list actions                     | Yes            | Yes               | Done   |
| Asset create/edit/lend/return/history modals | Yes            | Yes               | Done   |
| Asset show partial actions                   | Yes            | Yes               | Done   |
| Asset settings and asset type modals         | Yes            | Yes               | Done   |

## Notes

- `postForm` is used for asset create/edit where file upload (`file: true`) exists.
- Other form submits use `postUrlEncoded`.

## Remaining scope

None — no `$.easyAjax` in `Modules/Asset/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Asset module Blade `$.easyAjax` calls to `window.apiHttp` with standardized `catch` handling.
