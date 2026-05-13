# AI order integration — REST API (`/api/integrations/orders`)

**Vietnamese setup (Postman / webhook vs REST):** [`AI_ORDER_INTEGRATION_REST_SETUP_VI.md`](./AI_ORDER_INTEGRATION_REST_SETUP_VI.md)

## Base path

All routes are under the Laravel `api` prefix:

- **Create:** `POST /api/integrations/orders`
- **Read:** `GET /api/integrations/orders/{orderId}`
- **Update:** `PATCH` or `PUT /api/integrations/orders/{orderId}`
- **Delete:** `DELETE /api/integrations/orders/{orderId}` (sets order `status` to `canceled` when not already `canceled` / `refunded`)

## Authentication

Send the **per-company** value of `companies.ai_order_webhook_secret` using **one** of:

- Header `X-AI-Webhook-Secret: <secret>`
- Header `Authorization: Bearer <secret>`

**Global** `AI_ORDER_WEBHOOK_SECRET` from `.env` is **not** accepted on these routes (returns `401` with code `INTEGRATION_REST_REQUIRES_COMPANY_SECRET`).

## Method toggles (company settings)

When the company has a per-company webhook secret, **Sale order settings** shows **collapsible panels** per HTTP verb (tap the header to expand). **POST (create)** is expanded by default. Each panel includes **full URL**, **Postman template**, and **Example cURL** with copy buttons.

Each HTTP verb is allowed only if the corresponding flag is enabled on the **company** row (Sale order settings → checkboxes):

| Flag                                | HTTP           |
| ----------------------------------- | -------------- |
| `ai_order_integration_allow_create` | `POST`         |
| `ai_order_integration_allow_read`   | `GET`          |
| `ai_order_integration_allow_update` | `PUT`, `PATCH` |
| `ai_order_integration_allow_delete` | `DELETE`       |

If disabled: **`403`** JSON:

```json
{
    "status": "error",
    "code": "INTEGRATION_METHOD_DISABLED",
    "message": "This HTTP method is disabled for AI order integration in company settings."
}
```

Defaults after migration: **Create = on**, Read / Update / Delete = **off** (safe).

## Create (`POST`) body

Same rules as legacy webhook `POST /ai-order-webhook/{hash}`: use `StoreAiOrderWebhookRequest` validation — include `company_id` (must match the company for the secret), `client_code` or `client_id`, `items[]`, optional `external_event_id`, optional `check_stock: false`, etc.

## Update (`PATCH` / `PUT`)

JSON body (optional fields):

- `status` — one of the allowed order statuses.
- `note` — appended to the existing note; an audit suffix is appended automatically.

## Read (`GET`)

Returns a JSON summary: `order_id`, `order_number`, `company_id`, `status`, `total`, `note`.

## Route registration (Froiden RestAPI)

`routes/api.php` registers **AI order REST** with **`Illuminate\Support\Facades\Route::`** (not `ApiRoute::`) so URLs stay **`/api/integrations/...`** without Froiden’s default **`/api/v1/...`** prefix from `config/api.php` (`default_version`).

Those routes are declared **before** the `ApiRoute::group` block so they are registered first in the `api` route file. Named routes: `api.integrations.orders.store`, `api.integrations.orders.show`, `api.integrations.orders.update`, `api.integrations.orders.update.put`, `api.integrations.orders.destroy` (used by Sale order settings to build example URLs via `route()`).

## Troubleshooting `404` with `Requested resource not found`

**Step 0 — prove the hostname hits this app:** after deploying, `GET` (browser or Postman):

`https://<your-host>/api/integrations/__route_probe`

- **200** JSON with `"ok": true` → this Laravel app’s `routes/api.php` is mounted; if `POST /api/integrations/orders` still 404, compare URL/method character-for-character and clear `route:cache`. If **GET probe works in Postman but POST 404** and Postman shows **session / XSRF cookies** for the same host, Sanctum’s stateful stack may be applying **CSRF** to `api` POSTs — deploy the change that adds `api/integrations/*` to `VerifyCsrfToken` `$except`, or clear Postman cookies for the `.test` host.
- **404** on the probe too → the hostname is **not** serving this codebase (wrong document root / different project / proxy). Fix vhost or deploy target before debugging Postman further.

