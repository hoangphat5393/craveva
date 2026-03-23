# Pricing Module — Deep Analysis

**Project:** Craveva (Laravel modular app)  
**Module path:** `Modules/Pricing`  
**Analysis date:** 2026-03-23

This document traces routes, controllers, services, models, database usage, and reviews logic, bugs, performance, and security. **No code was modified** as part of this analysis.

---

## 1. Module Summary

### Purpose

The **Pricing** module implements **B2B-style price resolution** for products: per-client product overrides, corporate (company–customer) contracts, **pricing tiers** (with optional per-product tier lines), and **volume-based discounts**. A central **`PricingService`** applies a **two-stage** algorithm:

1. **Stage 1 — Unit price:** resolve from (highest priority first) **client product pricing** → **corporate contract** (`company_customer_pricing` + optional `company_customer_product_pricing`) → **client’s assigned tier** (`client_details.pricing_tier_id` + `pricing_tiers` / `pricing_tier_items`) → **product base price**.
2. **Stage 2 — Volume discount:** apply **`VolumeDiscountService`** rules from `volume_discount_rules` to the line (quantity × unit from stage 1).

The module also provides **CRUD UIs** (web, authenticated) for tiers, client-specific prices, company–customer pricing, client–tier assignment, volume rules, and **Excel imports** (queued jobs).

### Main features

| Area                         | Description                                                                                                                                                                                                                                 |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Pricing tiers**            | `PricingTierController` — tier CRUD, tier line items (`PricingTierItem`), quick actions, status toggles.                                                                                                                                    |
| **Client product pricing**   | `ClientPricingController` — per-user (`users.id`) + product rules with date range (`start_date` / `end_date`), overlap checks.                                                                                                              |
| **Company–customer pricing** | `CompanyPricingController` — one row per `(company_id, client_id)` with optional tier + discount; product-level overrides live in `company_customer_product_pricing` (used in `PricingService`, not full CRUD in the controllers reviewed). |
| **Client tier assignment**   | `ClientTierController` — sets `client_details.pricing_tier_id` and `client_code`.                                                                                                                                                           |
| **Volume rules**             | `VolumeRuleController` — CRUD for `volume_discount_rules`.                                                                                                                                                                                  |
| **Volume discount API (UI)** | `VolumeDiscountController::calculate` — JSON helper using `VolumeDiscountService`.                                                                                                                                                          |
| **Public-style API preview** | `GET /api/pricing/preview` — `PricingController::preview` returns JSON from `PricingService::calculate`.                                                                                                                                    |
| **Imports**                  | `PricingImportController` + `ImportClientProductPricingJob` / `ImportPricingTierItemsJob`.                                                                                                                                                  |
| **Cart integration**         | `ProductController::addCartItem` calls `PricingService::calculate` for users with `client` role.                                                                                                                                            |

**Note:** Entity `DealProposalPricing` exists (`deal_proposal_pricing` table) but **no other PHP references** were found in the repo — likely **unused / future** integration.

---

## 2. Flow Diagram (text)

### 2.1 Typical web request (authenticated)

```
User (browser)
  → Middleware: web, auth
  → Route: Modules/Pricing/Routes/web.php (prefix account/pricing)
  → Controller: e.g. ClientPricingController, PricingTierController, …
  → (No separate “Service” layer for CRUD — direct Eloquent on Entities)
  → Model: ClientProductPricing, PricingTier, CompanyCustomerPricing, VolumeDiscountRule, …
  → Database tables (see section 3)
  → Response: Blade view or Reply:: JSON (ajax)
```

### 2.2 Price calculation (cart / preview)

```
User or API client
  → ProductController::addCartItem (client role) OR GET /api/pricing/preview
  → PricingService::calculate($productId, $clientId, $quantity)
      → Product, User, ClientProductPricing, CompanyCustomerPricing,
         CompanyCustomerProductPricing, ClientDetails, PricingTier,
         PricingTierItem, VolumeDiscountService::calculate
  → DB: multiple reads (see §3)
  → Array / JSON: unit_price, price (line total after volume discount), applied source, tier_id, volume_discount
```

**ASCII diagram**

```
User → Route → Controller → [PricingService / VolumeDiscountService] → Model → DB → JSON / Reply
```

**Web CRUD (no PricingService)**

```
User → Route → Controller → Entity (Eloquent) → DB → View / Reply
```

---

## 3. Database Flow

### 3.1 Tables (Pricing module–related)

