# Module: Subdomain (nwidart)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Subdomain/Resources/views/**`

## Features migrated

| Feature                                            | easyAjax Found | Migrated to Axios | Status |
| -------------------------------------------------- | -------------- | ----------------- | ------ |
| Forgot password page (`forgot-subdomain`)          | Yes            | Yes               | Done   |
| Forgot company page (`forgot-company`)             | Yes            | Yes               | Done   |
| Login by subdomain (`login-subdomain`)             | Yes            | Yes               | Done   |
| Workspace page (`workspace`)                       | Yes            | Yes               | Done   |
| SaaS forgot company (`saas/forgot-company`)        | Yes            | Yes               | Done   |
| SaaS workspace (`saas/workspace`)                  | Yes            | Yes               | Done   |
| Banned subdomain settings + delete (`super-admin`) | Yes            | Yes               | Done   |

## API mapping

| Pattern                      | Helper                                                |
| ---------------------------- | ----------------------------------------------------- |
| POST serialized form         | `apiHttp.postUrlEncoded(url, $('#form').serialize())` |
| DELETE banned subdomain item | `apiHttp.delete(url, csrfToken)`                      |
| Error handling               | `.catch((err) => $.handleApiFormError(err))`          |

## Remaining scope

None — no `$.easyAjax` in `Modules/Subdomain/Resources/views/**`.

## Changes log

- 2026-03-27 — Migrated all Subdomain Blade `$.easyAjax` calls to `window.apiHttp`.
