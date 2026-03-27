# easyAjax → axios migration (standard process)

This folder tracks **per-module** migration from `$.easyAjax` (see `public/vendor/helper/helper.js`) to **`window.apiHttp`** (`resources/js/http/apiClient.js`), compiled into `public/js/main.js`.

## Shared client

| Item                                      | Location                                                                                                                                                                                          |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Axios instance + helpers                  | `resources/js/http/apiClient.js`                                                                                                                                                                  |
| Loaded on every authenticated layout page | `require('./http/apiClient')` in `resources/js/main.js`                                                                                                                                           |
| Global API                                | `window.apiHttp` — `get`, `post`, `put`, `patch`, `delete` (POST+`_method=DELETE`), `postForm`, `postUrlEncoded` (form-urlencoded / `.serialize()`), `instance`, `csrfToken`, `normalizeApiError` |

### Conventions

- **CSRF**: `meta[name="csrf-token"]` on each page (`layouts/app.blade.php`) + Laravel `XSRF-TOKEN` cookie (`withCredentials: true`).
- **JSON**: `Accept: application/json`. Laravel `Reply` payloads with `status: 'fail'` on HTTP 200 are **rejected** by the client (same behavior as `easyAjax` success handler calling `handleFail`).
- **Multipart / files**: use `apiHttp.postForm(url, formElementOrFormData)` — do not set `Content-Type` manually (browser sets boundary).
- **Form-urlencoded** (typical `$.easyAjax` with `$('#form').serialize()`): use `apiHttp.postUrlEncoded(url, $('#form').serialize())`.
- **DELETE from Blade**: `apiHttp.delete(url, csrfToken)` sends `FormData` with `_token` and `_method=DELETE`.
- **Errors**: `catch` receives a normalized `Error` with `status`, `errors` (validation map when present), `payload` (raw body when JSON).
- **UI blocking**: keep `$.easyBlockUI` / `$.easyUnblockUI` where already used until a shared non-jQuery overlay exists.

### Safe refactor rules

1. Change **only** the module (or view) in scope; do not rewrite `helper.js` globally in the same pass.
2. After edits: `pnpm run production` (or `npm run production`) so `public/js/main.js` updates.
3. Manually test CRUD paths touched (create/update/delete, validation, file upload).
4. Update this folder’s `{ModuleName}.md` status table and changelog.

### Analysis checklist (per module)

1. `rg '\$\.easyAjax\(' Modules/{ModuleName} --glob '*.blade.php'` (and `*.js` if any).
2. For each call: note `url` / `route()`, method, `file: true` or not.
3. Map routes in `Modules/{ModuleName}/Routes/web.php` (and `api.php`) → controller methods → Eloquent models / tables.

For **core app** areas (Product, Client, Order, etc.), scan `resources/views/{area}/` and related controllers under `app/Http/Controllers/`.

### Migration order (business priority — agreed)

Work these **before** optional nwidart modules (Sms, Letter, QRCode pilot, …):

| Priority | Area           | Code location (this repo)                                                                                                                                    | Tracker file                             |
| -------- | -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------- |
| 1        | Product        | `resources/views/products/**`                                                                                                                                | [product.md](./product.md)               |
| 2        | Client         | `resources/views/clients/**`                                                                                                                                 | [client.md](./client.md)                 |
| 3        | Inventory      | Stock/quantity flows: overlap with products + warehouse; scan `resources/views/products/**`, `Modules/Warehouse/**`, and any `*stock*` / `*inventory*` views | [inventory.md](./inventory.md)           |
| 4        | Warehouse      | `Modules/Warehouse/**` (nwidart module; may use Blade, Vue, or API-only)                                                                                     | [warehouse.md](./warehouse.md)           |
| 5        | Order          | `resources/views/orders/**`                                                                                                                                  | [order.md](./order.md)                   |
| 6        | Purchase order | `Modules/Purchase/Resources/views/purchase-order/**` (+ related routes)                                                                                      | [purchase-order.md](./purchase-order.md) |
| 7        | Delivery order | `Modules/Purchase/Resources/views/delivery-order/**`                                                                                                         | [delivery-order.md](./delivery-order.md) |
| 8        | Invoice        | `resources/views/invoices/**`                                                                                                                                | [invoice.md](./invoice.md)               |
| 9        | Payment        | `resources/views/payments/**`                                                                                                                                | [payment.md](./payment.md)               |

**Notes**

- **Product / Client / Order** live in the **main app**, not under `Modules/`. Track them with the `.md` files above, same checklist as modules.
- **Inventory** is often cross-cutting (catalog + stock + warehouse). Complete **Product** and align **Warehouse** before closing `inventory.md`.
- **Pilot modules** already migrated for process proof: see [QRCode.md](./QRCode.md).

Secondary / later waves: remaining `Modules/*`, super-admin-only views, integrations — lower priority than the table above unless blocking.

Latest secondary wave completed: **Purchase vendors** (`Modules/Purchase/Resources/views/vendors/**`) — see [vendor.md](./vendor.md).
