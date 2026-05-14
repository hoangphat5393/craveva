# P0-05 — Bidirectional trace (Warehouse ↔ Production) — UAT checklist (English)

**Purpose:** Formal manual verification that operators can navigate **from a warehouse product batch to related production context** and **from a production batch trace back to warehouse batch detail**, as required by P0-05.

**Prerequisites**

- Tenant has **Warehouse** and **Production** modules enabled (`module_settings`).
- User has at least **`view_production_orders`** and **`view_warehouse_stock`** (or equivalent to open batch list/detail).
- At least one **production batch** that consumed or produced stock linked to **`warehouse_product_batches`** (real pilot data or demo seed).

---

## A) Production → Warehouse

| Step | Action                                                                         | Expected                                                                                                         |
| ---- | ------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| A1   | Open **Production** → **Production orders** → pick an order → open a **batch** | Batch detail loads.                                                                                              |
| A2   | Open **Trace** (`production.batches.trace`)                                    | Trace page lists movements / links.                                                                              |
| A3   | Click a link to **warehouse product batch** (when shown)                       | Browser navigates to `warehouse.product-batches.show` (URL contains `/account/warehouse-product-batches/` + id). |
| A4   | Confirm batch identity                                                         | Product, warehouse, batch number / qty align with production context.                                            |

**Evidence to attach:** Screenshot or URL of trace page + destination warehouse batch detail.

---

## B) Warehouse → Production

| Step | Action                                                                                                     | Expected                                                                                |
| ---- | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| B1   | Open **Warehouse** → **Product batches** list → open one batch (`warehouse.product-batches.show`)          | Detail page loads.                                                                      |
| B2   | If the batch is referenced by production, follow **link to production batch / trace** (when present in UI) | Lands on `production.batches.show` or `production.batches.trace` for the correct batch. |
| B3   | Confirm round-trip                                                                                         | Batch code / order reference matches expectation.                                       |

**Evidence to attach:** Screenshot or URL of warehouse batch + production destination.

---

## Result

| Field             | Value |
| ----------------- | ----- |
| Date              |       |
| Tester            |       |
| Environment (URL) |       |
| Pass / Fail       |       |
| Issues (severity) |       |

When both **A** and **B** pass, update `FUNC_IMPROVE/P0_EXECUTION_LOG.md` (P0-05 row) with this checklist reference + evidence links.

**Automated (Dev, không thay Pass/Fail):** `php artisan test --compact tests/Feature/P0BiomixingAutomatedEvidenceTest.php` — kiểm tra Blade vẫn gọi `warehouse.product-batches.show` từ trace và `production.batches.trace` từ batch detail.
