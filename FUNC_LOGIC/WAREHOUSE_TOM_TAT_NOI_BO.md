# Warehouse — Tóm tắt nội bộ (điểm vào)

**Cập nhật:** 2026-04-09

Các tài liệu nội bộ cũ (audit trước nâng cấp, prompt UAT, §10–11) **không còn một file dài riêng**; nội dung tương đương được gom vào các file canonical dưới đây để tránh trùng lặp và link gãy.

---

## Đọc gì thay cho §10–11 và “trạng thái triển khai”

| Chủ đề                                                           | File                                                                                                                                               |
| ---------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Trạng thái WUP-01…WUP-07**, runbook local, checklist UAT nhanh | [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) (mục §1 Runbook + §6 Trạng thái triển khai) |
| **Mục lục** toàn bộ tài liệu Warehouse                           | [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)                                                                                                         |
| QA đối chiếu SO / PO / DO / Invoice / kho với code               | [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)                                     |
| Checklist UAT E2E (Mua · Bán · Kho)                              | [`UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md`](UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md)                                                                       |

---

## Khớp thiết kế WUP (tóm tắt)

- **P0 (WUP-01…04):** đã có trong code (policy `warehouse_type`, `WarehouseAvailabilityService`, lifecycle reserve/outbound/release trên Sales DO, guard inbound/outbound canonical). Chi tiết và tiến độ: [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) §6.
- **P1 nền:** WUP-06 (unit conversion + strict env), WUP-07 (idempotency + reconciliation command) **Done (nền)**; **WUP-05 Done (nền + webhook AI)** — API Sanctum + kiểm tra sellable trên `POST /ai-order-webhook/{hash}` (xem runbook §6).
- **WUP-08 / WUP-09:** vẫn là backlog / giai đoạn sau trong cùng file runbook (báo cáo vận hành rộng, bin/location).

---

## Tên file cũ (không còn trong repo)

- `WAREHOUSE_UPGRADE_PLANE.MD` + `WAREHOUSE_OPERATION_RUNBOOK_VI.md` → đã gộp vào [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](../FUNC_IMPROVE/WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) (xem [`WAREHOUSE_INDEX.md`](WAREHOUSE_INDEX.md)).

---

## Câu hỏi PM (bản riêng)

File `WAREHOUSE_PM_CAU_HOI_CHOT_NGHIEP_VU_VI.md` nếu không có trong repo: tham chiếu nghiệp vụ tại [`WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`](WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md) và [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md).
