# Tham chiếu biến môi trường — Kho, PO / GRN (DO nhập), Sales DO (phiếu giao bán), SO / Webhook AI

**Mục đích:** Gom **một chỗ** các biến cần khai báo trong `.env` (hoặc tương đương trên server) cho nghiệp vụ **quản lý kho** và luồng **Purchase / Sales** liên quan tồn kho.  
**Quy ước:** Tên biến theo code hiện tại; giá trị mặc định là **default trong code** nếu không set.

**Sau khi đổi `.env`:** chạy `php artisan config:clear` (và deploy lại config cache nếu dùng `config:cache` trên production).

**Ghi đè theo công ty (UI):** Các cờ trong bảng dưới có thể được lưu **theo từng `company_id`** tại **Cài đặt → (sidebar module Warehouse) → Luồng kho & tồn** (`/account/warehouse/company-flow-settings`). Khi công ty **chưa có** bản ghi trong bảng `warehouse_company_flow_settings`, hệ thống đọc **`config('warehouse.*')`** (tức mặc định từ `.env` / config đã publish). **Chỉnh DB/UI không cần** `config:clear`; chỉ khi đổi `.env` hoặc file config trên server mới cần xóa/refresh config cache.

**Chuỗi dịch (warehouse::app.\*):** Bản chạy thực tế load từ **`Modules/Warehouse/Resources/lang/{locale}/app.php`**; sau đó merge **`resources/lang/modules/warehouse`** nếu thư mục tồn tại (custom / Language Pack publish). Bản đồng bộ cho chỉnh sửa theo convention repo: **`Modules/LanguagePack/Languages/modules/Warehouse/{locale}/app.php`**.

---

## 1) Module Warehouse (`config/warehouse.php` ← `Modules/Warehouse/Config/config.php`)

| Biến `.env`                              | Mặc định   | Ý nghĩa nghiệp vụ                                                                                                                                                         |
| ---------------------------------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `WAREHOUSE_ALLOW_NEGATIVE_STOCK`         | `false`    | Cho phép tồn âm khi xuất/adjust (thường **false** trừ khi có quy trình đặc biệt).                                                                                         |
| `WAREHOUSE_STRICT_UNIT_CONVERSION`       | `false`    | Bật **true** khi đã map đủ `product_unit_conversions`: thiếu mapping sẽ lỗi thay vì cộng số “nguyên đơn vị”.                                                              |
| `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`    | `true`     | **Nhập kho** khi PO chuyển trạng thái **delivered** (đường nhập canonical thường dùng).                                                                                   |
| `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`     | `false`    | **Nhập kho** khi phiếu nhận hàng mua (GRN / DO nhập) **received**. Chỉ bật khi DO là nguồn nhập chuẩn.                                                                    |
| `WAREHOUSE_SALES_OUTBOUND_ENABLED`       | `true`     | Bật tích hợp xuất kho bán (invoice/shipment tùy mode). `false` = không tự động trừ tồn qua các service warehouse.                                                         |
| `WAREHOUSE_SALES_OUTBOUND_MODE`          | `shipment` | **`shipment`**: trừ tồn khi **Sales DO / Sales Shipment** → **ship**. **`invoice`**: trừ kho theo hóa đơn (legacy). **Không** để hai nguồn xuất cùng lúc — chọn một mode. |
| `WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK` | `true`     | Webhook `POST /ai-order-webhook/{hash}`: kiểm tra **sellable** trước khi tạo SO. `false` nếu tích hợp chưa gửi `unit_id` / chưa sẵn dữ liệu tồn.                          |

**Quy tắc tránh nhập đôi (PO delivered vs DO received):** chỉ **một** trong hai cờ inbound nên là `true` cho cùng một quy trình nhận hàng. Bật cả hai → `WarehouseFlowPolicyService` báo **conflict** (guard).

---

## 2) Module Purchase — UI / Inventory hỗ trợ (`config/purchase.php` ← `Modules/Purchase/Config/config.php`)

