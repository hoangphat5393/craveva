# Biomixing — Hướng dẫn test & UAT (một cửa)

**Mục đích:** Tập hợp **đường dẫn tài liệu** và **lệnh kiểm thử tự động** để QA/BA/Dev chạy pilot Biomixing mà không phải mò từng thư mục.

**Phạm vi thật trong repo (2026-05):** Đã có **Production MVP** (BOM, lệnh, batch, RM/FG, policy, rework), **Warehouse** (batch, đối soát), **Sales/Estimate Phase 1** (duyệt nội bộ, convert SO) theo các playbook dưới đây. Các mục **Phase 3** (CCP/HACCP đầy đủ, QC lab…) vẫn theo `BIOMIXING_GAP_STATUS_VI.md` — **chưa** coi là đã triển khai hết trong code.

**Chỉ mục tài liệu tổng (tiếng Anh):** `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`

**Doc hub Biomixing:** `FUNC_IMPROVE/BIOMIXING_DOC_HUB_VI.md`

**Cài đặt & pilot trên máy local (Herd/Valet/Docker, migrate, module):** `FUNC_IMPROVE/BIOMIXING_LOCAL_DEV_SETUP_VI.md`

---

## 1) Kiểm tra nhanh không cần UI (regression / smoke)

Chạy từ thư mục gốc repo:

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php
```

Gói Production + Warehouse (khuyến nghị trước demo / sau deploy):

```bash
php artisan test --compact tests/Feature/BiomixingDemoRoutesReadinessTest.php tests/Feature/P0BiomixingAutomatedEvidenceTest.php tests/Feature/ProductionPostingServiceTest.php tests/Feature/ProductionFgQuantityPolicyServiceTest.php tests/Feature/ProductionVarianceApprovalPermissionTest.php tests/Unit/ProductionFgInventoryLedgerSyncTest.php tests/Feature/WarehouseReconciliationServiceInventorySnapshotTest.php tests/Feature/WarehouseProductBatchRoutesTest.php
```

Sau deploy — đồng bộ dữ liệu cũ (ops):

```bash
php artisan production:backfill-fg-inventory-ledger --dry-run
php artisan warehouse:backfill-opening-stock-to-default --dry-run
```

Luồng tenant Production (HTTP + DB thật, có thể skip nếu DB thiếu dữ liệu):

```bash
php artisan test --compact tests/Feature/ProductionBomAndOrderTenantFlowTest.php
```

Trạng thái module Production: `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md` · vận hành: `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md`

---

## 2) UAT / pilot theo bảng P0 Biomixing

**Hang doi buoc tiep theo (thu tu lam):** `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`

| Việc                                                     | Tài liệu                                                                                       |
| -------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| **Bảng test case QA/BA (một lượt)**                      | `FUNC_IMPROVE/P0_QA_BA_MASTER_TEST_CASE_TABLE_VI.md` — gom TC P0-01,02,03,05,06,08 + WUP-01…07 |
| Theo dõi task P0, trạng thái, DoD                        | `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`                                                   |
| Thứ tự ưu tiên tuần                                      | `FUNC_IMPROVE/P0_BIOMIXING_NEXT_STEPS_VI.md`                                                   |
| **3 luồng gốc + Production:** A–D (+ **E** FG→Inventory) | `FUNC_IMPROVE/P0_MINI_UAT_CHECKLIST_BIOMIXING_VI.md` (**P0-08**)                               |
| Trace **Warehouse ↔ Production**                         | `FUNC_IMPROVE/P0_05_TRACE_BIDIRECTIONAL_UAT_CHECKLIST.md` (**P0-05**, VI+EN)                   |
| Duyệt lệch FG — ma trận quyền                            | `FUNC_IMPROVE/P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md` (**P0-02**)                              |
| Shadow Yield/UOM (governance, mặc định tắt pilot)        | `FUNC_IMPROVE/11_SHADOW_YIELD_UOM_PLANNED_ANALYSIS_VI.md` §8 (**P0-03**)                       |

---

## 3) Demo end-to-end trên Hub (Purchase → Kho → Production → Bán)

| Bước / nội dung                                         | File                                             |
| ------------------------------------------------------- | ------------------------------------------------ |
| Chuẩn bị môi trường, bật module, quyền, thứ tự màn hình | `FUNC_IMPROVE/BIOMIXING_FULL_DEMO_RUNBOOK_VI.md` |

Đọc khái niệm RM/FG, luồng tồn: `FUNC_IMPROVE/BIOMIXING_FLOW_CONCEPTS_VI.md`

---

## 4) Nâng cấp kho & bảng WUP (P0-07)

| Nội dung                                                      | File                                                                                                     |
| ------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| Runbook warehouse, chu kỳ test UI, checklist UAT sau nâng cấp | `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md`                                                               |
| **WUP + mẫu điền UAT (P0-07)**                                | `FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md` — **§2.1** (bảng evidence) và **§2.1.1** (mẫu bảng WUP-01…07) |

---

## 5) Phase 1 thương mại — Báo giá → duyệt → Sales Order

| Nội dung                | File                                                             |
| ----------------------- | ---------------------------------------------------------------- |
| Thao tác UI từng bước   | `PROJECT BIOMIXING/UI_RUNBOOK_PHASE1_QUOTATION_TO_SO_VI.md`      |
| Bối cảnh President / VP | `PROJECT BIOMIXING/PHASE_BUSINESS_CONTEXT_EXAMPLE.md` (§6.3–6.4) |

---

## 6) Nền tảng ERP (SO / PO / DO / Invoice / Kho) — không riêng Biomixing nhưng bắt buộc khi test E2E

| Chủ đề                                       | File                                                    |
| -------------------------------------------- | ------------------------------------------------------- |
| Trạng thái QA nền tảng                       | `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`               |
| Quy trình PO · DO · SO · Invoice · Warehouse | `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md` |
| Hub kho (mục lục)                            | `FUNC_LOGIC/WAREHOUSE_INDEX.md`                         |
| **UAT E2E mua / bán / kho**                  | `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`        |

---

## 7) Sơ đồ & kịch bản PM / demo (tham chiếu, không thay runbook kỹ thuật)

| Loại                       | Vị trí                                       |
| -------------------------- | -------------------------------------------- |
| Mermaid / HTML flow        | `PROJECT BIOMIXING/*.mmd`, `*.html`          |
| Demo script (storytelling) | `PROJECT BIOMIXING/BIOMIXING_DEMO_SCRIPT.md` |
| Mục lục thư mục            | `PROJECT BIOMIXING/README.md`                |

---

## 8) Kế hoạch phát triển còn lại (để biết “chưa làm gì”)

| File                                          | Dùng khi                                 |
| --------------------------------------------- | ---------------------------------------- |
| `FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`     | Trạng thái phase vs code                 |
| `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` | Production vận hành (reserve, lifecycle) |
| `FUNC_IMPROVE/BIOMIXING_PREP_INDEX_EN.md`     | Chỉ mục tài liệu triển khai              |

---

## 9) Epic doc (debug nhanh)

| Chủ đề               | File                                                                                           |
| -------------------- | ---------------------------------------------------------------------------------------------- |
| Post RM UOM          | `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` §2 · `FUNC_BUG/PRODUCTION_RM_OUTBOUND_UOM_VI.md` |
| Post FG → Inventory  | `FUNC_LOGIC/PRODUCTION_OPERATIONS_LIVE_VI.md` §2                                               |
| Opening stock vs kho | `FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK_VI.md`                                       |
| FG variance          | `P0_VARIANCE_APPROVAL_ROLE_MATRIX_VI.md`, `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md` §3.2            |

---

_Cập nhật: 2026-05-24 — doc sync manifest, audit phase, Luồng E, backfill commands, `ProductionFgInventoryLedgerSyncTest`._
