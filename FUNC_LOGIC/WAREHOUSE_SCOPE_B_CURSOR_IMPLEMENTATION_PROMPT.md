# Cursor / AI — Implementation Prompt: Warehouse Scope B (Miaolin inventory-aware sales)

**Status:** PM confirmed **Scope B** — **backend v1 đã triển khai trong repo** (xem nhật ký `WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md`). **UAT staging + Go/No-Go** vẫn là bước bắt buộc trước khi coi “production-ready”.  
**Goal:** Implement warehouse **outbound** on sales, **reversal** paths, remove unsafe **legacy stock** side effects, add **guards/tests** so stock stays consistent with `StockMovementService` and `stock_movements` ledger.

**Honest quality bar:** “No errors” in production means **automated tests + staging UAT + idempotent posting + transaction safety** — not a promise of zero unknown edge cases without QA sign-off.

---

## A) Paste this block into Cursor (Composer / Agent) as the main task

```
You are working on the Craveva Laravel codebase (warehouse multi-tenant). PM signed Scope B:
inventory-aware sales must reduce PHYSICAL warehouse stock via StockMovementService (recordOutbound),
with reversals, and without conflicting legacy PurchaseStockAdjustment mutations.

NON-NEGOTIABLES
1) All stock changes for sales MUST go through Modules/Warehouse/Services/StockMovementService (recordOutbound / appropriate reversal strategy). No direct edits to warehouse_product_batches or warehouse_product_stock from controllers.
2) Use DB::transaction for each business posting unit (per invoice event or per line batch — choose one documented strategy).
3) Idempotency: posting the same business event twice MUST NOT double-deduct stock. Implement a clear idempotency strategy (e.g. persisted “posting_applied” flags on invoice/order lines, or unique reference keys in stock_movements, or a dedicated sales_stock_postings table).
4) Remove or feature-flag legacy stock mutation in Modules/Purchase/Observers/PaymentObserver.php that adjusts PurchaseStockAdjustment by product_id without warehouse — it conflicts with multi-warehouse truth.
5) Replace or augment Invoice stock validation that uses PurchaseStockAdjustment::sum(net_quantity) by product only — must validate availability per warehouse when invoice lines carry warehouse context (or use a defined fallback: client default warehouse / company default / line warehouse).
6) Respect company scoping and existing WarehouseBusinessException / HandlesWarehouseErrors patterns where applicable.
7) Config: document and enforce “only ONE inbound canonical path” in prod (PO delivered XOR DO received) — add a runtime warning or admin notice if both env flags are true.

DELIVERABLES
- A dedicated service class (name suggestion: SalesWarehouseStockService or InvoiceWarehouseStockService) that:
  - Resolves warehouse_id per invoice line using PM-approved rules (see DECISIONS below).
  - Calls StockMovementService::recordOutbound with payload: company_id, warehouse_id, product_id, quantity, reference_type (Invoice::class or InvoiceItems::class), reference_id, and any needed metadata.
  - Implements reversal for void/cancel/delete/update flows that change quantities (define which invoice statuses trigger reversal — align with PM).
- Wire into the correct lifecycle point (PM must confirm trigger). Likely candidates in this codebase:
  - app/Observers/InvoiceObserver.php (created/updated/deleted) OR invoice status transitions
  - InvoiceController flows if that is where business “finalization” happens
- Migration if you need a table for idempotency / posted flags (preferred over silent duplicates).
- Feature flag env e.g. WAREHOUSE_SALES_OUTBOUND_ENABLED=true for safe rollout.
- PHPUnit / Feature tests for: happy path outbound, insufficient stock blocked, idempotent repost, reversal restores stock, payment observer no longer mutates legacy stock when flag on.

FILES TO REVIEW FIRST (do not guess)
- Modules/Warehouse/Services/StockMovementService.php
- Modules/Purchase/Observers/PaymentObserver.php
- app/Http/Controllers/InvoiceController.php (stock check using PurchaseStockAdjustment)
- app/Models/Invoice.php, app/Models/InvoiceItems.php (fields for warehouse? client relation for default_warehouse_id?)
- Modules/Warehouse/Exceptions/WarehouseBusinessException.php + Http/Controllers/Concerns/HandlesWarehouseErrors.php
- FUNC_LOGIC/WAREHOUSE_UAT_PRE_IMPLEMENTATION_ANALYSIS.md
- FUNC_LOGIC/WAREHOUSE_PM_ENG_ALIGNMENT_BRIEF.md

ACCEPTANCE CRITERIA (must pass before merge)
- Creating/processing a sales document per PM trigger reduces warehouse stock and creates stock_movements rows with correct warehouse_from_id/outbound semantics.
- Re-running the same trigger does NOT duplicate deductions (idempotency).
- Cancelling/reversing per PM rules restores stock correctly.
- With WAREHOUSE_SALES_OUTBOUND_ENABLED=true, PaymentObserver does not adjust PurchaseStockAdjustment for stock purposes (or is fully bypassed behind the flag).
- Invoice “insufficient stock” validation is consistent with warehouse availability when module warehouse is enabled.
- php artisan test (or project test command) passes for new tests; run pint/phpstan if project uses them.

OUT OF SCOPE (unless PM explicitly asks)
- Deep links in movement ledger UI
- Full migration off all legacy PurchaseStockAdjustment usages across entire ERP in one PR

DECISIONS REQUIRED FROM PM (insert before coding if not already written down)
- OUTBOUND_TRIGGER: [ invoice_created | invoice_sent | invoice_paid | delivery_confirmed | other ]
- WAREHOUSE_RESOLUTION_RULE: [ per_invoice_item_warehouse | invoice_header_warehouse | client.default_warehouse_id | company_default_warehouse ]
- REVERSAL_MATRIX: which events restore stock (void invoice, delete invoice, credit note, return quantity change, etc.)

Proceed in small commits: (1) feature flag + service skeleton + tests, (2) observer/controller integration, (3) remove/disable legacy payment stock, (4) invoice validation alignment, (5) docs/env example updates.
```

