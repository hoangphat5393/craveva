# Warehouse — Mục lục tài liệu (điểm vào)

**Cập nhật:** 2026-04-06  
**Mục đích:** Ít file hơn — bắt đầu từ bảng dưới.

---

## Đọc theo nhu cầu

| Bạn cần…                                                               | File                                                                                           |
| ---------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| **Quy trình PO / DO / SO / Invoice / Kho (một chỗ)**                   | **[`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)** |
| Luồng chi tiết **chỉ module kho** (điều chỉnh, chuyển, ledger…)        | [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md)                       |
| **Runbook vận hành + kế hoạch nâng cấp (WUP)**                         | [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md)         |
| Kiến trúc, DB, URL, permission                                         | [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)                                       |
| Trạng thái code, Scope A/B, **audit trước upgrade**, **prompt Cursor** | [`WAREHOUSE_TOM_TAT_NOI_BO.md`](WAREHOUSE_TOM_TAT_NOI_BO.md) §10–11                            |
| Câu hỏi PM (VI + **EN** cuối file)                                     | [`WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md`](WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md)       |
| Checklist UAT tay                                                      | [`WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`](WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md)                     |
| Luồng code SO/Invoice/PO (English, deep)                               | [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md)                                             |
| Audit đa kho (lịch sử + note Scope B)                                  | [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md)                           |
| Refactor **Sales DO / GRN** (quyết định + kế hoạch + tracker)          | [`SO_DO_PO_GRN_REFACTOR_VI.md`](SO_DO_PO_GRN_REFACTOR_VI.md)                                   |

**Link cũ giữ tên:** [`B2B_ERP_PO_DO_INVOICE_GUIDE.md`](B2B_ERP_PO_DO_INVOICE_GUIDE.md) → stub trỏ về **QUY*TRINH*…**.

---

## File đã xóa / gộp (không tìm trong repo)

Nội dung đã chuyển vào **`WAREHOUSE_TOM_TAT_NOI_BO.md`** (§10–11) hoặc **`WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md`** (bản EN):

- `WAREHOUSE_PRE_UPGRADE_DEPENDENCY_AUDIT_CHECKLIST.md`
- `WAREHOUSE_CURSOR_PROMPT_UAT_COMPLETION.md`
- `WAREHOUSE_PM_BUSINESS_QUESTIONS_EN.md`

**Gộp 2026-04-06:**

- `WAREHOUSE_OPERATION_RUNBOOK_VI.md` + `WAREHOUSE_UPGRADE_PLANE.MD` → [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md)
- `REFACTOR_SO_DO_PO_GRN_IMPLEMENTATION_PLAN_VI.md` + `REFACTOR_SO_DO_PO_GRN_TRACKER_VI.md` + `REFATOR_SO_DO_PO_GRN_DECISION_VI.md` → [`SO_DO_PO_GRN_REFACTOR_VI.md`](SO_DO_PO_GRN_REFACTOR_VI.md)
- `WEBHOOK_INBOUND_OUTBOUND_NOTE_VI.md` → mục [Thuật ngữ Inbound/Outbound](#thuật-ngữ-inboundoutbound-api--webhook) bên dưới

Các tên cũ khác (Scope B log, Go-No-Go, Dingxin explained…) đã gộp trước đó — xem danh sách trong commit lịch sử.

---

## Thuật ngữ Inbound/Outbound (API & Webhook)

- **Inbound (đối với ERP):** hệ thống bên ngoài **gọi vào** ERP (ERP nhận request), ví dụ `POST /ai-order-webhook/{hash}` để tạo Order.
- **Outbound:** ERP **gọi ra** ngoài (module Webhooks đẩy event) — khác hướng với inbound trên.
- Mẹo: **IN** = người ta gọi vào mình; **OUT** = mình gọi ra người ta.

Luồng LINE → AI → ERP: LINE → AI (inbound phía AI); AI → ERP (inbound phía ERP). Module Webhooks ERP = outbound.

**Trả lời ngắn cho PM/CTO:** Endpoint test AI → ERP là **inbound**. Module Webhooks hiện tại là **outbound**. Hai hướng không thay thế nhau.

Chi tiết PO/DO/invoice vs `PurchaseBill`: [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md) (mục L).

---

## Liên hệ MAOLIN

[`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) trỏ warehouse về **`WAREHOUSE_INDEX.md`** này.
