# Documentation audit — `PROJECT BIOMIXING` · `FUNC_LOGIC` · `FUNC_IMPROVE`

**Cập nhật:** 2026-05-21  
**Phạm vi:** Documentation audit · sync · spec reconciliation · doc-to-code validation  
**Nguồn sự thật triển khai:** code (`app/`, `Modules/`), tests, [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md)

---

## 1. Executive summary

| Khía cạnh                                | Kết luận                                                                                                                                                                                                                                                                                                                                                     |
| ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Cấu trúc 3 thư mục**                   | Đúng: PM/diagram → `PROJECT BIOMIXING/`; logic kho/SO/PO → `FUNC_LOGIC/`; plan & gap → `FUNC_IMPROVE/`.                                                                                                                                                                                                                                                      |
| **Living docs (đọc trước khi demo/UAT)** | [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md), [`PHASE1_PM_STATUS_LIVE_VI.md`](./PHASE1_PM_STATUS_LIVE_VI.md), [`PHASE2_PM_PLAN_VI.md`](./PHASE2_PM_PLAN_VI.md), [`P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](./P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md), [`FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md`](../FUNC_LOGIC/SALES_FULFILLMENT_DOCS_INDEX.md). |
| **Doc-to-code (2026-05-20 → 21)**        | **P2-UOM** và **SKU tự động** đã có trong code; plan P2 §13 DoD chưa tick — **đã cập nhật** trong lần sync này.                                                                                                                                                                                                                                              |
| **Technical debt (docs)**                | Nhiều file `BIOMIXING_*` / audit 2026-02–04 mô tả kho «Partial» — vẫn giữ lịch sử; đọc kèm baseline 2026. Một số `FLOW_*` chưa nhắc Purchase Products + UOM.                                                                                                                                                                                                 |

---

## 2. Vai trò từng thư mục (living documentation map)

```text
PROJECT BIOMIXING/     → PM, proposal, diagram (.mmd/.html), demo script, RTF gốc
FUNC_IMPROVE/          → Gap status, phase plan, playbook, P0/P2 epic, audit
FUNC_LOGIC/            → Flow kỹ thuật, quy trình SO/PO/GRN/kho, glossary, audit logic
```

| Cần biết…                     | Mở file                                                                                                       |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------- |
| Yêu cầu PM Gary (gốc)         | `PROJECT BIOMIXING/PM_YEU_CAU_TONG_HOP_VI.md`                                                                 |
| Phase 1 đủ chưa / UAT báo giá | `FUNC_IMPROVE/PHASE1_PM_STATUS_LIVE_VI.md`                                                                    |
| Phase 2 / lệnh SX / BOM       | `FUNC_IMPROVE/PHASE2_PM_PLAN_VI.md`, `PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_*.md`                               |
| Trạng thái code vs proposal   | **`FUNC_IMPROVE/BIOMIXING_GAP_STATUS_VI.md`**                                                                 |
| Đa đơn vị + giá (KiotViet)    | `FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`                                                             |
| SO → PO → GRN → kho           | `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`, `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`    |
| Thêm sản phẩm / import        | `FUNC_LOGIC/FLOW_ADD_PRODUCT.md` (đã sync SKU auto)                                                           |
| LanguagePack / dịch           | `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md`, `FUNC_LOGIC/GLOSSARY_PURCHASE_ERP_VI.json` |

---

## 3. Doc-to-code validation (2026-05-21)

### 3.1 P2-UOM (đa đơn vị + UOM price)

| Hạng mục plan                                | Code / test                                                                                                                                       | Trạng thái doc |
| -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | -------------- |
| A — `product_unit_conversions` + UI SP       | Migration `2026_05_20_180000_*`, `ProductUnitConversionSyncService`, blades UOM                                                                   | ✅ Implemented |
| B — SO, Estimate, Invoice, PO line UOM + giá | `DocumentLineUnitPricing`, `ProductSellableUnitsService`, partials `item-unit-select`                                                             | ✅ Implemented |
| C — BOM hint quy đổi; NL SX base             | `ProductUnitQuantityHintService`, `ProductionOrderMaterialRequirementsSummary` + `convertToBase`                                                  | ✅ Implemented |
| Tests                                        | `ProductUnitConversionSyncTest`, `OrderProductUnitPriceTest`, `WarehouseUnitConversionFlowTest`, `ProductionOrderMaterialRequirementsSummaryTest` | ✅             |

**Ghi chú:** GRN line UOM vẫn theo luồng PO; strict conversion → `warehouse::app.flow_strict_unit_conversion_hint` (settings).

### 3.2 SKU tự động (Purchase Products)

| Hạng mục                                        | Code                                                                     | Trạng thái doc                                                                |
| ----------------------------------------------- | ------------------------------------------------------------------------ | ----------------------------------------------------------------------------- |
| Sinh SKU khi tạo (trống / placeholder)          | `ProductSkuGenerator`, `ResolvesProductSku`                              | ✅ Sync 2026-05-21                                                            |
| Format `{PREFIX}-{TYPE}-{SEQ}` per `company_id` | `product_sku_sequences` migration                                        | ✅                                                                            |
| Placeholder EN «Auto-generated» / VI «Tự động»  | `purchase::app.skuAutoGeneratedPlaceholder` (LanguagePack + module lang) | ✅                                                                            |
| Unique SKU per company (validation)             | `Rule::unique` trong Store/Update Purchase product requests              | ✅ (app-level; DB unique index chưa thêm — tránh fail migrate nếu data trùng) |

### 3.3 Phase 1 / Phase 2 (không đổi trong đợt này)

Đối chiếu đầy đủ vẫn tại [`BIOMIXING_GAP_STATUS_VI.md`](./BIOMIXING_GAP_STATUS_VI.md) — **khớp code** cho P0-3…P1-4, PDF BOM, SO→Production order.

---

## 4. Spec reconciliation — lệch giữa PM / plan / code

| Chủ đề                    | PM / `PROJECT BIOMIXING`      | Plan `FUNC_IMPROVE`               | Code thực tế                                  | Hành động                                   |
| ------------------------- | ----------------------------- | --------------------------------- | --------------------------------------------- | ------------------------------------------- |
| Kho batch / GRN canonical | Proposal Phase 3              | `BIOMIXING_BASELINE_PREP_2026_VI` | `ERP_SO_PO_DO_INV_WH_QA_VI`                   | Đọc baseline + QA VI, không đọc gap 2026-02 |
| UOM price trên SP         | PM chat / screenshot KiotViet | `P2_PRODUCT_UOM_*`                | Đã triển khai A–C                             | Cập nhật gap status → UAT                   |
| SKU global vs per company | Chưa nêu trong PM gốc         | Thảo luận 2026-05-21              | **Per `company_id`**                          | Ghi trong `FLOW_ADD_PRODUCT`                |
| Shadow yield UOM          | `11_SHADOW_YIELD_*`           | Governance P0                     | Flag `yield_uom_shadow_enabled` default false | Không bật production cho đến khi PM ký      |

---

## 5. Knowledge base cleanup (đề xuất ưu tiên)

| Ưu tiên | File / nhóm                                                                                | Đề xuất                                                |
| ------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------ |
| P0      | `BIOMIXING_GAP_STATUS_VI.md`, `PHASE2_PM_PLAN_VI.md`, `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` | Giữ — cập nhật mỗi sprint                              |
| P1      | `FUNC_LOGIC/FLOW_ADD_PRODUCT.md`, `GLOSSARY_PURCHASE_ERP_VI.json`                          | Đã sync SKU/UOM                                        |
| P2      | `BIOMIXING_GAP_ANALYSIS.md`, `BIOMIXING_FLOW_CRACEVA_GAP.md`                               | Giữ + banner «đọc kèm GAP_STATUS 2026-05»              |
| P3      | `BIOMIXING_PROPOSAL_REVISED.md`, timeline EN đã xóa                                        | Chỉ sales narrative                                    |
| Archive | `CURSOR_AND_GIT_ACTIVITY_REPORT_* - bk.md`, `purchase_lang_audit_report.csv`               | Di chuyển `archive/` hoặc ghi «historical» trong INDEX |

Chi tiết file `BIOMIXING_*` lỗi thời: [`BIOMIXING_DOC_AUDIT_2026_VI.md`](./BIOMIXING_DOC_AUDIT_2026_VI.md).

---

## 6. Technical debt — documentation only

1. **Định kỳ:** Sau mỗi epic (UOM, SKU, Production…), cập nhật `BIOMIXING_GAP_STATUS_VI.md` + §Implementation trong plan epic.
2. **Một checklist UAT:** `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md` + mini UAT P2 trong `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md` §11.
3. **Tránh nhân bản:** Diagram chỉ ở `PROJECT BIOMIXING/`; không copy nguyên văn vào `FUNC_IMPROVE/`.
4. **LanguagePack:** Key mới chỉ thêm qua `Modules/LanguagePack/...` rồi publish — đã nêu trong `FLOW_Modules_Package_LanguagePack_*`.

---

## 7. Lệnh kiểm tra nhanh (dev / QA doc)

```powershell
php artisan test --compact tests/Feature/ProductSkuGeneratorTest.php tests/Feature/ProductUnitConversionSyncTest.php tests/Feature/OrderProductUnitPriceTest.php
php artisan migrate --no-interaction
php artisan languagepack:publish-translation --no-interaction
.\scripts\test.ps1 phase1
```

---

## 8. Changelog sync (2026-05-21)

| File đã cập nhật                                                       | Nội dung                        |
| ---------------------------------------------------------------------- | ------------------------------- |
| `DOCUMENTATION_AUDIT_CROSS_FOLDER_2026_05_VI.md`                       | **Mới** — báo cáo này           |
| `BIOMIXING_GAP_STATUS_VI.md`                                           | P2-UOM done; SKU auto           |
| `P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`                                   | §16 trạng thái triển khai + DoD |
| `PHASE2_PM_PLAN_VI.md`                                                 | P2-1 / Sprint D                 |
| `FUNC_LOGIC/FLOW_ADD_PRODUCT.md`                                       | Purchase path, SKU, UOM         |
| `FUNC_LOGIC/GLOSSARY_PURCHASE_ERP_VI.json`                             | SKU placeholder keys            |
| `PROJECT BIOMIXING/README.md`                                          | Hub living docs                 |
| `FUNC_IMPROVE/INDEX.md`, `FUNC_LOGIC/README.md`, `FUNC_LOGIC/INDEX.md` | Link audit                      |
| `BIOMIXING_DOC_AUDIT_2026_VI.md`                                       | Trỏ audit cross-folder          |

---

_Maintainer: cập nhật file này + `BIOMIXING_GAP_STATUS_VI.md` sau mỗi đợt release chức năng lớn._
