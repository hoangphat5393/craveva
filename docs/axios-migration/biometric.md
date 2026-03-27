# Module: Biometric (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Biometric/Resources/views/**`

## Features migrated

| Feature                         | easyAjax Found | Migrated to Axios | Status |
| ------------------------------- | -------------- | ----------------- | ------ |
| Devices index actions           | Yes            | Yes               | Done   |
| Device create modal             | Yes            | Yes               | Done   |
| Employee biometric edit actions | Yes            | Yes               | Done   |

## Remaining scope

None — no `$.easyAjax` in `Modules/Biometric/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Biometric module Blade `$.easyAjax` calls to `window.apiHttp`.
