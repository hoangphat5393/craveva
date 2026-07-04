# Pricing Module — Actionable Development Tasks

## Trạng thái rà soát (2026-06-14)

- Đây vẫn là backlog improve, chưa phải tài liệu “done”.
- Đối chiếu nhanh code hiện tại:
    - Đã có: `SEC-01` (route `pricing/preview` đã nằm sau `auth:sanctum`).
    - Đã có (2026-06-14): `SEC-02`, `SEC-03`, `SEC-04`, `SEC-05`, `SEC-06`, `LOG-01`, `LOG-02`, `LOG-04`, `LOG-05`, `LOG-06`, `LOG-07`, `PERF-01` — xem test `Modules\Pricing\Tests\Unit\ContractPricingTest.php` và `tests/Feature/PricingHardeningTest.php`.
    - Đã có (2026-06-16): `PERF-03` — `PricingService` không lookup lại Product trong corporate pricing path khi `calculate()` đã load product; tier lookup được cache trong service instance.
    - Chưa hoàn tất: `PERF-02`, `REF-01` — đều là low-priority / cần profiling hoặc quyết định riêng trước khi làm.
- Kết luận: giữ file này trong `FUNC_IMPROVE` là đúng; không xóa.

**Regression 2026-06-16:** `php artisan test --compact Modules\Pricing\Tests\Unit\ContractPricingTest.php tests\Feature\PricingHardeningTest.php` → **18 passed / 30 assertions**.

Derived from: `FUNC_LOGIC/PRICING_BUSINESS.md`
Audience: developers implementing fixes (Laravel).  
Status: backlog — not implemented unless ticketed separately.

---

## How to use each task

Each task includes: **Problem** → **Risk** → **Solution** → **Example** → **Priority** → **Category**.

---

# Security

---

### SEC-01 — Lock down or remove unauthenticated `GET /api/pricing/preview`

| Field        | Content                                                                                                                                                                                                                                                                                                                                              |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Problem**  | Route `Modules/Pricing/Routes/api.php` exposes `pricing/preview` with only `api` middleware. Any client can pass `product_id`, `client_id`, `quantity` and receive computed pricing JSON.                                                                                                                                                            |
| **Risk**     | **Security** — information disclosure (pricing strategy, contract hints). **Compliance** — sensitive commercial data. No rate limiting in module.                                                                                                                                                                                                    |
| **Solution** | Choose one policy (product owner approval): (A) Require `auth:sanctum` / `auth:api` and authorize caller can view that product/client pricing; (B) Move endpoint under `account` + session auth + same policy; (C) Keep public but require signed URL + short TTL + throttle; (D) Remove endpoint if unused. Add `throttle` middleware in all cases. |
| **Example**  | ```php                                                                                                                                                                                                                                                                                                                                               |

// Modules/Pricing/Routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
Route::get('pricing/preview', [PricingController::class, 'preview'])
->middleware('can:viewPricingPreview,product'); // or inline policy
});

````|
| **Priority** | **Critical** |

---

### SEC-02 — Fix IDOR on client tier assignment (`ClientTierController`)

**Status 2026-06-14:** Done — `edit` / `update` dùng scoped client query theo role client + company hiện tại; tier list cũng scope theo company/platform.

| Field | Content |
|-------|---------|
| **Problem** | `edit` / `update` use `User::findOrFail($id)` without verifying the user is a **client** of the **current company** (same tenant as `company()`). |
| **Risk** | **Security** — Insecure Direct Object Reference (IDOR): staff might change another company’s client tier if they can guess or obtain user IDs. |
| **Solution** | Resolve allowed users the same way as `User::allClients()` (or a dedicated query scoped by `company_id` / role `client`). If `$id` is not in the set, `abort(403)`. Apply in both `edit` and `update`. |
| **Example** | ```php
// Modules/Pricing/Http/Controllers/ClientTierController.php — inside edit/update

$user = User::query()
    ->whereKey($id)
    ->whereHas('roles', fn ($q) => $q->where('name', 'client'))
    ->where(function ($q) {
        $q->where('company_id', company()->id)
            ->orWhereHas('clientDetails', fn ($d) => $d->where('company_id', company()->id));
    })
    ->firstOrFail();
