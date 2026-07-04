# easyAjax -> axios migration (standard process)

This folder tracks **per-module** migration from `$.easyAjax` (see `public/vendor/helper/helper.js`) to **`window.apiHttp`** (`resources/js/http/apiClient.js`), compiled into `public/js/main.js`.

For the remaining full migration plan and wave-by-wave progress tracker, see [FULL_MIGRATION_PLAN.md](./FULL_MIGRATION_PLAN.md).

## Current status snapshot

**Last static scan:** 2026-06-25 from repo root, limited to `resources/views`, `Modules`, and `resources/js` Blade/JS files.

The `$.easyAjax` migration is complete for app/module views and the installer environment view. Direct `$.ajax` remains a separate backlog.

| Pattern | Current result |
| ------- | -------------- |
| `$.easyAjax(` | 0 matches |
| `$.ajax(` / `jQuery.ajax(` | 32 matches in 21 files |
| `window.apiHttp` / `apiHttp.` | 1065 files |

Breakdown for `$.easyAjax(`:

| Area | Files |
| ---- | ----- |
| `resources/views/**` | 0 |
| `Modules/**` | 0 |

The installer environment view uses standalone browser `fetch` instead of `window.apiHttp` because installer pages do not load `public/js/main.js`.

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

### Migration waves

The per-module tracker files were retired on 2026-06-17 after the listed implementation waves were marked complete. Keep this README as the canonical process + status index; use `git log -- docs/axios-migration/<old-file>.md` only when historical implementation notes are needed.

Important: as of the 2026-06-27 scan, `$.easyAjax` scopes are clean. Use the commands in this README and `AJAX_AUDIT.md` before claiming direct `$.ajax` is migrated.

Core business waves:

| Area | Scope | Status |
| ---- | ----- | ------ |
| Product | `resources/views/products/**` | Completed |
| Client | `resources/views/clients/**` | Completed |
| Inventory | Product inventory + purchase inventory + Warehouse stock/adjust/transfer UI | Completed |
| Warehouse | `Modules/Warehouse/**` create/adjust/transfer AJAX flows | Completed |
| Order | `resources/views/orders/**` | Completed |
| Purchase order | `Modules/Purchase/Resources/views/purchase-order/**` | Completed |
| Delivery order | `Modules/Purchase/Resources/views/delivery-order/**` | Completed |
| Invoice | `resources/views/invoices/**` | Completed for `$.easyAjax`; still has direct `$.ajax` to review |
| Payment | `resources/views/payments/**` | Completed |
| Finance core | Invoices, estimates, proposals, credit notes, expenses | Completed for listed estimate/invoice/payment/proposal/credit-note/expense scopes; direct `$.ajax` still needs separate review |
| HR / attendance / leave | Employees, attendances, timelogs, leaves, weekly timesheets | Partial: related settings/timelog views still need review |

Other completed waves:

| Area | Scope | Status |
| ---- | ----- | ------ |
| Tasks / Projects / Project templates | `resources/views/tasks/**`, `projects/**`, `project-templates/**`, `recurring-task/**` | Completed for `$.easyAjax`; direct `$.ajax` still needs separate review |
| Leads / Tickets / Event calendar / Super-admin | Core app views | Completed for `$.easyAjax`; direct `$.ajax` still needs separate review |
| Purchase vendors and related purchase screens | `Modules/Purchase/Resources/views/**` vendor/bill/payment/product/report areas | Completed |
| QRCode pilot | `Modules/QRCode/Resources/views/**` | Completed |
| Sms / EInvoice / LanguagePack / Subdomain / Policy / Webhooks | Module views | Completed |
| Asset / Biometric / Affiliate / Pricing / CyberSecurity | Module views | Completed |
| ServerManager / Zoom / Onboarding / Letter / ProjectRoadmap / Biolinks / Performance | Module views | Completed |
| Payroll / Recruit | Module views | Completed |
| Global right modal | `.openRightModal` in `resources/js/custom.js` | Completed |

### Known remaining `$.easyAjax` hotspots

Remaining `$.easyAjax` from the 2026-06-27 scan:

| Area | Files with `$.easyAjax` |
| ---- | ----------------------- |
| None | 0 |

Estimate-related public/request/template views were migrated in the 2026-06-22 cleanup batch.
