# Module: ServerManager (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/ServerManager/Resources/views/**`

## Features migrated

| Feature                                 | easyAjax Found | Migrated to Axios | Status |
| --------------------------------------- | -------------- | ----------------- | ------ |
| Domain create/edit/show/index actions   | Yes            | Yes               | Done   |
| Hosting create/edit/show/index actions  | Yes            | Yes               | Done   |
| Provider create/edit/show/index actions | Yes            | Yes               | Done   |
| Provider create-provider modal          | Yes            | Yes               | Done   |

## Remaining scope

None — no `$.easyAjax` in `Modules/ServerManager/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all ServerManager Blade `$.easyAjax` calls to `window.apiHttp`.
