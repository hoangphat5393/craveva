# Warehouse — Mục lục tài liệu (điểm vào)

**Cập nhật:** 2026-05-09  
**Mục đích:** Ít file hơn — bắt đầu từ bảng dưới.

---

## Đọc theo nhu cầu

| Bạn cần…                                                                        | File                                                                                                                                                                                                                                                                                                   |
| ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Rà migration: trùng tên file, bảng Sales DO/GRN**                             | `php database/scripts/audit_migrations_registry.php` · test `MigrationRegistryAuditTest`                                                                                                                                                                                                               |
| **Phân biệt `*.test` (local Herd) vs server thật**                              | **[`ENV_LOCAL_VS_SERVER_HOSTNAMES_VI.md`](ENV_LOCAL_VS_SERVER_HOSTNAMES_VI.md)**                                                                                                                                                                                                                       |
| **Master: luồng bán, schema, bảng legacy đã gỡ (3 env), audit gộp**             | **[`ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md`](ERP_SO_PO_DO_GRN_SCHEMA_MATRIX_VI.md)**                                                                                                                                                                                                                     |
| **Chỉ cần nắm cơ bản** (+ **đa kho** tóm tắt §3): SO→DO→kho→invoice; PO→GRN→kho | **[`HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md`](HUONG_DAN_KHO_BAN_CO_BAN_VA_PHAN_MO_RONG_VI.md)**                                                                                                                                                                                                 |
| **Quy trình PO / DO / SO / Invoice / Kho (một chỗ)**                            | **[`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)**                                                                                                                                                                                                         |
| **Audit E2E / QA hiện tại**                                                     | [`ERP_SO_PO_DO_INV_WH_QA_VI.md`](ERP_SO_PO_DO_INV_WH_QA_VI.md)                                                                                                                                                                                                                                         |
| **Audit riêng Sales DO** (remaining, confirm, đổi kho)                          | [`AUDIT_SALES_DO_FUNCTIONAL_VI.md`](AUDIT_SALES_DO_FUNCTIONAL_VI.md)                                                                                                                                                                                                                                   |
| Luồng chi tiết **chỉ module kho** (điều chỉnh, chuyển, ledger…)                 | [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)                                                                                                                                                                                                                               |
| **Tồn theo lô (batch) — UI, trace Production, đối soát**                        | Routes `warehouse.product-batches.*`, nhãn menu/i18n `warehouse::app.stockBatches` (EN **Stock batches**); widget **Stock** (snapshot vs batch, WUP-07); chi tiết + test trong [`04_WH_RUNBOOK_UPGRADE_VI.md`](../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md) §6 · `WarehouseProductBatchRoutesTest.php` |
| **Runbook vận hành + kế hoạch nâng cấp (WUP)**                                  | [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md)                                                                                                                                                                                                              |
| **Biến `.env` / kho + PO·GRN·Sales DO·webhook AI**                              | [`WH_PURCHASE_ENV_REFERENCE_VI.md`](WH_PURCHASE_ENV_REFERENCE_VI.md)                                                                                                                                                                                                                                   |
| Kiến trúc, DB, URL, permission                                                  | [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)                                                                                                                                                                                                                                               |
| Audit **code** (route web/API, config, rủi ro API)                              | [`AUDIT_WAREHOUSE_MODULE_VI.md`](AUDIT_WAREHOUSE_MODULE_VI.md)                                                                                                                                                                                                                                         |
| Trạng thái code, Scope A/B, **audit trước upgrade**, **prompt Cursor**          | [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md) §10–11                                                                                                                                                                                                                                    |
| Câu hỏi PM / gap nghiệp vụ                                                      | [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md) · [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)                                                                                                                                              |
| **Checklist UAT E2E (Mua · Bán · Kho)**                                         | **[`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)**                                                                                                                                                                                                                       |
| Luồng code SO/Invoice/PO (English, deep)                                        | [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md)                                                                                                                                                                                                                                                     |
| Audit đa kho (lịch sử + note Scope B)                                           | [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md)                                                                                                                                                                                                                                   |
| Refactor **Sales DO / GRN** (quyết định + kế hoạch + tracker)                   | [`SO_DO_PO_GRN_REFACTOR_VI.md`](../FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md)                                                                                                                                                                                                                        |
| Demo nhanh inventory trigger (PO/GRN/Bill, SO/DO/Invoice)                       | [`../FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md`](../FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md) (mục **Phụ lục A**)                                                                                                                                                                                           |
| Định hướng nghiệp vụ inventory + batch                                          | [`../FUNC_IMPROVE/06_INVENTORY_BUSINESS_IMPROVE.md`](../FUNC_IMPROVE/06_INVENTORY_BUSINESS_IMPROVE.md)                                                                                                                                                                                                 |

