# Biomixing — Documentation Sync (2026-05-24)

**Mục đích:** Một cửa cho **đồng bộ tài liệu** chức năng Biomixing sau các đợt vá (UOM outbound, opening stock P1, FG→Inventory P1c, audit quy trình).  
**Nguồn sự thật triển khai:** code + tests + [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md) + [`BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`](./BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md).

---

## 1. Living documentation map (đọc theo thứ tự)

| #   | Vai trò                                                   | File                                                                                                                               |
| --- | --------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| 0   | **Luồng nghiệp vụ chuẩn (LIVE — SSOT)**                   | [`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`](./BIOMIXING_BUSINESS_FLOW_LIVE_VI.md)                                                       |
| 1   | **Trạng thái code vs PM (cập nhật thường xuyên)**         | [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)                                                                       |
| 2   | **Audit toàn quy trình theo phase + go-live**             | [`BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`](./BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md)                                       |
| 3   | **Test & UAT một cửa**                                    | [`BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`](./BIOMIXING_UAT_AND_TEST_GUIDE_VI.md)                                                       |
| 4   | **Audit 3 thư mục (FUNC_IMPROVE / FUNC_LOGIC / PROJECT)** | [`DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`](./DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md)                               |
| 5   | Phase 1 PM                                                | [`PHASE1_PM_STATUS_LIVE_VI.md`](./PHASE1_PM_STATUS_LIVE_VI.md)                                                                     |
| 6   | Phase 2 PM                                                | [`PHASE2_PM_PLAN_VI.md`](./PHASE2_PM_PLAN_VI.md)                                                                                   |
| 7   | Playbook dev P0–P1                                        | [`BIOMIXING_PLAYBOOK_P0P1_VI.md`](./BIOMIXING_PLAYBOOK_P0P1_VI.md)                                                                 |
| 8   | Demo Hub                                                  | [`BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`](./BIOMIXING_FULL_DEMO_RUNBOOK_VI.md)                                                         |
| 9   | P0 task + DoD                                             | [`P0_EXECUTION_LOG.md`](./P0_EXECUTION_LOG.md), [`P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`](./P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md) |
| 10  | PM / diagram (không copy vào FUNC_IMPROVE)                | [`PROJECT BIOMIXING/README.md`](../PROJECT%20BIOMIXING/README.md)                                                                  |

### Epic / gap chuyên đề (khi demo hoặc debug)

| Chủ đề                     | File                                                                                                                                                 |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| Post RM UOM (g→kg)         | [`15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md`](./15_PRODUCTION_OUTBOUND_UOM_GAP_VI.md)                                                                     |
| Post FG → Inventory list   | [`16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md`](./16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md)                                                     |
| Opening stock vs warehouse | [`13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`](./13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md)                                                           |
| Đa đơn vị + giá (KiotViet) | [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md)                                                                         |
| FG policy / variance       | [`01_PROD_BOM_FG_POLICY_VI.md`](./01_PROD_BOM_FG_POLICY_VI.md), [`P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`](./P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md) |
| UX backlog                 | [`10_UX_UI_IMPROVEMENT_BACKLOG.md`](./10_UX_UI_IMPROVEMENT_BACKLOG.md)                                                                               |

---

## 2. Ma trận chức năng Biomixing — trạng thái doc (2026-05-24)

| Chức năng                                  | Code    | Doc đã sync                      | UAT / ghi chú                                 |
| ------------------------------------------ | ------- | -------------------------------- | --------------------------------------------- |
| Phase 1: BOM báo giá, duyệt 2 cấp, SO gate | ✅      | ✅ GAP_STATUS, PHASE1, UAT A     | P0-08-A chưa ký                               |
| Phase 2: BOM, lệnh, release snapshot       | ✅      | ✅ PLAYBOOK, PHASE2              | —                                             |
| Tổng NL + shortfall + link PO              | ✅      | ✅ GAP_STATUS P0-3, P1-2         | —                                             |
| SO → lệnh SX (prefill)                     | ✅      | ✅ P1-1, P1-4                    | —                                             |
| Batch 5 bước, post RM/FG                   | ✅      | ✅ P0-4, PLAYBOOK                | Luồng D code ✅                               |
| Post RM `convertToBase`                    | ✅      | ✅ `15_*`                        | Luồng D UAT                                   |
| Post FG → `warehouse_product_batches`      | ✅      | ✅ PLAYBOOK, FLOW_CONCEPTS       | Trace / Stock batches                         |
| **Post FG → Inventory list (P1c)**         | ✅      | ✅ **`16_*`**, GAP_STATUS, audit | Backfill command; Luồng E UAT                 |
| Opening stock → kho mặc định (P1)          | ✅      | ✅ `13_*`                        | `warehouse:backfill-opening-stock-to-default` |
| FG variance policy + approve               | ✅      | ✅ `01_*`, P0-02                 | UX badge Pending (UX-008)                     |
| Trace P↔W                                  | ✅ code | ✅ P0-05 checklist               | UAT screenshot                                |
| P2 UOM A/B/C + SKU auto                    | ✅      | ✅ `P2_*`, audit §3              | UAT Oldtown                                   |
| CCP / receiving QC / COA                   | ❌      | ✅ DEV_PLAN Phase 3+             | Roadmap                                       |
| Inventory list SSOT = warehouse only       | ❌      | ✅ `06_INVENTORY` backlog        | Chưa implement                                |

