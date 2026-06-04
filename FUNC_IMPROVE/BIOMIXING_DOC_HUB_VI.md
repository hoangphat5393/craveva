# Biomixing — Doc hub (đọc trước / đồng bộ tài liệu)

**Cập nhật:** 2026-05-27 (pass 6 — bỏ audit/legacy đã xong)  
**Nguồn sự thật triển khai:** code + tests + [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)

---

## 1. Living docs — đọc theo thứ tự

| #        | Vai trò                                                      | File                                                                                                                                           |
| -------- | ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| 0        | **Luồng nghiệp vụ (SSOT)**                                   | [`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](./BIOMIXING_BUSINESS_FLOW_LIVE_VI.md)                                                                   |
| 1        | Trạng thái code vs PM                                        | [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)                                                                                   |
| 2        | Test & UAT một cửa                                           | [`BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](./BIOMIXING_UAT_AND_TEST_GUIDE_VI.md)                                                                   |
| 3        | Production vận hành                                          | [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md)                                             |
| 4        | Phase 1 PM                                                   | [`PHASE1_PM_STATUS_LIVE_VI.md`](./PHASE1_PM_STATUS_LIVE_VI.md)                                                                                 |
| 5        | Playbook / demo                                              | [`BIOMIXING_PLAYBOOK_P0P1_VI.md`](./BIOMIXING_PLAYBOOK_P0P1_VI.md), [`BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`](./BIOMIXING_FULL_DEMO_RUNBOOK_VI.md) |
| 6        | P0 hàng đợi                                                  | [`P0_BIOMIXING_NEXT_STEPS_VI.md`](./P0_BIOMIXING_NEXT_STEPS_VI.md)                                                                             |
| 7        | PM / diagram                                                 | [`../PROJECT BIOMIXING/README.md`](../PROJECT%20BIOMIXING/README.md)                                                                           |
| EN index | [`BIOMIXING_PREP_INDEX_EN.md`](./BIOMIXING_PREP_INDEX_EN.md) |

### Epic / debug nhanh

| Chủ đề                            | File                                                                                                                                                                                                   |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Post RM UOM                       | [`../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`](../FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md) §2 · [`../FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md`](../FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md) |
| Post FG → Inventory               | `PRODUCTION_OPERATIONS_LIVE_VI.md` §2 · backfill `production:backfill-fg-inventory-ledger`                                                                                                             |
| Opening stock                     | [`13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`](./13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md)                                                                                                             |
| UOM + giá (P2)                    | [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md)                                                                                                                           |
| Trace P↔W                         | [`P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md`](./P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md)                                                                                                           |
| Khái niệm RM/FG/BOM               | [`BIOMIXING_FLOW_CONCEPTS_VI.md`](./BIOMIXING_FLOW_CONCEPTS_VI.md)                                                                                                                                     |
| BOM → FG cost (kế hoạch)          | [`20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md`](./20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN_VI.md)                                                                                                     |
| Form Product pricing (hiện trạng) | [`21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md`](./21_PRODUCT_FORM_PRICING_CURRENT_STATE_VI.md)                                                                                                         |

---

## 2. Ma trận doc ↔ chức năng (tóm)

| Chức năng                    | Code    | Doc                                     |
| ---------------------------- | ------- | --------------------------------------- |
| Phase 1 Estimate / SO gate   | ✅      | GAP_STATUS, PHASE1, UAT A               |
| Phase 2 BOM / lệnh / batch   | ✅      | PLAYBOOK, PRODUCTION_OPERATIONS_LIVE    |
| Post RM `convertToBase`      | ✅      | PRODUCTION_OPERATIONS_LIVE §2, FUNC_BUG |
| Post FG → Inventory P1c      | ✅      | PRODUCTION_OPERATIONS_LIVE §2           |
| Trace Warehouse ↔ Production | ✅ code | P0-05 checklist                         |
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

1. Đổi nghiệp vụ → **`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`** (§ bước + changelog) + `BIOMIXING_GAP_STATUS_VI.md`.
2. **Không** copy diagram từ `PROJECT BIOMIXING/` sang `FUNC_IMPROVE/` — chỉ link.
3. Plan/audit đã retire → [`LEGACY_ARCHIVE.md`](./LEGACY_ARCHIVE.md) · tra lịch sử: `git log -- <path>`.
