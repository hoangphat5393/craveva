# Module: Webhooks (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Webhooks/Resources/views/**`

## Features migrated

| Feature                          | easyAjax Found | Migrated to Axios | Status |
| -------------------------------- | -------------- | ----------------- | ------ |
| Webhooks list quick actions      | Yes            | Yes               | Done   |
| Webhooks single-row actions      | Yes            | Yes               | Done   |
| Webhooks create/edit forms       | Yes            | Yes               | Done   |
| Webhooks variable GET helper     | Yes            | Yes               | Done   |
| Webhooks logs list/quick actions | Yes            | Yes               | Done   |

## Remaining scope

None — no `$.easyAjax` in `Modules/Webhooks/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Webhooks views from `$.easyAjax` to `window.apiHttp` (`get`, `postUrlEncoded`, `delete`) with standardized `catch` handling.