``` |
| **Priority** | **High** *(adjust query to match your actual `User::allClients()` semantics)* |

---

### SEC-03 — Harden `VolumeDiscountController::calculate` company context

**Status 2026-06-14:** Done — controller truyền `company()->id`; service khi không có context chỉ đọc platform rules (`company_id IS NULL`), không đọc rules của mọi tenant.

| Field | Content |
|-------|---------|
| **Problem** | `VolumeDiscountController::calculate` calls `VolumeDiscountService::calculate($items)` without passing seller `company_id`. Service falls back to `company()`; when `company()` is null, `VolumeDiscountRule` query may not restrict by `company_id` (see `VolumeDiscountService` lines 32–36). |
| **Risk** | **Security** — Cross-tenant rule leakage or wrong discount if invoked from API/job without company context. |
| **Solution** | (1) Pass explicit `company_id` from authenticated user: `companyId: user()->company_id` or from request only after validation against policy. (2) In `VolumeDiscountService`, when `$contextCompanyId` is null, **default to no rows** or only platform rules (`whereNull('company_id')`) — never “all companies”. Document behavior. |
| **Example** | ```php
// VolumeDiscountController.php
$result = $service->calculate(
    $items,
    company()?->id ?? abort(403, 'Company context required.')
);
``` |
| **Priority** | **High** |

---

### SEC-04 — Validate and scope bulk `row_ids` (quick actions)

**Status 2026-06-14:** Done — Pricing quick actions dùng helper `ValidatesBulkRowIds`: validate `row_ids` string, chỉ nhận ID số dương, unique, cap 500; các bulk update/delete đều scope theo `company_id`.

| Field | Content |
|-------|---------|
| **Problem** | Controllers use `explode(',', $request->row_ids)` without strict validation (empty strings, non-numeric, huge lists). Relies on global scopes for isolation. |
| **Risk** | **Security** — Mass-assignment style abuse, DoS via huge payloads; **bug** — accidental deletes if UI mis-sends IDs. |
| **Solution** | Add Form Request or shared rule: `row_ids` required, string, max length; split; `array_filter`; cast each to int; `array_unique`; cap max count (e.g. 500). Optionally verify each ID exists in scoped query before `delete`/`update`. |
| **Example** | ```php
$ids = collect(explode(',', $request->input('row_ids', '')))
    ->map(fn ($v) => (int) trim($v))
    ->filter(fn ($id) => $id > 0)
    ->unique()
    ->take(500)
    ->values()
    ->all();

$request->validate(['row_ids' => 'required|string|max:10000']);
``` |
| **Priority** | **Medium** |

---

### SEC-05 — Audit `CompanyScope` + `company()` for Pricing admin routes

**Status 2026-06-14:** Done — Pricing account controllers đều có middleware abort khi thiếu `company()`; các đường quick/status/edit/update quan trọng đã bổ sung scope rõ theo `company_id`.

| Field | Content |
|-------|---------|
| **Problem** | `App\Scopes\CompanyScope`: when `company()` is null but user is authenticated, `HasCompany` models **do not** add `company_id` filter — cross-tenant visibility possible for super-admin or broken session. |
| **Risk** | **Security** — Accidental data exposure across tenants on Pricing screens. |
| **Solution** | Document: all Pricing web routes that touch `HasCompany` models must run only when `company()` is set (middleware already aborts in several controllers — extend consistently). Add automated test: hit `ClientPricingController` index as user without company → 403. |
| **Example** | ```php
// Middleware (reusable): EnsureCompanyContext
if (! company()) {
    abort(403, 'Company context is required.');
}
``` |
| **Priority** | **Medium** |

---

### SEC-06 — Add permission checks to `PricingTierController::changeStatus`

**Status 2026-06-14:** Done — `PricingTierController::changeStatus` validate payload, check `edit_pricing_tiers`, và resolve tier bằng `company_id` hiện tại trước khi update status.

| Field | Content |
|-------|---------|
| **Problem** | `changeStatus` updates tier without `user()->permission('edit_pricing_tiers')` like other methods. |
| **Risk** | **Security** — Inconsistent authorization if route is ever exposed or called directly. |
| **Solution** | Mirror `update`: `abort_403($editPermission == 'none');` and ensure tier belongs to company (use scoped query or `PricingTier::where('company_id', user()->company_id)->findOrFail($request->tierId)`). |
| **Example** | ```php
$editPermission = user()->permission('edit_pricing_tiers');
abort_403($editPermission == 'none');

$tier = PricingTier::where('company_id', user()->company_id)
    ->findOrFail($request->tierId);
