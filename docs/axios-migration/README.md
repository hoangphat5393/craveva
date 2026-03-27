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

### After full migration: shorter, DRY code (future phase)

Do **not** start this during active migration waves — wait until `resources/views/**` has no `$.easyAjax`, builds are green, and key flows are smoke-tested. Then you can reduce repetition without risking behavior drift.

| Direction                | Idea                                                                                                                                                                                                                         |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **One-line errors**      | Add a tiny global helper, e.g. `showApiToastError(err)`, that wraps the normalized `Error` from `apiHttp` + Swal toast (same options as today). Use it in `.catch` instead of pasting the same `Swal.fire` block everywhere. |
| **Block UI + request**   | Wrap `apiHttp` + `$.easyBlockUI` / `$.easyUnblockUI` in a helper, e.g. `withBlockUI(selector, () => apiHttp.postUrlEncoded(...))`, so `try/finally` always unblocks.                                                         |
| **Validation on modals** | Standardize on `$.handleApiFormError` (or extend it once) for 422 + `$.showErrors`, instead of duplicating `catch` branches per Blade file.                                                                                  |
| **Leave inline scripts** | Move repeated patterns into `resources/js/**` (imported from `main.js` or a small `http/ui.js` module), use `async/await` for readability — Blade only passes route URLs and selectors.                                      |
| **Interceptors**         | Use sparingly: only for truly global behavior (e.g. session expiry redirect). Keep per-page `catch` when UX differs (modal vs toast vs redirect).                                                                            |
| **Overlay**              | Later, replace jQuery block UI with a single overlay component or CSS-only loading state if you reduce jQuery dependency.                                                                                                    |
| **Guardrails**           | Add an ESLint rule or a VS Code snippet for the approved `apiHttp` + block + catch pattern so new code stays short _and_ consistent.                                                                                         |

**Avoid:** rebuilding a second “god” helper that hides all behavior like legacy `easyAjax` — keep wrappers **thin** and composable.

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

Latest core waves completed:

- **Tasks** (`resources/views/tasks/**`) — migrated to `window.apiHttp`
- **Projects** (`resources/views/projects/**`) — migrated to `window.apiHttp`
- **Project templates** (`resources/views/project-templates/**`) — migrated to `window.apiHttp`
- **Leads** (`resources/views/leads/**`) — see [leads.md](./leads.md)
- **Tickets** (`resources/views/tickets/**`) — see [tickets.md](./tickets.md)
- **Event calendar** (`resources/views/event-calendar/**`) — see [event-calendar.md](./event-calendar.md)
- **Super-admin** (`resources/views/super-admin/**`) — see [super-admin.md](./super-admin.md)

Latest secondary waves completed:

- **Purchase vendors** (`Modules/Purchase/Resources/views/vendors/**`) — see [vendor.md](./vendor.md)
- **Sms** (`Modules/Sms/Resources/views/**`) — see [sms.md](./sms.md)
- **EInvoice** (`Modules/EInvoice/Resources/views/**`) — see [einvoice.md](./einvoice.md)
- **LanguagePack** (`Modules/LanguagePack/Resources/views/**`) — see [languagepack.md](./languagepack.md)
- **Subdomain** (`Modules/Subdomain/Resources/views/**`) — see [subdomain.md](./subdomain.md)
- **Policy** (`Modules/Policy/Resources/views/**`) — see [policy.md](./policy.md)
- **Webhooks** (`Modules/Webhooks/Resources/views/**`) — see [webhooks.md](./webhooks.md)
- **Asset** (`Modules/Asset/Resources/views/**`) — see [asset.md](./asset.md)
- **Biometric** (`Modules/Biometric/Resources/views/**`) — see [biometric.md](./biometric.md)
- **Affiliate** (`Modules/Affiliate/Resources/views/**`) — see [affiliate.md](./affiliate.md)
- **Pricing** (`Modules/Pricing/Resources/views/**`) — see [pricing.md](./pricing.md)
- **CyberSecurity** (`Modules/CyberSecurity/Resources/views/**`) — see [cybersecurity.md](./cybersecurity.md)
- **ServerManager** (`Modules/ServerManager/Resources/views/**`) — see [servermanager.md](./servermanager.md)
- **Zoom** (`Modules/Zoom/Resources/views/**`) — see [zoom.md](./zoom.md)
- **Onboarding** (`Modules/Onboarding/Resources/views/**`) — see [onboarding.md](./onboarding.md)
- **Letter** (`Modules/Letter/Resources/views/**`) — see [letter.md](./letter.md)
- **ProjectRoadmap** (`Modules/ProjectRoadmap/Resources/views/**`) — see [projectroadmap.md](./projectroadmap.md)
- **Biolinks** (`Modules/Biolinks/Resources/views/**`) — see [biolinks.md](./biolinks.md)
- **Performance** (`Modules/Performance/Resources/views/**`) — see [performance.md](./performance.md)
  Latest completed waves:

- **Finance core** (`resources/views/invoices/**`, `estimates/**`, `proposals/**`, `credit-notes/**`, `expenses/**`) — completed, see [finance-core.md](./finance-core.md)
- **HR / attendance / leave** (`resources/views/employees/**`, `attendances/**`, `timelogs/**`, `leaves/**`, `weekly-timesheets/**`) — completed, see [hr-attendance-leave.md](./hr-attendance-leave.md)
- **Payroll** (`Modules/Payroll/Resources/views/**`) — completed, see [payroll.md](./payroll.md)
- **Recruit** (`Modules/Recruit/Resources/views/**`) — completed, see [recruit.md](./recruit.md)