---

## B) Decisions PM must fill (copy to Slack/email if empty)

**Engineering defaults (v1) are in `WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md` — PM should confirm or override.**

| Field                                                      | Value (PM fills)                                                               |
| ---------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `OUTBOUND_TRIGGER`                                         | _Default code:_ non-draft, non–credit-note; sync after save (created/updated). |
| `WAREHOUSE_RESOLUTION_RULE`                                | _Default code:_ client default → company default → first active warehouse.     |
| `REVERSAL_MATRIX` (short list)                             | _Default code:_ delete = reverse; update = reverse+repost.                     |
| Default warehouse if line has no warehouse                 | _Same as resolution rule_ (no per-line column yet).                            |
| `WAREHOUSE_SALES_OUTBOUND_ENABLED` rollout: staging first? | **Recommended:** Yes                                                           |

---

## C) Verification checklist (human + automated)

**Automated**

- [x] New/updated tests green locally and in CI (ít nhất unit tests Scope B; mở rộng feature test DB tùy roadmap).
- [x] No `PurchaseStockAdjustment` stock mutation left on payment path when outbound flag is ON (`PaymentObserver` + test unit).

**Staging UAT (from `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md` + Scope B)**

- [ ] Sale triggers outbound; movement ledger shows outbound with correct reference.
- [ ] Insufficient stock blocked with clear message (per warehouse).
- [ ] Reversal restores quantity; ledger consistent.
- [ ] Idempotency: repeat action does not double-post.
- [ ] PO/DO inbound still single canonical path; no double count with env.

**Docs**

- [ ] Update `FUNC_LOGIC/WAREHOUSE_UAT_GO_NO_GO_SHEET.md` status when ready (code landed — chờ bằng chứng UAT).
- [x] `.env.example` documents new flags.
- [x] Nhật ký triển khai: `WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md`.

---

## D) What “đảm bảo không lỗi” realistically means

| Layer                     | What we guarantee                                                 |
| ------------------------- | ----------------------------------------------------------------- |
| Code                      | Transactions, validation, idempotency hooks, tests for core paths |
| Process                   | Staging UAT + PM sign-off on trigger/rules                        |
| Not guaranteed without QA | Every edge case in legacy invoice/order combinations              |

---

## E) Trạng thái triển khai & quyết định đã gắn trong code (cập nhật 2026-03-28)

**Nhật ký chi tiết:** `FUNC_LOGIC/WAREHOUSE_SCOPE_B_IMPLEMENTATION_LOG.md`

| Hạng mục prompt                                                                                                           | Trạng thái                                                                                                |
| ------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| `InvoiceWarehouseStockService` + `recordOutbound` / reversal                                                              | Đã có                                                                                                     |
| Idempotency (`invoice_warehouse_stock_postings`)                                                                          | Đã có                                                                                                     |
| `DB::transaction` (đơn vị: sync invoice = reverse + post lại trong một transaction; controller bọc `save()` khi bật flag) | Đã có                                                                                                     |
| Feature flag `WAREHOUSE_SALES_OUTBOUND_ENABLED`                                                                           | Đã có                                                                                                     |
| `PaymentObserver` không sửa legacy stock khi flag bật                                                                     | Đã có                                                                                                     |
| Invoice validation theo kho (khi outbound bật + `direct`)                                                                 | Đã có                                                                                                     |
| Cảnh báo runtime khi cả hai inbound PO+DO bật                                                                             | Đã có (log)                                                                                               |
| PHPUnit                                                                                                                   | Unit tests Scope B (`shouldPostOutbound`, guard `app.seeding`); feature test DB đầy đủ = tùy chọn mở rộng |
| Sync/reverse khi CLI / queue / PHPUnit                                                                                    | **Không** chặn bằng `runningInConsole` — chỉ bỏ qua khi `config('app.seeding')` (db:seed)                 |

**Quyết định PM (v1 — mặc định kỹ thuật, chờ xác nhận chính thức):**

| Field                       | Giá trị trong code                                                               |
| --------------------------- | -------------------------------------------------------------------------------- |
| `OUTBOUND_TRIGGER`          | Không draft + không credit note — sync sau `created` / cuối `updated`            |
| `WAREHOUSE_RESOLUTION_RULE` | Client default warehouse → company default → kho active đầu tiên                 |
| `REVERSAL_MATRIX`           | Xóa invoice: reverse trước khi xóa dòng; cập nhật: reverse+post lại (idempotent) |

---

_End of prompt pack — version with repo paths: 2026-03-28_