---

## 3. Lệnh kiểm tra doc-to-code (sau mỗi đợt sync)

```powershell
# Phase 1 Estimate
.\scripts\test.ps1 phase1

# Biomixing P0 smoke + Production core
php artisan test --compact `
  tests/Feature/P0BiomixingAutomatedEvidenceTest.php `
  tests/Feature/BiomixingDemoRoutesReadinessTest.php `
  tests/Feature/ProductionVarianceApprovalPermissionTest.php `
  tests/Feature/ProductionPostingServiceTest.php `
  tests/Feature/ProductionFgQuantityPolicyServiceTest.php `
  tests/Unit/ProductionFgInventoryLedgerSyncTest.php `
  tests/Feature/WarehouseProductBatchRoutesTest.php

# Backfill (ops, sau deploy)
php artisan production:backfill-fg-inventory-ledger --dry-run
php artisan warehouse:backfill-opening-stock-to-default --dry-run
```

---

## 4. Quy tắc sync (maintainer)

1. **Một thay đổi code lớn** → cập nhật **`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`** (§ bước + §9 changelog) + `BIOMIXING_GAP_STATUS_VI.md` + epic doc (`15_*`, `16_*`, …) + mục changelog §5 file này.
2. **Audit định kỳ** → cập nhật `BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md` + `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`.
3. **Không nhân bản diagram** từ `PROJECT BIOMIXING/` sang `FUNC_IMPROVE/` — chỉ link.
4. **Tài liệu cũ 2026-02–04** (`BIOMIXING_GAP_ANALYSIS`, `FLOW_CRACEVA_GAP`) — đọc kèm banner baseline; không ghi đè bằng nhận định mới.

---

## 5. Changelog sync

### 2026-05-24 — LIVE business flow + UX-008

| File | Nội dung |
| ---- | -------- |
| **`BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`** | SSOT luồng E2E + mermaid + §9 changelog |
| `.cursor/rules/biomixing-business-flow-live.mdc` | Rule cập nhật LIVE khi đổi nghiệp vụ |
| UX-008 | `outputVarianceApprovalUiState` + UI 3 trạng thái |

### 2026-05-24 — Full process audit + documentation sync

| File                                                  | Nội dung                             |
| ----------------------------------------------------- | ------------------------------------ |
| **`BIOMIXING_DOCUMENTATION_SYNC_2026_05_VI.md`**      | **Mới** — file này                   |
| **`BIOMIXING_FULL_PROCESS_AUDIT_2026_05_VI.md`**      | Audit phase 1–2, P0, go-live         |
| `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`      | §1, living docs, §3.5 P1c, changelog |
| `BIOMIXING_GAP_STATUS_VI.md`                          | P1c, link audit                      |
| `BIOMIXING_PREP_INDEX_EN.md`, `FUNC_IMPROVE/INDEX.md` | Link sync + audit                    |
| `PROJECT BIOMIXING/README.md`                         | Living docs: 16, audit, P1c          |
| `BIOMIXING_UAT_AND_TEST_GUIDE_VI.md`                  | Test bundle + Luồng E + backfill     |
| `BIOMIXING_FULL_DEMO_RUNBOOK_VI.md`                   | Post FG → Inventory, backfill        |
| `PHASE2_PM_PLAN_VI.md`                                | P1c, DoD, % MVP, tài liệu            |
| `P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md`               | Luồng E (FG → Inventory)             |
| `P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md`               | Link audit, TC-P0-08-E               |
| `PROJECT BIOMIXING/PHASE1_2_BUSINESS_FLOW_PM_VI.md`   | % Phase 2, link FUNC_IMPROVE         |
| `10_UX_UI_IMPROVEMENT_BACKLOG.md`                     | UX-008 variance badge                |
| `P0_EXECUTION_LOG.md`                                 | Link audit                           |
| `06_INVENTORY_BUSINESS_IMPROVE.md`                    | P1c cross-ref (đã có)                |
| `01_PROD_BOM_FG_POLICY_VI.md`                         | P1c done (đã có)                     |

### 2026-05-23 — P1c FG → Inventory ledger

| File                                           | Nội dung              |
| ---------------------------------------------- | --------------------- |
| `16_PRODUCTION_FG_INVENTORY_LEDGER_SYNC_VI.md` | Living doc + backfill |
| `13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`    | P1c section           |
| `ProductionFgInventoryLedgerSync.php` + tests  | Code                  |

### 2026-05-20 — UOM outbound + P0 Production UX

Xem `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md` §8 changelog 2026-05-20.

---

_Maintainer: sau mỗi sprint Biomixing, cập nhật §2 + §5 và chạy §3._
