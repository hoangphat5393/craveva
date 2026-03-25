# Area: Product (core app)

## Status

- [ ] Not Started
- [ ] In Progress
- [x] Completed

## Scope

Primary views: `resources/views/products/**` (index, `ajax/*`, category, sub-category).

## Features

| Feature                                                                              | easyAjax Found | Migrated to Axios | Status |
| ------------------------------------------------------------------------------------ | -------------- | ----------------- | ------ |
| Product list — quick action, delete row, add to cart, empty cart                     | Yes            | Yes               | Done   |
| Product create / edit (modal) — save, subcategories GET, options GET, file delete    | Yes            | Yes               | Done   |
| Import products                                                                      | Yes            | Yes               | Done   |
| Product show — delete                                                                | Yes            | Yes               | Done   |
| Category / sub-category modals — CRUD + inline edit                                  | Yes            | Yes               | Done   |
| Cart / empty cart — `orders.store` redirect, cart line sync, remove item, empty cart | Yes            | Yes               | Done   |

## API mapping (representative routes)

- `productCategory.*`, `productSubCategory.*`, `products.*`, `get_product_sub_categories`, `products.import.store`, `products.options`, `products.apply_quick_action`, `product-files.destroy`
- Cart/checkout from product views: `products.add_cart_item`, `products.remove_cart_item`, `orders.store` (redirect via `Reply::redirect`)

## Notes

- **`apiHttp.postUrlEncoded`** (`resources/js/http/apiClient.js`) used for `application/x-www-form-urlencoded` bodies (same as jQuery `.serialize()`).
- **`apiHttp.postForm`** used for multipart product create/update/import (Dropzone + file fields).
- **`orders.store`** success: check `response.action === 'redirect' && response.url` before `window.location` (same as `easyAjax` `redirect: true`).

## Changes log

- **2026-03-25** — Migrated all `$.easyAjax` in `resources/views/products/**/*.blade.php` to `window.apiHttp` (`get`, `postUrlEncoded`, `postForm`, `delete`). Added `postUrlEncoded` helper in `apiClient.js`. Run `pnpm run production` after pull.
