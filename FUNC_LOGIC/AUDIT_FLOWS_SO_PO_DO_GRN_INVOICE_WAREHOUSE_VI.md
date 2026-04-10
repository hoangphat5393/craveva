# Audit luồng SO · PO · DO/GRN · Warehouse · Invoice (Craveva)

**Ngày audit:** 2026-04-09  
**Phương pháp:** Đối chiếu **code thực tế** (observer, service, `config/warehouse.php`, `config/purchase.php`) với tài liệu quy trình; không thay thế UAT tay trên DB thật.

**Mục tiêu:** Trả lời (1) các chứng từ **liên quan nhau thế nào**, (2) **điều kiện** để luồng ổn định, (3) **rủi còn lại** khi vận hành.

---

## 1) Thuật ngữ (tránh nhầm “DO”)

| Tên trong tài liệu / UI       | Thực thể kỹ thuật (tóm tắt)                                                                           |
| ----------------------------- | ----------------------------------------------------------------------------------------------------- |
| **PO**                        | `PurchaseOrder` — đặt hàng NCC.                                                                       |
| **GRN / DO nhập**             | `DeliveryOrder` (hoặc `Grn` runtime) **type inbound**, nhận hàng mua — **không** phải phiếu giao bán. |
| **SO**                        | `Order` — đơn bán.                                                                                    |
| **Sales DO / phiếu giao bán** | `SalesShipment` (alias nghiệp vụ) — **không** dùng bảng `delivery_orders` cho bán.                    |
| **Invoice**                   | `Invoice` — AR; xuất kho **chỉ khi** `WAREHOUSE_SALES_OUTBOUND_MODE=invoice` và service bật.          |

Chi tiết phân biệt DO mua vs bán: [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md) (mục F–G).

---

## 2) Nhập kho (mua) — PO delivered vs GRN/DO received

| Kích hoạt                                | Điều kiện config                           | Code chính                                                                                                                                                               |
| ---------------------------------------- | ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **PO → inbound**                         | `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` | `PurchaseOrderObserver::recordPurchaseOrderInbound` → `WarehouseFlowPolicyService::assertInboundSourceAllowed('purchase_order')` → `StockMovementService::recordInbound` |
| **DO inbound → inbound**                 | `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`  | `DeliveryOrderObserver` → cùng policy `assertInboundSourceAllowed` → inbound; có **idempotent** `inbound_stock_applied`                                                  |
| **Cấu hình xung đột (cả hai PO + DO)**   | Cả hai `true`                              | `WarehouseFlowPolicyService` **throw** (fail fast) — tránh nhập đôi theo thiết kế WUP-04                                                                                 |
| **PO delivered + DO received (cùng lô)** | Cả hai bật nhưng DO observer có **guard**  | Nếu PO đã `delivered` và PO inbound bật → DO inbound **skip** + `Log::warning` (lớp an toàn thêm)                                                                        |