| Table                              | Entity / usage                                                                                                          |
| ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `client_product_pricing`           | `ClientProductPricing` — per client user + product, company-scoped (`HasCompany`), date range.                          |
| `company_customer_pricing`         | `CompanyCustomerPricing` — seller `company_id` + buyer `client_id` (user id post-migration).                            |
| `company_customer_product_pricing` | `CompanyCustomerProductPricing` — per-contract product `custom_price` / discounts.                                      |
| `pricing_tiers`                    | `PricingTier` — tier definitions; `company_id` nullable (platform vs company).                                          |
| `pricing_tier_items`               | `PricingTierItem` — per-tier per-product overrides; **no** `HasCompany` (scoped via tier).                              |
| `volume_discount_rules`            | `VolumeDiscountRule` — quantity bands, `applies_to_type` all/products.                                                  |
| `deal_proposal_pricing`            | `DealProposalPricing` — **unused** in codebase search.                                                                  |
| `client_details`                   | `App\Models\ClientDetails` — `pricing_tier_id`, `client_code` (not Pricing module entity, but core to tier resolution). |
| `users`                            | Client identity (`client_id` in pricing tables = `users.id`).                                                           |
| `products`                         | Base price and `company_id` (seller).                                                                                   |

### 3.2 Relationships (conceptual)

- `client_product_pricing` → `users` (`client_id`), `products` (`product_id`), `companies` (`company_id`).
- `company_customer_pricing` → `companies` (`company_id`), `users` (`client_id`), `pricing_tiers` (`pricing_tier_id` optional).
- `company_customer_product_pricing` → `company_customer_pricing`, `products`.
- `pricing_tier_items` → `pricing_tiers`, `products`.
- `volume_discount_rules` → `companies` (optional `company_id`), optional `products` via `applies_to_product_id`.

### 3.3 Read/write patterns

- **Reads in `PricingService::calculate`:** sequential lookups — `Product::findOrFail`, `User::find`, `ClientProductPricing::where(...)`, corporate chain, `ClientDetails::where('user_id', ...)`, `PricingTier::find`, `PricingTierItem::where(...)`, then `VolumeDiscountService` querying `volume_discount_rules` per cart line item in a loop.
- **Writes:** Controllers `store` / `update` / `destroy` on respective entities; imports via queued jobs writing `ClientProductPricing` / tier items.
- **Joins:** `ClientTiersDataTable` joins `users`, `role_user`, `roles`, `client_details`, `pricing_tiers` for listing clients with tier names.
- **Filters:** `CompanyScope` (see §7) on models using `HasCompany`; explicit `where('company_id', user()->company_id)` in some controllers; `PricingService` filters by dates, `is_active`, quantities.

---

## 4. Request Flow (detailed examples)

### 4.1 `GET /api/pricing/preview`

| Step       | File                                                     | Function    | What happens                                                                                   |
| ---------- | -------------------------------------------------------- | ----------- | ---------------------------------------------------------------------------------------------- |
| Route      | `Modules/Pricing/Routes/api.php`                         | —           | `Route::get('pricing/preview', …)` under `api` prefix; middleware `api` only (**no `auth`**).  |
| Controller | `Modules/Pricing/Http/Controllers/PricingController.php` | `preview`   | Reads `product_id`, `client_id`, `quantity`; instantiates `PricingService`; calls `calculate`. |
| Service    | `Modules/Pricing/Services/PricingService.php`            | `calculate` | Full pricing resolution + volume discount.                                                     |
| Models     | Same service file + entities                             | —           | Eloquent queries as above.                                                                     |
| Response   | `PricingController`                                      | —           | `response()->json($result)`.                                                                   |

### 4.2 `POST /account/pricing/client-pricing` (store)

| Step       | File                                                           | Function | What happens                                                                                                         |
| ---------- | -------------------------------------------------------------- | -------- | -------------------------------------------------------------------------------------------------------------------- |
| Route      | `Modules/Pricing/Routes/web.php`                               | —        | `Route::post('client-pricing', …)` inside `middleware auth`, prefix `account/pricing`.                               |
| Controller | `Modules/Pricing/Http/Controllers/ClientPricingController.php` | `store`  | `abort_403` on permission; validates input; overlap check; creates `ClientProductPricing`; `Reply::successWithData`. |
| Service    | —                                                              | —        | None.                                                                                                                |
| Model      | `Modules/Pricing/Entities/ClientProductPricing.php`            | —        | `save()`.                                                                                                            |

### 4.3 Client adds to cart (integrated flow)

| Step       | File                                         | Function      | What happens                                                                                                                                            |
| ---------- | -------------------------------------------- | ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Route      | App routes                                   | —             | Product/cart route leading to `ProductController::addCartItem`.                                                                                         |
| Controller | `app/Http/Controllers/ProductController.php` | `addCartItem` | For `client` role, `app(PricingService::class)->calculate($productId, user()->id, $quantity)`; derives unit price from returned `price` / `unit_price`. |
| Service    | `PricingService.php`                         | `calculate`   | Same as API preview.                                                                                                                                    |