$tier->is_active = $request->status == 'active';
$tier->save();
``` |
| **Priority** | **Medium** |

---

# Logic bug

---

### LOG-01 — Remove or fix `PricingService::calculatePrice` using `clientId = 0`

**Status 2026-06-14:** Done — legacy `calculatePrice()` đã chuyển sang fail-fast bằng `BadMethodCallException`; không còn silent base-price fallback.

| Field | Content |
|-------|---------|
| **Problem** | `calculatePrice` delegates to `calculate($productId, 0, $quantity)`. User id `0` is invalid; corporate and tier logic that depend on a real user are wrong. |
| **Risk** | **Bug** — Wrong prices for any caller; silent failures in `User::find(0)`. |
| **Solution** | (1) Grep for `calculatePrice(` — remove calls or pass real `clientId`. (2) Deprecate and throw `InvalidArgumentException` if `$clientId` is null/0 until callers fixed. (3) Or delete method if unused. |
| **Example** | ```php
public function calculatePrice(int $productId, ?int $companyId, ?int $customerCompanyId, int $quantity = 1): array
{
    throw new \BadMethodCallException('Use calculate($productId, $clientUserId, $quantity).');
}
``` |
| **Priority** | **High** |

---

### LOG-02 — Enforce `valid_from` / `valid_to` in `getClientContractPricing`

**Status 2026-06-14:** Done — corporate contract pricing bỏ qua hợp đồng future/expired trước khi áp custom price / discount / tier.

| Field | Content |
|-------|---------|
| **Problem** | `CompanyCustomerPricing` has `valid_from` / `valid_to` in fillable but `PricingService::getClientContractPricing` does not filter inactive-by-date contracts. |
| **Risk** | **Bug** — Expired or future contracts still affect price. |
| **Solution** | After loading `$relation`, add: if `valid_from` set, `now()->startOfDay() >= valid_from`; if `valid_to` set, `now()->startOfDay() <= valid_to`. Align date granularity with rest of app (date vs datetime). |
| **Example** | ```php
$now = now()->toDateString();

if ($relation->valid_from && $now < $relation->valid_from) {
    return null;
}
if ($relation->valid_to && $now > $relation->valid_to) {
    return null;
}
``` |
| **Priority** | **High** |

---

### LOG-03 — Clarify zero discount for `custom_discount_value`

**Status 2026-06-14:** Done — corporate contract, volume discount service, và company pricing list đều dùng `!== null` cho discount value; regression xác nhận `0%` contract vẫn được nhận là contract pricing.

| Field | Content |
|-------|---------|
| **Problem** | Condition `if ($relation->custom_discount_type && $relation->custom_discount_value)` treats `0` as “no discount”. |
| **Risk** | **Bug** — If business allows “0%” as explicit override, path is skipped; if `0` means “no row”, current behavior is OK. |
| **Solution** | Product decision: use `!is_null($relation->custom_discount_value)` or `filled()` for string fields. Add unit test for both cases. |
| **Example** | ```php
if ($relation->custom_discount_type !== null && $relation->custom_discount_value !== null) {
    // ...
}
``` |
| **Priority** | **Medium** |

---

### LOG-04 — Support or forbid `tiered` volume discount type

**Status 2026-06-14:** Done — controller create/update volume rule chỉ cho `percentage,fixed_amount`; `tiered` chỉ còn trong migration cũ và chưa được expose cho người dùng.

| Field | Content |
|-------|---------|
| **Problem** | DB enum includes `tiered`; `VolumeDiscountService` only handles `percentage` and `fixed_amount`. |
| **Risk** | **Bug** — Merchants think tiered rules work; customers get zero volume discount. |
| **Solution** | Either implement tiered logic, or restrict `VolumeRuleController` validation to `percentage,fixed_amount` and migration to align enum (if safe). |
| **Example** | ```php
'discount_type' => 'required|in:percentage,fixed_amount',
``` |
| **Priority** | **Medium** |

---

### LOG-05 — Set `start_date` / `end_date` in `ImportClientProductPricingJob`

**Status 2026-06-14:** Done — import field có `start_date` / `end_date`; job default `today()` / `2099-12-31` khi file không gửi ngày.

| Field | Content |
|-------|---------|
| **Problem** | Migration requires non-null `start_date` / `end_date` on `client_product_pricing`. Import job may not set them → save failures or inconsistent data. |
| **Risk** | **Bug** — Failed imports; broken pricing rows. |
| **Solution** | On create/update: set `start_date` to import default (e.g. `today()` or column from CSV) and `end_date` to `2099-12-31` or CSV column. Mirror `ClientPricingController::store` defaults. |
| **Example** | ```php
$pricing->start_date = $startFromCsv ?? now()->startOfDay();
$pricing->end_date = $endFromCsv ?? '2099-12-31';

// If using Carbon cast:
$pricing->end_date = Carbon::parse($pricing->end_date)->endOfDay();
``` |
| **Priority** | **High** |

---

### LOG-06 — Replace ambiguous `firstOrNew` in import when multiple rows per client+product

**Status 2026-06-14:** Done — import key hiện gồm `client_id + product_id + start_date + end_date`, cho phép nhiều date range và update đúng range.

| Field | Content |
|-------|---------|
| **Problem** | Unique constraint on `(client_id, product_id)` was removed; multiple date ranges allowed. `firstOrNew([...])` picks arbitrary row. |
| **Risk** | **Bug** — Wrong row updated; overlaps not managed. |
| **Solution** | Import strategy: (1) Upsert by **explicit** id from CSV; or (2) **delete** old rows for same pair+overlapping range then insert; or (3) `updateOrCreate` with composite key including date range; or (4) one row per pair only — enforce unique again if business allows. |
| **Example** | ```php
ClientProductPricing::updateOrCreate(
    [
        'client_id' => $clientDetails->user_id,
        'product_id' => $product->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ],
    [ /* ... */ ]
);
``` |
| **Priority** | **High** |

---

### LOG-07 — Overlap check: ignore inactive rows

**Status 2026-06-14:** Done — `ClientPricingController` overlap queries trong store/update đã filter `is_active = true`; regression xác nhận inactive range không chặn tạo contract pricing mới.

| Field | Content |
|-------|---------|
| **Problem** | `ClientPricingController` overlap query does not exclude `is_active = false`. Inactive ranges can block new active ranges. |
| **Risk** | **Bug** — Users cannot create valid pricing; support burden. |
| **Solution** | Add `->where('is_active', true)` to overlap queries in `store` and `update`, or business rule: inactive overlaps still block (document if intentional). |
| **Example** | ```php
->where('is_active', true)
``` |
| **Priority** | **Medium** |

---

### LOG-08 — Timezone consistency for pricing dates

**Status 2026-06-14:** Done — `PricingService` dùng `company()->timezone` khi có context, fallback `config('app.timezone')`, cho client pricing date windows và corporate/tier validity checks.

| Field | Content |
|-------|---------|
| **Problem** | `ClientProductPricing` uses datetime comparisons with `now()`; company may use different timezone. |
| **Risk** | **Bug** — Edge-of-day wrong price for global customers. |
| **Solution** | Use company timezone if available (`company()->timezone` or config) for `now()` in `PricingService` client pricing filter; document in `MASTER_DOCUMENTATION` or module README. |
| **Example** | ```php
$now = company()
    ? now()->timezone(company()->timezone ?? config('app.timezone'))
    : now();
``` |
| **Priority** | **Low** |

---

# Performance

---

### PERF-01 — Batch volume discount rule loading in `VolumeDiscountService`

**Status 2026-06-14:** Done — service preload active rules một lần theo company/platform rồi match trong PHP theo cùng thứ tự `minimum_quantity DESC, id ASC`.

| Field | Content |
|-------|---------|
| **Problem** | Loop over `$items` runs **one SQL query per line** to resolve `VolumeDiscountRule`. |
| **Risk** | **Performance** — Slow cart/checkout with many lines; DB load. |
| **Solution** | (1) Preload all active rules for `$companyId` once (`where company_id null or = X`). (2) In PHP, for each item, pick best matching rule using same ordering as `orderByDesc('minimum_quantity')->orderBy('id')`. (3) Add test that results match old behavior for single-item carts. |
| **Example** | ```php
$rules = VolumeDiscountRule::query()
    ->where('is_active', true)
    ->where(function ($q) use ($companyId) {
        $q->whereNull('company_id')->orWhere('company_id', $companyId);
    })
    ->orderByDesc('minimum_quantity')
    ->orderBy('id')
    ->get();

foreach ($items as $item) {
    $rule = $rules->first(fn ($r) => $this->ruleMatches($r, $item));
}
``` |
| **Priority** | **High** |

---

### PERF-02 — Add composite DB indexes after profiling

| Field | Content |
|-------|---------|
| **Problem** | Volume rule query filters `is_active`, `company_id`, `applies_to_*`, quantities, ordering. |
| **Risk** | **Performance** — Full table scans at scale. |
| **Solution** | Run `EXPLAIN` on production-like data. Add composite index matching filter order, e.g. `(company_id, is_active, applies_to_type, minimum_quantity)` — **exact** columns depend on query plan. |
| **Example** | ```php
$table->index(['company_id', 'is_active', 'minimum_quantity'], 'vdr_company_active_minqty');
``` |
| **Priority** | **Low** *(only after profiling)* |

---

### PERF-03 — Reduce duplicate `find` calls in `PricingService` (optional)

**Status 2026-06-16:** Done — `calculate()` truyền Product đã load vào corporate contract pricing path, tránh `Product::find()` lặp lại khi áp corporate discount/tier; `resolvePricingTierById()` cache tier theo id trong cùng service instance. Regression khóa corporate tier item path trong `PricingHardeningTest`.

| Field | Content |
|-------|---------|
| **Problem** | Multiple `Product::find` / `PricingTier::find` in same request. |
| **Risk** | **Performance** — Minor extra queries for single-product path. |
| **Solution** | Pass `$product` instance through private methods; cache tier once per tier id. |
| **Example** | ```php
// Pass $product instead of $productId in private helpers
``` |
| **Priority** | **Low** |

---

# Refactor

---

### REF-01 — Resolve or remove dead `DealProposalPricing` code

| Field | Content |
|-------|---------|
| **Problem** | Entity `DealProposalPricing` / table `deal_proposal_pricing` has no references in PHP codebase. |
| **Risk** | **Maintainability** — Confusion; migration debt. |
| **Solution** | If unused: remove entity, migration drop table (careful with prod), or archive. If planned: add integration ticket and link from proposal module. |
| **Example** | N/A — process decision. |
| **Priority** | **Low** |

---

### REF-02 — Document volume rule selection semantics

**Status 2026-06-14:** Done — `VolumeDiscountService::calculate` có PHPDoc ghi rõ selection order `minimum_quantity DESC, id ASC`; không phải chiến lược “maximum discount wins”.

| Field | Content |
|-------|---------|
| **Problem** | First matching rule after `orderByDesc('minimum_quantity')->orderBy('id')` is **not** “maximum discount”. |
| **Risk** | **Business** — Stakeholders misunderstand promotions. |
| **Solution** | Add PHPDoc on `VolumeDiscountService::calculate` and admin tooltip in `volume_rules` UI explaining selection order. |
| **Example** | ```php
/**
 * Selects the first rule after ordering by minimum_quantity DESC, then id ASC.
 * This is not necessarily the largest discount.
 */
``` |
| **Priority** | **Low** |

---

### REF-03 — `PricingTierController::changeStatus` + permission (duplicate of SEC-06)

**Status 2026-06-14:** Done — đã xử lý trong `SEC-06`; không tách policy riêng trong scope hiện tại.

| Field | Content |
|-------|---------|
| **Problem** | Same as SEC-06 — listed under refactor if treated as consistency pass across all Pricing controllers. |
| **Risk** | **Security / maintainability** |
| **Solution** | Implement SEC-06; optionally extract policy class `PricingTierPolicy`. |
| **Priority** | **Medium** |

---

## Summary matrix

| ID | Title | Category | Priority |
|----|-------|----------|----------|
| SEC-01 | API preview auth | Security | Critical |
| SEC-02 | Client tier IDOR | Security | High |
| SEC-03 | Volume discount company context | Security | High |
| SEC-04 | Bulk row_ids validation | Security | Medium |
| SEC-05 | Company scope audit | Security | Medium |
| SEC-06 | Tier changeStatus permission | Security | Medium |
| LOG-01 | calculatePrice client 0 | Logic bug | High |
| LOG-02 | Corporate contract dates | Logic bug | High |
| LOG-03 | Zero discount value | Logic bug | Medium |
| LOG-04 | Tiered volume type | Logic bug | Medium |
| LOG-05 | Import dates | Logic bug | High |
| LOG-06 | Import firstOrNew | Logic bug | High |
| LOG-07 | Overlap + inactive | Logic bug | Medium |
| LOG-08 | Timezone | Logic bug | Low |
| PERF-01 | Batch volume rules | Performance | High |
| PERF-02 | Indexes | Performance | Low |
| PERF-03 | Cache finds | Performance | Low — Done 2026-06-16 |
| REF-01 | Dead DealProposalPricing | Refactor | Low |
| REF-02 | Document rule semantics | Refactor | Low |
| REF-03 | Policy refactor (optional) | Refactor | Medium |

---

*End of task list.*
````
