# Area: Order (core app)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

Primary views: `resources/views/orders/**` — index, `ajax/create|edit|show|admin_create`, Stripe modals, offline payment modal.

## Features

| Feature                                                                          | easyAjax Found | Migrated to Axios | Status |
| -------------------------------------------------------------------------------- | -------------- | ----------------- | ------ |
| Orders index — change status (with/without bank), delete row, quick action       | Yes            | Yes               | Done   |
| ajax/admin_create — category GET, client POST, add item GET, store               | Yes            | Yes               | Done   |
| ajax/create — client POST (parallel), add item GET, invoice store, discount POST | Yes            | Yes               | Done   |
| ajax/edit — category GET, client POST (parallel), add item GET, order update     | Yes            | Yes               | Done   |
| ajax/show — status POST, delete, Payfast/Square, Razorpay fail + confirm         | Yes            | Yes               | Done   |
| stripe/index — save address                                                      | Yes            | Yes               | Done   |
| stripe/stripe-payment — make invoice, complete, payment failed                   | Yes            | Yes               | Done   |
| offline/index — offline payment (multipart)                                      | Yes            | Yes               | Done   |

## API mapping

| Pattern                                                | Helper                                           |
| ------------------------------------------------------ | ------------------------------------------------ |
| GET JSON                                               | `apiHttp.get` / `get(url, { params })`           |
| POST `application/x-www-form-urlencoded`               | `apiHttp.postUrlEncoded`                         |
| POST JSON (nested: `paymentIntent`, `items`, Razorpay) | `apiHttp.post`                                   |
| DELETE                                                 | `apiHttp.delete`                                 |
| Multipart (offline receipt)                            | `apiHttp.postForm`                               |
| Laravel `Reply::redirect`                              | `response.action === 'redirect' && response.url` |
| `Reply::successWithData(..., ['redirectUrl' => ...])`  | `response.redirectUrl`                           |

## Notes

- **Warehouse**: no `$.easyAjax` in `Modules/Warehouse/**` — see [warehouse.md](./warehouse.md).
- Quick action on orders index still posts to `route('invoices.apply_quick_action')` (pre-existing route/table id wiring).
- `orders/ajax/create` submits to `invoices.store` (pre-existing).

## Changes log

- 2026-03-25 — Migrated all `$.easyAjax` in `resources/views/orders/**` to `window.apiHttp`; aligned redirect handling with `Reply::redirect` (`action` + `url`).