**Kết luận nhập:** Ổn định khi **chỉ một** nguồn canonical được dùng cho cùng quy trình nhận (khuyến nghị trong `.env`: ví dụ PO `true`, DO `false`, hoặc ngược lại). Xem bảng env: [`WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md).

---

## 3) Xuất kho (bán) — Sales DO (ship) vs Invoice

| Mode `WAREHOUSE_SALES_OUTBOUND_MODE`  | Ai trừ tồn?                                                                                                         | Ghi chú                                                                     |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| **`shipment`** (mặc định khuyến nghị) | `SalesShipmentStockService` khi **ship**; `InvoiceWarehouseStockService::shouldPostOutboundFromInvoice()` **false** | Invoice **không** post outbound — tránh trừ hai lần với shipment.           |
| **`invoice`**                         | `InvoiceWarehouseStockService::syncInvoiceStock` (qua `App\Observers\InvoiceObserver`)                              | Xuất kho theo hóa đơn (legacy); shipment không đóng vai trò outbound chính. |

**Điều kiện chung outbound:**

- `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
- Module **Warehouse** bật; với request web, user thường cần `warehouse` trong `user_modules` (`InvoiceWarehouseStockService::isEnabled()`).
- `WarehouseFlowPolicyService::assertOutboundConfigurationValid()` — mode phải `shipment` hoặc `invoice` (không giá trị lạ).

**Sales DO lifecycle (reserve / outbound / release):** `SalesDoService` + `SalesShipmentStockService` + `StockReservationService` — confirm reserve, ship outbound + consume reservation, cancel release, reverse xử lý hoàn kho + re-reserve khi cần.

**Kết luận xuất:** Ổn định khi **một mode** được chốt và team không kỳ vọng “vừa ship vừa invoice đều trừ tồn” cùng lúc.

---

## 4) Thanh toán (Payment) và tồn legacy

- `PaymentObserver::adjustStock` chỉ chạy điều chỉnh `PurchaseStockAdjustment` khi **`WAREHOUSE_SALES_OUTBOUND_ENABLED` là false** (đường legacy).
- Khi warehouse outbound bật (`true`), observer **bỏ qua** chỉnh legacy này — tránh **double** với `StockMovementService`.

**Kết luận:** Phù hợp orchestration “một ledger kho” (`stock_movements`); không coi đây là lỗi nếu `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`.

---

## 5) SO không tự trừ tồn

- Tạo / cập nhật **Order (SO)** **không** gọi xuất kho trực tiếp trong luồng đã rà soát; trừ tồn đi qua **Sales DO ship** hoặc **Invoice** (tùy mode).
- Webhook AI tạo Order (`AiOrderWebhookController`): có thể **chặn** tạo đơn nếu thiếu sellable (theo package + env) — không thay đổi rule “SO không trừ tồn”.

---

## 6) Nâng cấp WUP (P0/P1) — liên quan ổn định

| Hạng mục                                | Ảnh hưởng luồng                                                                        |
| --------------------------------------- | -------------------------------------------------------------------------------------- |
| **WUP-01** `warehouse_type`             | Chặn reserve/outbound bán từ kho không sellable.                                       |
| **WUP-02** availability API / service   | Một nguồn công thức sellable; API Sanctum; webhook AI dùng cùng service khi bật check. |
| **WUP-03** reserve → outbound → release | Đồng bộ lifecycle Sales DO.                                                            |
| **WUP-04** inbound/outbound canonical   | Guard + log; tránh cấu hình mơ hồ.                                                     |
| **WUP-06** unit conversion              | Trừ/nhập theo base unit khi có mapping.                                                |
| **WUP-07** idempotency / reconciliation | Giảm post trùng; đối soát ngày.                                                        |

Chi tiết trạng thái: [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md) §6.

---

## 7) Rủi ro / phần chưa “đóng” hoàn toàn (theo QA + kiến trúc)

Các điểm sau **không** làm mất ổn định mặc định nếu env đúng, nhưng cần biết khi mở rộng:

| Chủ đề                                           | Ghi chú                                                                                                                                                                         |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Nhiều invoice trên một SO                        | DB cho phép; UI/luồng từng đợt có thể cần hoàn thiện thêm — xem [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md). |
| `default_warehouse_id` khách vs kho thực tế giao | Invoice/shipment resolve kho theo rule tập trung — cần master data đúng.                                                                                                        |
| Job/console không có `user()`                    | `InvoiceWarehouseStockService::isEnabled()` vẫn có nhánh cho ngữ cảnh không user — nên kiểm tra khi tự động hóa.                                                                |
| **WUP-08 / WUP-09**                              | Báo cáo vận hành rộng, bin/location — ngoài phạm vi P0/P1 nền.                                                                                                                  |

---

## 8) Kết luận audit (ổn định hay chưa?)

- **Có:** Luồng **được thiết kế ổn định** khi:
    1. Chọn **một** nguồn nhập canonical (PO **hoặc** DO/GRN) qua `.env`, không bật xung đột cố ý.
    2. Chọn **một** mode xuất bán (`shipment` **hoặc** `invoice`) và `WAREHOUSE_SALES_OUTBOUND_ENABLED` phù hợp kỳ vọng.
    3. Module Warehouse + migration + quyền user đúng; master kho / `warehouse_type` / client default warehouse khớp nghiệp vụ.
- **Cần UAT định kỳ:** mọi thay đổi `.env` trên từng tenant; kiểm tra một lô PO+DO thật và một lô SO→Sales DO→Invoice để bắt lệch kỳ vọng vận hành.
- **Smoke tự động:** [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md) §E.1 — lệnh `php artisan test` cho `WarehouseUpgradeP0Test`, `PurchaseInboundStockFlowTest`, `InvoiceWarehouseStockScopeBTest`.

**Tài liệu vận hành liên quan:** [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md) · Env: [`WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md`](WAREHOUSE_AND_PURCHASE_FLOW_ENV_REFERENCE_VI.md) · Runbook WUP: [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md).
