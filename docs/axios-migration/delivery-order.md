# Area: Delivery order (Purchase module)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

`Modules/Purchase/Resources/views/delivery-order/**` (index, show, `ajax/create`, `ajax/edit`).

## Features

| Feature                                               | easyAjax Found | Migrated to Axios | Status |
| ----------------------------------------------------- | -------------- | ----------------- | ------ |
| index — change status POST, row delete                | Yes            | Yes               | Done   |
| show — ajax tabs                                      | Yes            | Yes               | Done   |
| ajax/create — store POST, get PO items GET            | Yes            | Yes               | Done   |
| ajax/edit — update PUT (serialized), get PO items GET | Yes            | Yes               | Done   |

## API mapping

| Pattern                             | Helper                                                    |
| ----------------------------------- | --------------------------------------------------------- |
| GET (`purchase_order_id`, `_token`) | `apiHttp.get` + `params`                                  |
| POST create/update                  | `apiHttp.postUrlEncoded` (update includes `&_method=PUT`) |
| DELETE                              | `apiHttp.delete`                                          |
| Redirect                            | `redirectUrl` or `action` + `url`                         |

## Changes log

- 2026-03-25 — Migrated all `$.easyAjax` in `delivery-order` views to `window.apiHttp`; block UI + Swal on error.
