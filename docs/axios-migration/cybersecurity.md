# Module: CyberSecurity (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/CyberSecurity/Resources/views/**`

## Features migrated

| Feature                                       | easyAjax Found | Migrated to Axios | Status |
| --------------------------------------------- | -------------- | ----------------- | ------ |
| Security settings tab navigation              | Yes            | Yes               | Done   |
| Blacklist IP create/edit/ajax                 | Yes            | Yes               | Done   |
| Blacklist email create/edit/ajax              | Yes            | Yes               | Done   |
| Login expiry create/edit/ajax                 | Yes            | Yes               | Done   |
| Security/single-session setting partial saves | Yes            | Yes               | Done   |

## Notes

- Some edit forms intentionally send real `PUT` with urlencoded body to match previous `type: "PUT"` behavior.

## Remaining scope

None — no `$.easyAjax` in `Modules/CyberSecurity/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all CyberSecurity Blade `$.easyAjax` calls to `window.apiHttp`.
