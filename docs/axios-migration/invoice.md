# Area: Invoice (core app)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

Primary views: `resources/views/invoices/**` — index, `ajax/create|edit|show`, timelog create, applied credits, Stripe, offline payment, file upload, shipping address.

## Features

| Feature                                                                                                      | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------------------------------------------------------------------------ | -------------- | ----------------- | ------ |
| index — delete, quick action, approve offline, send, reminder, cancel, toggle shipping                       | Yes            | Yes               | Done   |
| offline/index — multipart offline payment, GET method description                                            | Yes            | Yes               | Done   |
| add_shipping_address, file_upload — POST                                                                     | Yes            | Yes               | Done   |
| stripe/index, stripe-payment — address POST, Stripe complete / failed                                        | Yes            | Yes               | Done   |
| ajax/create — category, client, add item, store (multipart), currency, offline methods                       | Yes            | Yes               | Done   |
| ajax/edit — same patterns + dropify image delete GET, update (multipart)                                     | Yes            | Yes               | Done   |
| ajax/show — Payfast, Square, Razorpay, send, approve, reminder, cancel, delete, toggle shipping, delete file | Yes            | Yes               | Done   |
| ajax/create-timelog-invoice — fetch timelogs, store                                                          | Yes            | Yes               | Done   |
| ajax/applied_credits — delete applied credit                                                                 | Yes            | Yes               | Done   |

## API mapping

Same helpers as [order.md](./order.md): `get`, `post`, `postUrlEncoded`, `postForm`, `delete`; redirects via `redirectUrl` or `action` + `url`.

## Changes log

- 2026-03-25 — Migrated all `$.easyAjax` in `resources/views/invoices/**` to `window.apiHttp`; fixed modal title typo in `offline/index.blade.php` (`payOffline`).