### 4.4 `POST /account/pricing/discount/calculate`

| Step       | File                                                            | Function    | What happens                                                                                                                               |
| ---------- | --------------------------------------------------------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| Route      | `web.php`                                                       | —           | `VolumeDiscountController@calculate`.                                                                                                      |
| Controller | `Modules/Pricing/Http/Controllers/VolumeDiscountController.php` | `calculate` | Passes `items` to `VolumeDiscountService::calculate($items)` — **no** second argument, so company context from `company()` inside service. |
| Service    | `Modules/Pricing/Services/VolumeDiscountService.php`            | `calculate` | Per item: query matching `VolumeDiscountRule`, sum discount.                                                                               |

---

## 5. Logic Analysis (core business rules)

### 5.1 `PricingService::calculate` (conditions / branches)

- **Client product pricing wins** if: `client_id`, `product_id`, `is_active`, `start_date <= now`, `end_date >= now` (see **edge cases** in Issues).
- **Corporate path** `getClientContractPricing($sellerCompanyId, $clientId, $productId)`: requires `CompanyCustomerPricing` row for seller + client; then product-level custom price, or company-level discount (`fixed_amount` mapped to `fixed`), or tier linked on the contract (with tier validity and tier item / tier-level discount).
- **Tier path:** `ClientDetails` by `user_id = $clientId`; tier must be active and within `valid_from` / `valid_to`; tier `company_id` must be `null` **or** match product’s `company_id`; then tier item or tier-level discount.
- **Default:** base `products.price`, then stage 2.

### 5.2 `applyDiscount` (`PricingService`)

- Supports `custom_price`, `percentage`, `fixed`, `specific_price`.

### 5.3 `VolumeDiscountService::calculate`

- For each input line: builds query on active rules, optional company filter (see Issues), product vs “all”, quantity between min and max, picks **first** rule after `orderByDesc('minimum_quantity')->orderBy('id')` — **not** “best discount” for multiple matching rules.

### 5.4 Validations (examples)

- **ClientPricingController:** `client_id`, `product_id`, dates, discount fields; overlap query for date ranges.
- **PricingTierController:** tier fields; `storeItem` validates `product_id` exists in `products`.
- **VolumeRuleController:** rule name, quantities, discount, `applies_to_type`, optional `product_id`.

---

## 6. Issues Found

### 6.1 Logic / correctness

| Issue                                         | Detail                                                                                                                                                                                                                                                                  |
| --------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`calculatePrice` uses `clientId = 0`**      | `PricingService::calculatePrice` calls `calculate($productId, 0, $quantity)`. User id `0` is invalid; `User::find(0)` is null; corporate / tier behavior is wrong if anything relies on a real client. Method is marked legacy in comments — still dangerous if called. |
| **Corporate contract validity dates ignored** | `CompanyCustomerPricing` has `valid_from` / `valid_to` in `$fillable`, but `getClientContractPricing` does **not** filter by them.                                                                                                                                      |
| **`custom_discount_value` falsy check**       | `if ($relation->custom_discount_type && $relation->custom_discount_value)` treats `0` as “no discount” — intentional or bug depends on product requirements.                                                                                                            |
| **Volume rule type `tiered`**                 | DB enum allows `tiered`; `VolumeDiscountService` only handles `percentage` and `fixed_amount` — `tiered` yields **no** discount.                                                                                                                                        |
| **Import job vs date columns**                | `ImportClientProductPricingJob` does not set `start_date` / `end_date` after migration made them required — risk of **failed saves** or reliance on model/database defaults (none defined in entity for defaults).                                                      |
| **`firstOrNew` in import**                    | With **multiple** rows per `(client_id, product_id)` allowed (unique dropped), `firstOrNew` may update an arbitrary row — **ambiguous** with overlapping windows.                                                                                                       |
| **`DealProposalPricing`**                     | Entity/table appears **unused** — dead code or incomplete feature.                                                                                                                                                                                                      |

### 6.2 Missing validation / authorization gaps

| Issue                                     | Detail                                                                                                                                                                                                                                    |
| ----------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **API `pricing/preview` unauthenticated** | Anyone can probe `product_id` + `client_id` + `quantity` and receive computed pricing JSON — **information disclosure** and **no rate limit** in module code.                                                                             |
| **Client tier assignment**                | `ClientTierController::edit` / `update` use `User::findOrFail($id)` without verifying the user is a **client of the current company** — potential **IDOR** if IDs are guessable and `company()` context does not restrict `User` queries. |
| **Bulk actions**                          | `row_ids` from request are not always validated as “integers / owned rows” beyond global scopes — pairing with CSRF session reduces risk but IDs should still be validated.                                                               |
| **`PricingTierController::changeStatus`** | No explicit permission check in method (relies on route being only called from authorized UI — still inconsistent with other actions).                                                                                                    |

