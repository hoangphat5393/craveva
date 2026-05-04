# FUNC_* Docs Index (đối chiếu với AI context)

- Generated at: 2026-05-04T05:35:06+00:00
- Mục tiêu: nối các tài liệu thủ công trong `FUNC_*` với cấu trúc `ai-context/**` để AI retrieval tốt hơn.

## 1) Entry points (khuyến nghị đọc)

- `ai-context/core/SYSTEM_OVERVIEW.md`
- `ai-context/core/MODULE_INDEX.md`
- `MASTER_DOCUMENTATION.md`

## 2) FUNC_LOGIC (logic/flow – theo nghiệp vụ)

- `FUNC_LOGIC/README.md` — mục lục theo chủ đề
- Warehouse / Purchase / Inventory:
  - `FUNC_LOGIC/WAREHOUSE_INDEX.md`
  - `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`
  - `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`
  - `FUNC_LOGIC/AUDIT_WAREHOUSE_MODULE_VI.md`
  - `FUNC_LOGIC/AUDIT_PURCHASE_MODULE_VI.md`
  - `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`
- Sales → PO/DO/SO/Invoice (workflows):
  - `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
  - `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md`
  - `FUNC_LOGIC/ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`
  - `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`
- Client/User schema:
  - `FUNC_LOGIC/FLOW_USERS_CLIENT.md`
  - `FUNC_LOGIC/SCHEMATIC_LAYER_USERS_CLIENT_DETAILS_1_1_REASON_AND_FIX.md`
- Import mechanisms:
  - `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`
  - `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`

## 3) FUNC_BUG (bug notes)

- `FUNC_BUG/SOCIAL_AUTH_SETTINGS_MAC_INVALID_FIX.md` — lỗi decrypt secrets trên settings
- `FUNC_BUG/CLIENT_IMPORT_MASTER.md` — gộp lỗi import client (data + staging)
- `FUNC_BUG/PRODUCT_IMPORT_MASTER.md` — gộp product import (mapping/custom field/performance)
- `FUNC_BUG/FULL_TEST_SUITE_FAILURES_SNAPSHOT.md` — snapshot test failures
- Nhóm staging/runbook: `FUNC_BUG/STAGING_*` (dùng để vận hành/incident, không phải spec sản phẩm)

## 4) FUNC_IMPORT / FUNC_IMPROVE / FUNC_REPORT

- `FUNC_IMPORT/**` — hướng dẫn/prompt triển khai import theo domain
- `FUNC_IMPROVE/**` — plan cải tiến (một số file ghi rõ “archived analysis”)
- `FUNC_REPORT/**` — báo cáo/tài liệu ảnh/chụp màn hình phục vụ PM/QA

## 5) Mapping sang ai-context (gợi ý)

- Workflow sale→delivery: `ai-context/workflows/sales_to_delivery.md` + các doc `FUNC_LOGIC/ERP_SO_PO_DO_*`
- Inventory transaction: `ai-context/workflows/inventory_transaction.md` + `FUNC_LOGIC/WAREHOUSE_*`
- Payment flow: `ai-context/workflows/payment_flow.md` + `FUNC_LOGIC/*invoice*`, `FUNC_LOGIC/*payment*` (nếu có)
