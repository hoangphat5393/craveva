# Area: Payment (core app)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

Primary views: `resources/views/payments/**` — index, `ajax/create|edit`, `ajax/add-bulk-payments`.

## Features

| Feature                                                                                                 | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| index — delete row, quick action                                                                        | Yes            | Yes               | Done   |
| ajax/create — account list GET, store (multipart), project invoice list POST, currency, offline methods | Yes            | Yes               | Done   |
| ajax/edit — same patterns + update (multipart)                                                          | Yes            | Yes               | Done   |
| ajax/add-bulk-payments — refresh table GET, save bulk POST                                              | Yes            | Yes               | Done   |

## API mapping

| Pattern                                     | Helper                         |
| ------------------------------------------- | ------------------------------ |
| GET with query params                       | `apiHttp.get(url, { params })` |
| POST JSON (project invoice list)            | `apiHttp.post`                 |
| Multipart create/update payment             | `apiHttp.postForm`             |
| Bulk save (serialized form, no file inputs) | `apiHttp.postUrlEncoded`       |

## Changes log

- 2026-03-25 — Migrated all `$.easyAjax` in `resources/views/payments/**` to `window.apiHttp`.