### 6.3 Wrong or fragile conditions

| Issue                                               | Detail                                                                                                                           |
| --------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Client product pricing date vs `Carbon` casting** | Columns are `dateTime`; comparisons use `now()` — generally OK; timezone alignment with `company()` should be verified app-wide. |
| **Overlap check**                                   | Overlap query does not exclude `is_active = false` — inactive rows can still **block** new active ranges.                        |

### 6.4 Performance

| Issue                                              | Detail                                                                                                                                                                                                                                                                                                |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **N+1 in `VolumeDiscountService`**                 | One **query per item** in `$items` loop — for large multi-line payloads, this scales poorly.                                                                                                                                                                                                          |
| **Repeated `Product::find` / `PricingTier::find`** | Acceptable for single-product `calculate`, but multiple `find` calls could be consolidated.                                                                                                                                                                                                           |
| **Indexes**                                        | Migrations add indexes on `client_id`, `[client_id, product_id]` for `client_product_pricing`; `volume_discount_rules` has indexes on `company_id`, `applies_to_product_id`. Consider composite indexes matching common `where` + `orderBy` in volume rule selection if profiling shows slow queries. |

### 6.5 Security

| Issue                                                 | Detail                                                                                                                                                                                                                                                                                                              |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **SQL injection**                                     | Eloquent / validated requests — **low risk** for normal paths; raw user strings in DataTable `like` queries follow existing app patterns.                                                                                                                                                                           |
| **Unauthenticated API preview**                       | As above — **high** for sensitive pricing.                                                                                                                                                                                                                                                                          |
| **`CompanyScope` when `company()` is null**           | If `auth` present but `company()` is null, `HasCompany` models **omit** `company_id` filter — can expose **cross-tenant** rows for super-admin or misconfigured sessions (by design in `App\Scopes\CompanyScope` — verify all Pricing admin routes always have `company()`).                                        |
| **`VolumeDiscountService` when `$companyId` is null** | If `companyId` is not passed and `company()` is null, the code **does not** restrict rules by `company_id` — may aggregate **all companies’** active rules (critical if invoked from context without company). `PricingService` passes **seller** `company_id` into `applyVolumeDiscount` — **good** for cart path. |

---

## 7. Suggested Fixes (for a follow-up implementation pass)

1. **Protect `GET /api/pricing/preview`:** Add `auth:sanctum` / token, or throttle + signed URLs, or move under `account` with policies — align with product owner’s threat model.
2. **Fix or remove `calculatePrice`:** Pass a real `clientId` or remove the wrapper; document callers.
3. **Corporate contract dates:** Apply `valid_from` / `valid_to` in `getClientContractPricing` if those fields are in use.
4. **Import job:** Set `start_date` / `end_date` explicitly; align with overlap rules.
5. **Volume discount:** Either handle `tiered` in code or disallow it in validation; document rule selection (`orderBy` vs “best discount”).
6. **Client tier IDOR:** Restrict `User` query to clients belonging to the current company (same pattern as `User::allClients()`).
7. **Overlap query:** Optionally ignore inactive rows when checking conflicts.
8. **Performance:** Batch volume rule resolution for many lines (e.g. preload rules once, filter in PHP) or single query with careful ordering semantics.
9. **`changeStatus` permission:** Align `PricingTierController::changeStatus` with other tier endpoints (permission + company scope).

---

## 8. File reference index (quick lookup)

| Role                | Path                                                                                                                                                                                  |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Web routes          | `Modules/Pricing/Routes/web.php`                                                                                                                                                      |
| API routes          | `Modules/Pricing/Routes/api.php`                                                                                                                                                      |
| Route registration  | `Modules/Pricing/Providers/RouteServiceProvider.php`                                                                                                                                  |
| Core pricing engine | `Modules/Pricing/Services/PricingService.php`                                                                                                                                         |
| Volume discounts    | `Modules/Pricing/Services/VolumeDiscountService.php`                                                                                                                                  |
| API JSON preview    | `Modules/Pricing/Http/Controllers/PricingController.php`                                                                                                                              |
| CRUD                | `ClientPricingController`, `CompanyPricingController`, `PricingTierController`, `ClientTierController`, `VolumeRuleController`, `VolumeDiscountController`, `PricingImportController` |
| Jobs                | `Modules/Pricing/Jobs/ImportClientProductPricingJob.php`, `ImportPricingTierItemsJob.php`                                                                                             |
| Integration         | `app/Http/Controllers/ProductController.php` (`addCartItem`)                                                                                                                          |

---

_End of analysis._
