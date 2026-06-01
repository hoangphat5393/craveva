# FUNC\_\* Docs Index (đối chiếu với AI context)

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
    - `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`
- Sales → PO/DO/SO/Invoice (workflows):
    - `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
    - `FUNC_LOGIC/ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`
    - `FUNC_LOGIC/ERP_SO_PO_DO_INV_WH_QA_VI.md`
    - `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`
- Client/User schema:
    - `FUNC_LOGIC/FLOW_USERS_CLIENT.md`
    - `FUNC_LOGIC/SCHEMATIC_USERS_CLIENT_1_1_VI.md`
- Import mechanisms:
    - `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`
    - `FUNC_LOGIC/PRODUCT_IMPORT_SLOWNESS_ANALYSIS.md`

## 3) FUNC_BUG (bug notes)

- **`FUNC_BUG/REGISTRY.md`** — bảng tra cứu
- `FUNC_BUG/CLIENT_IMPORT_VI.md`, `FUNC_BUG/PRODUCT_IMPORT_VI.md`
- **Staging:** `FUNC_BUG/STAGING_QUICK_REF_VI.md` → `docs/SERVER_RUNBOOK_VI.md`

## 4) FUNC_IMPORT / FUNC_IMPROVE / FUNC_REPORT / docs / SPECIFICATION / LOG_REPORT

- `FUNC_IMPORT/**` — đặc tả import (sau gộp 2026-05: `IMPORT_SPECS_VI.md`, …)
- `FUNC_IMPROVE/**` — **living:** `BIOMIXING_GAP_STATUS_VI.md`, `BIOMIXING_BUSINESS_FLOW_LIVE_VI.md`, `BIOMIXING_DOC_HUB_VI.md`; **retired:** `LEGACY_ARCHIVE.md`
- `FUNC_REPORT/**` — báo cáo/tài liệu ảnh/chụp màn hình phục vụ PM/QA
- `docs/**` — runbook staging/hub; **audit:** `docs/DOCUMENTATION_AUDIT_DOCS_2026_05_VI.md`
- **`SPECIFICATION/`** — spec EN, audit luồng signup/menu, snapshot SSH + GCP/SQL gộp: `SPECIFICATION/INDEX.md`, `SPECIFICATION/GCP_AND_CLOUDSQL_SNAPSHOT_VI.md`, **audit:** `SPECIFICATION/DOCUMENTATION_AUDIT_SPECIFICATION_2026_05_VI.md`
- **`LOG_REPORT/`** — snapshot đếm dòng PHP backend: `LOG_REPORT/INDEX.md`, **audit:** `LOG_REPORT/DOCUMENTATION_AUDIT_LOG_REPORT_2026_05_VI.md`

## 5) Mapping sang ai-context (gợi ý)

- Workflow sale→delivery: `ai-context/workflows/sales_to_delivery.md` + các doc `FUNC_LOGIC/ERP_SO_PO_DO_*`
- Inventory transaction: `ai-context/workflows/inventory_transaction.md` + `FUNC_LOGIC/WAREHOUSE_*`
- Payment flow: `ai-context/workflows/payment_flow.md` + `FUNC_LOGIC/*invoice*`, `FUNC_LOGIC/*payment*` (nếu có)