**Menu Operations (Warehouse):** Warehouses, Adjust stock, Transfer stock, Stock movements — chi tiết & chỗ vào Import / Cài đặt luồng kho: [`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md) **Phụ lục C** · **Kế hoạch giai đoạn UAT:** **§6**.

**Legacy docs đã loại bỏ khỏi repo:** dùng trực tiếp các file canonical ở bảng trên.

**Legacy theo chu de inventory:** gom theo 2 file canonical:

- Tracker/kết quả test staging: `../FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md` (Phụ lục A)
- Huong nghiep vu + UX/UI: `../FUNC_IMPROVE/06_INVENTORY_BUSINESS_IMPROVE.md`

---

## File đã xóa / gộp (không tìm trong repo)

Nội dung đã chuyển vào **`WAREHOUSE_TOM_TAT_NOI_BO.md`** (§10–11):

- `WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md`
- `WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md`
- `WAREHOUSE_PM_BUSINESS_QUESTIONS_EN.md`

**Gộp 2026-04-12 / 2026-04-23:**

- Checklist UAT cũ và audit E2E cũ đã hợp nhất vào:
    - [`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)
    - [`ERP_SO_PO_DO_INV_WH_QA_VI.md`](ERP_SO_PO_DO_INV_WH_QA_VI.md)

**Gộp 2026-04-06:**

- `WAREHOUSE_OPERATION_RUNBOOK_VI.md` + `WAREHOUSE_UPGRADE_PLANE.MD` → [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE_VI.md)
- `REFACTOR_SO_DO_PO_GRN_IMPLEMENTATION_PLAN_VI.md` + `REFACTOR_SO_DO_PO_GRN_TRACKER_VI.md` + `REFATOR_SO_DO_PO_GRN_DECISION_VI.md` → [`SO_DO_PO_GRN_REFACTOR_VI.md`](../FUNC_IMPROVE/05_SO_DO_PO_GRN_REFACTOR_VI.md)
- `WEBHOOK_INBOUND_OUTBOUND_NOTE_VI.md` → mục [Thuật ngữ Inbound/Outbound](#thuật-ngữ-inboundoutbound-api--webhook) bên dưới

Các tên cũ khác (Scope B log, Go-No-Go, Dingxin explained…) đã gộp trước đó — xem danh sách trong commit lịch sử.

---

## Thuật ngữ Inbound/Outbound (API & Webhook)

- **Inbound (đối với ERP):** hệ thống bên ngoài **gọi vào** ERP (ERP nhận request), ví dụ **`POST /api/integrations/orders`** để tạo Order (tích hợp AI / third-party).
- **Outbound:** ERP **gọi ra** ngoài (module Webhooks đẩy event) — khác hướng với inbound trên.
- Mẹo: **IN** = người ta gọi vào mình; **OUT** = mình gọi ra người ta.

Luồng LINE → AI → ERP: LINE → AI (inbound phía AI); AI → ERP (inbound phía ERP). Module Webhooks ERP = outbound.

**Trả lời ngắn cho PM/CTO:** Endpoint test AI → ERP là **inbound**. Module Webhooks hiện tại là **outbound**. Hai hướng không thay thế nhau.

Chi tiết PO/DO/invoice vs `PurchaseBill`: [`ERP_SO_PO_DO_INV_WH_QA_VI.md`](ERP_SO_PO_DO_INV_WH_QA_VI.md) (mục 2).

---

## Liên hệ MAOLIN

[`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) trỏ warehouse về **`WAREHOUSE_INDEX.md`** này.
