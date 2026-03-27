# Module: Affiliate (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Affiliate/Resources/views/**`

## Features migrated

| Feature                                        | easyAjax Found | Migrated to Axios | Status |
| ---------------------------------------------- | -------------- | ----------------- | ------ |
| Affiliate dashboard tabs and partial actions   | Yes            | Yes               | Done   |
| Affiliate create/edit slug and profile actions | Yes            | Yes               | Done   |
| Payout list and payout modals                  | Yes            | Yes               | Done   |
| Referral list and create modal                 | Yes            | Yes               | Done   |
| Affiliate settings save                        | Yes            | Yes               | Done   |

## Remaining scope

None — no `$.easyAjax` in `Modules/Affiliate/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Affiliate module Blade `$.easyAjax` calls to `window.apiHttp`.