That JSON shape usually means the HTTP request **never reached** the Laravel route `POST /api/integrations/orders` (or reached a different stack that does not register this path).

1. **On the machine that serves the hostname** (e.g. Herd for `*.test`), run:
    - `php artisan route:list | findstr integrations` (Windows) or `php artisan route:list | grep integrations`
    - You should see `POST api/integrations/orders` → `AiIntegrationOrdersController@store`, and `php artisan route:list --name=api.integrations` should list the named routes above.
2. If the line is missing: deploy/pull this codebase, then `php artisan route:clear` (stale `route:cache` is a common cause).
3. **Postman body:** use **Body → raw → JSON**, not `x-www-form-urlencoded`. Headers must include `Content-Type: application/json` and `X-AI-Webhook-Secret` (company secret).
4. **Do not move these five routes into `ApiRoute::` only** unless you also change clients to **`/api/v1/integrations/orders`** (Froiden’s default version prefix). This project intentionally keeps **`/api/integrations/orders`** for ERP examples and Postman.

## Audit checklist (why “API không chạy”)

| Symptom                                                                      | Likely cause                                                                                                                                | What to do                                                                                                                                                                                 |
| ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `404` + `Requested resource not found` + nested `error.details.url`          | Request did not hit this app’s route table (wrong Herd path, old `php artisan serve`, stale deploy, or `route:cache` without these routes). | On the **host that answers the URL**, run `php artisan route:list` and confirm lines containing `api/integrations`. Restart `serve` / PHP after `git pull`. Run `php artisan route:clear`. |
| `404` on probe `GET /api/integrations/__route_probe`                         | Wrong vhost / codebase / proxy.                                                                                                             | Fix document root and deploy.                                                                                                                                                              |
| `GET` probe **200** but `POST …/orders` **404** in Postman (cookies present) | Stateful Sanctum + CSRF on `api` for same-domain requests before CSRF exclude.                                                              | Deploy `VerifyCsrfToken` `$except` including `api/integrations/*`, or clear cookies for that host in Postman.                                                                              |
| `405` “GET method is not supported … Supported methods: POST”                | Opening `…/api/integrations/orders` in the **browser address bar** (always GET).                                                            | Use **POST** from Postman/curl; use **GET** only for `…/orders/{id}`.                                                                                                                      |
| `401` + `INTEGRATION_REST_REQUIRES_COMPANY_SECRET`                           | Header uses only **global** `.env` secret.                                                                                                  | Generate **per-company** secret on Sale order settings; send that value in `X-AI-Webhook-Secret`.                                                                                          |
| `403` + `INTEGRATION_METHOD_DISABLED`                                        | Verb unchecked in **Allowed HTTP methods**.                                                                                                 | Enable **Create (POST)** (and others as needed) and **Save**.                                                                                                                              |
| `200`-ish JSON + `duplicate: true`                                           | Same `external_event_id` already processed.                                                                                                 | Use a **new** `external_event_id` per real create.                                                                                                                                         |
| `422` on product / client                                                    | `item_name` not exact catalog name (or `sku` mismatch), or bad `client_code`.                                                               | Examples on the settings page use the **first** product by `id` and a real `client_code` from `client_details` for that company — copy from page after refresh or fix catalog line.        |
| Postman OK but browser `.test` 404                                           | Two different document roots or mixed `http://127.0.0.1:8000` vs `https://…test`.                                                           | Confirm Herd site path = this repo `public`; one URL end-to-end.                                                                                                                           |

**Automated proof the stack creates orders:** from repo root run  
`php artisan test --compact tests/Feature/AiIntegrationOrdersRestApiTest.php`.

## Legacy webhook

`POST /ai-order-webhook/{hash}` remains supported. It respects **`ai_order_integration_allow_create`** the same way as REST create.

## ERP settings UI

**Settings → Sale order settings → API:** short intro, copy fields (Base URL, company id, webhook URL, header), optional **REST** collapsible panels with copyable examples, **Allowed HTTP methods** checkboxes, legacy **example curl**, regenerate secret. Technical reference text is kept in repository docs only, not on the customer-facing page.
