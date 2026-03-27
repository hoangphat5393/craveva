# Module: Policy (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Policy/Resources/views/**`

## Features migrated

| Feature                                   | easyAjax Found | Migrated to Axios | Status |
| ----------------------------------------- | -------------- | ----------------- | ------ |
| Policy list/archive quick actions         | Yes            | Yes               | Done   |
| Policy show page actions (delete/publish) | Yes            | Yes               | Done   |
| Policy create/edit forms                  | Yes            | Yes               | Done   |
| Policy signature acknowledge flow         | Yes            | Yes               | Done   |
| Non-acknowledged reminder                 | Yes            | Yes               | Done   |
| AJAX tabs in policy show                  | Yes            | Yes               | Done   |

## Remaining scope

None — no `$.easyAjax` in `Modules/Policy/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Policy views from `$.easyAjax` to `window.apiHttp` (`get`, `postUrlEncoded`, `postForm`, `delete`) with standardized `catch` handling.
