# Biomixing — Doc hub (đọc trước / đồng bộ tài liệu)

**Cập nhật:** 2026-05-27 (pass 6 — bỏ audit/legacy đã xong)  
**Nguồn sự thật triển khai:** code + tests + [`BIOMIXING_GAP_STATUS.md`](./BIOMIXING_GAP_STATUS.md)

---

## 1. Living docs — đọc theo thứ tự

| #        | Vai trò                                                      | File                                                                                                                                           |
| -------- | ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| 0        | **Luồng nghiệp vụ (SSOT)**                                   | [`BIOMIXING_BUSINESS_FLOW_LIVE.md`](./BIOMIXING_BUSINESS_FLOW_LIVE.md)                                                                   |
| 1        | Trạng thái code vs PM                                        | [`BIOMIXING_GAP_STATUS.md`](./BIOMIXING_GAP_STATUS.md)                                                                                   |
| 2        | Test & UAT một cửa                                           | [`BIOMIXING_UAT_AND_TEST_GUIDE.md`](./BIOMIXING_UAT_AND_TEST_GUIDE.md)                                                                   |
| 3        | Production vận hành                                          | [`../FUNC_LOGIC/PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md)                                             |
| 4        | Phase 1 PM                                                   | [`BIOMIXING_GAP_STATUS.md`](./BIOMIXING_GAP_STATUS.md) § Phase 1                                                                         |
| 5        | Demo / runbook                                               | [`BIOMIXING_FULL_DEMO_RUNBOOK.md`](./BIOMIXING_FULL_DEMO_RUNBOOK.md)                                                                                   |
| 6        | P0 hàng đợi                                                  | [`P0_BIOMIXING_NEXT_STEPS.md`](./P0_BIOMIXING_NEXT_STEPS.md)                                                                             |
| 7        | PM / diagram                                                 | [`../PROJECT BIOMIXING/README.md`](../PROJECT%20BIOMIXING/README.md)                                                                           |
| EN index | [`BIOMIXING_PREP_INDEX_EN.md`](./BIOMIXING_PREP_INDEX_EN.md) |

### Epic / debug nhanh

| Chủ đề                            | File                                                                                                                                                                                                   |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Post RM UOM                       | [`../FUNC_LOGIC/PRODUCTION_BUSINESS.md`](../FUNC_LOGIC/PRODUCTION_BUSINESS.md) §3 · [`../FUNC_BUG/BUG_PRODUCTION_UOM.md`](../FUNC_BUG/BUG_PRODUCTION_UOM.md) |
| Post FG → Inventory               | `PRODUCTION_BUSINESS.md` §3 · backfill `production:backfill-fg-inventory-ledger`                                                                                                             |
| Opening stock                     | [`13_OPENING_STOCK_VS_WAREHOUSE_STOCK.md`](./13_OPENING_STOCK_VS_WAREHOUSE_STOCK.md)                                                                                                             |
| UOM + giá (P2)                    | [`P2_PRODUCT_UOM_KIOTVIET_PLAN.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN.md)                                                                                                                           |
| Trace P↔W                         | [`P0_QA_BA_MASTER_TEST_CASE_TABLE.md`](./P0_QA_BA_MASTER_TEST_CASE_TABLE.md) — TC-P0-05 + phụ lục P0-05                                                                                           |
| Khái niệm RM/FG/BOM               | [`BIOMIXING_FLOW_CONCEPTS.md`](./BIOMIXING_FLOW_CONCEPTS.md)                                                                                                                                     |
| BOM → FG cost (kế hoạch)          | [`20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md`](./20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md)                                                                                                     |
| Form Product pricing / cost from BOM | [`20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md`](./20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md)                                                                                                  |

---

## 2. Ma trận doc ↔ chức năng (tóm)

| Chức năng                    | Code    | Doc                                     |
| ---------------------------- | ------- | --------------------------------------- |
| Phase 1 Estimate / SO gate   | ✅      | GAP_STATUS, PHASE1, UAT A               |
| Phase 2 BOM / lệnh / batch   | ✅      | GAP_STATUS, PRODUCTION_BUSINESS  |
| Post RM `convertToBase`      | ✅      | PRODUCTION_BUSINESS §3, FUNC_BUG |
| Post FG → Inventory P1c      | ✅      | PRODUCTION_BUSINESS §3           |
| Trace Warehouse ↔ Production | ✅ code | P0_QA_BA_MASTER_TEST_CASE_TABLE TC-P0-05 |
| CCP / COA / receiving QC     | ❌      | roadmap GAP_STATUS                      |

---

## 3. Lệnh kiểm tra sau đợt sync

```powershell
.\scripts\test.ps1 phase1

php artisan test --compact `
  tests/Feature/P0BiomixingAutomatedEvidenceTest.php `
  tests/Feature/BiomixingDemoRoutesReadinessTest.php `
  tests/Feature/ProductionPostingServiceTest.php `
  tests/Unit/ProductionFgInventoryLedgerSyncTest.php
```

Backfill (ops): `production:backfill-fg-inventory-ledger --dry-run`, `warehouse:backfill-opening-stock-to-default --dry-run`

---

## 4. Quy tắc maintainer

1. Đổi nghiệp vụ → **`BIOMIXING_BUSINESS_FLOW_LIVE.md`** (§ bước + changelog) + `BIOMIXING_GAP_STATUS.md`.
2. **Không** copy diagram từ `PROJECT BIOMIXING/` sang `FUNC_IMPROVE/` — chỉ link.
3. Plan/audit đã retire → [`LEGACY_ARCHIVE.md`](./LEGACY_ARCHIVE.md) · tra lịch sử: `git log -- <path>`.