Các biến này **không** thay đổi logic xuất/nhập kho cốt lõi như bảng trên, nhưng cần cho **vận hành PO / GRN / Inventory** và tên màn hình SO/DO.

| Biến `.env`                                 | Mặc định    | Ý nghĩa                                                                                                                                                                 |
| ------------------------------------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `PURCHASE_FLOW_NAMING_MODE`                 | `compat_v2` | **`legacy`**: nhãn/route kiểu cũ (Sales Shipments / Delivery Orders). **`compat_v2`**: nhãn nghiệp vụ **Sales DO / GRN** (route kỹ thuật có thể giữ `sales-do`, `grn`). |
| `PURCHASE_DO_GRN_CUTOVER_ENABLED`           | `false`     | Công tắc dự phòng cutover DO/GRN (framework); khi `false` giữ hành vi hiện tại.                                                                                         |
| `PURCHASE_INVENTORY_MAX_CUSTOM_FIELD_JOINS` | `0`         | Giới hạn JOIN custom field trên DataTable Inventory (0 = tắt, tránh chậm).                                                                                              |
| `PURCHASE_INVENTORY_NEAR_EXPIRY_DAYS`       | `30`        | Ngưỡng “gần hết HSD” (ngày) cho lọc / trạng thái near expiry.                                                                                                           |

---

## 3) Tích hợp AI → tạo Sales Order (SO) qua webhook

Không nằm trong `config/warehouse.php` nhưng **dùng chung** với kiểm tồn (WUP-05) khi bật check stock.

| Biến / cấu hình           | Ghi chú                                                                                                                                                                                                                                                                        |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `AI_ORDER_WEBHOOK_SECRET` | Chuỗi bí mật dùng cho **URL** `.../ai-order-webhook/{hash}` và header **`X-AI-Webhook-Secret`** (phải khớp). Đọc trong code qua `config('app.ai_order_webhook_secret', env('AI_ORDER_WEBHOOK_SECRET'))` — nên khai báo trong `config/app.php` hoặc chỉ `.env` tùy cách deploy. |
| (payload) `check_stock`   | Không phải env: client gửi `false` để bỏ qua kiểm sellable trên webhook.                                                                                                                                                                                                       |
| Gói company               | Kiểm tồn webhook chỉ chạy nếu package company có module **`warehouse`** (theo JSON `module_in_package`).                                                                                                                                                                       |

---

## 4) Bảng tóm tắt luồng nghiệp vụ ↔ biến chính

| Luồng                      | File cấu hình / biến                                                                                                   |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| **Nhập kho mua**           | `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED` (chọn **một** nguồn canonical).            |
| **Xuất kho bán (SO → …)**  | `WAREHOUSE_SALES_OUTBOUND_ENABLED`, `WAREHOUSE_SALES_OUTBOUND_MODE` (`shipment` = Sales DO ship; `invoice` = invoice). |
| **Đơn vị / quy đổi**       | `WAREHOUSE_STRICT_UNIT_CONVERSION` + bảng `product_unit_conversions`.                                                  |
| **AI đặt hàng → SO**       | `AI_ORDER_WEBHOOK_SECRET` + `WAREHOUSE_AI_ORDER_WEBHOOK_CHECK_STOCK`.                                                  |
| **Tên màn GRN / Sales DO** | `PURCHASE_FLOW_NAMING_MODE`.                                                                                           |

---

## 5) Tài liệu liên quan

- Runbook & trạng thái WUP: [`WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md`](WAREHOUSE_RUNBOOK_AND_UPGRADE_PLAN_VI.md)
- Quy trình PO / DO / SO / Invoice / Kho: [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md)
- QA đối chiếu code: [`ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md`](ERP_SO_PO_DO_INVOICE_WAREHOUSE_QA_VERIFICATION_VI.md)

**Cập nhật:** 2026-04-09 — đối chiếu `Modules/Warehouse/Config/config.php`, `Modules/Purchase/Config/config.php`, `AiOrderWebhookController`.
