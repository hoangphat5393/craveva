# Báo cáo rà soát hệ thống Đa kho (Multi-Warehouse)

**Phạm vi:** Mã nguồn Laravel trong workspace (đọc code + luồng nghiệp vụ), không chạy test E2E trên môi trường thật.  
**Ngày:** 2026-03-25  
**Phiên bản tài liệu:** 1.0

---

## 1. Tổng quan hệ thống

### 1.1 Module đã kiểm tra

| Thành phần                                 | Ghi chú ngắn                                                                                                                                                       |
| ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Module Warehouse** (`Modules/Warehouse`) | Kho, `WarehouseProductStock`, `WarehouseProductBatch`, `StockMovementService` (nhập/xuất/chuyển, FEFO), `StockReservationService`, chuyển kho UI.                  |
| **Purchase — Tồn kho / Điều chỉnh**        | `PurchaseStockAdjustment`, `PurchaseInventoryController`, nhập Excel qua `ImportInventoryJob`.                                                                     |
| **Purchase Order (PO)**                    | `PurchaseOrderObserver` — khi `delivery_status = delivered` cập nhật `PurchaseStockAdjustment` (theo `warehouse_id`) và gọi `recordInbound` nếu bật config.        |
| **Delivery Order (DO)**                    | `DeliveryOrderObserver` — DO inbound, `status = received` → `recordInboundBatch` nếu bật config; có `inbound_stock_applied` chống ghi trùng.                       |
| **Client**                                 | `ClientDetails.default_warehouse_id`; import `designated_warehouse_code` / `name` → `ClientImportProcessor`.                                                       |
| **Product**                                | Thuộc tính sản phẩm (SKU, v.v.); **không** có `warehouse_id` trên bản ghi sản phẩm — tồn theo kho nằm ở lớp kho/lô.                                                |
| **Hóa đơn bán (Invoice)**                  | `Invoice` / `InvoiceController` — kiểm tra tồn qua `PurchaseStockAdjustment::where('product_id')->sum('net_quantity')` (tổng mọi dòng adjustment, không theo kho). |
| **Thanh toán (Payment / Billing)**         | `Modules/Purchase/Observers/PaymentObserver` — khi tạo/xóa payment, điều chỉnh `PurchaseStockAdjustment` theo `product_id` **không lọc `warehouse_id`**.           |
| **Đơn hàng bán (Order)**                   | `OrderObserver` — **không** thấy tích hợp `StockMovementService` / xuất kho theo warehouse.                                                                        |

### 1.2 Nguồn sự thật tồn vật lý

- **Chuẩn đa kho:** `warehouse_product_batches` + đồng bộ `warehouse_product_stock` qua `StockMovementService::syncLegacyWarehouseStock`.
- **Legacy:** Bảng `purchase_stock_adjustments.net_quantity` vẫn được nhiều luồng cập nhật; với đa kho, tổng theo `product_id` không đủ để mô tả “tồn từng kho”.

---

## 2. Trạng thái tích hợp

### 2.1 Client ↔ Kho

- **Có:** Cột DB `client_details.default_warehouse_id` (mặc định giao hàng / ưu tiên nghiệp vụ).
- **Import:** `ClientImport` hỗ trợ `designated_warehouse_code`, `designated_warehouse_name` → map sang kho hợp lệ.
- **Hạn chế:** Giá trị này **không** tự động gán warehouse cho mọi giao dịch bán hoặc xuất kho trong code đã rà soát; chủ yếu là metadata + form.

**Đánh giá:** Tích hợp **metadata: Đạt**; **tự động hóa đơn bán theo kho: Chưa thấy trong code Order/Invoice.**

### 2.2 Product ↔ Kho

- Sản phẩm định danh toàn công ty; tồn theo **company + warehouse + product (+ batch/expiry)** qua module Warehouse.
- **Đạt** về mô hình dữ liệu; không có xung đột bắt buộc phải có `warehouse_id` trên `products`.

### 2.3 Inventory (Purchase Inventory / Điều chỉnh tồn)

- Form tạo/điều chỉnh có `warehouse_id`, batch, ngày SX/HSD (migration & controller).
- Điều chỉnh số lượng gọi `StockMovementService` (nhập/xuất) khi đủ điều kiện.
- **Đạt** như luồng nhập/xuất có gắn kho.

### 2.4 PO / DO / Invoice / Billing

| Luồng                            | Tích hợp kho vật lý                                                                                    | Ghi chú                                                                                                  |
| -------------------------------- | ------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------- |
| **PO → delivered**               | Có (`recordInbound` nếu `config('warehouse.inbound_from_purchase_order_delivered')` mặc định **true**) | Cần `purchase_orders.warehouse_id`.                                                                      |
| **DO inbound → received**        | Có (`recordInboundBatch` nếu `inbound_from_delivery_order_received` mặc định **false**)                | Bật env khi DO là chứng từ nhận hàng chuẩn; tránh bật đồng thời với PO nếu không kiểm soát double-count. |
| **Invoice (bán)**                | **Không** gọi `StockMovementService`                                                                   | Kiểm tra tồn bằng **tổng** `PurchaseStockAdjustment` theo `product_id`.                                  |
| **Payment (thanh toán invoice)** | Điều chỉnh **legacy** `PurchaseStockAdjustment` theo `product_id::first()`                             | **Không** an toàn cho đa kho.                                                                            |

---

## 3. Kết quả luồng end-to-end (kiểm tra logic mã)

Cách đọc: **PASS** = logic tồn tại và nhất quán với thiết kế đa kho _trong phạm vi đoạn code đó_. **FAIL** = thiếu hoặc mâu thuẫn với đa kho. **CẢNH BÁO** = phụ thuộc cấu hình hoặc trùng nguồn nhập kho.

| Bước | Mô tả                                                                                       | Kết quả                                                                                          |
| ---- | ------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| 1    | Tạo kho, kích hoạt module Warehouse, phân quyền                                             | **PASS** (module + migration permissions có trong repo)                                          |
| 2    | Import khách + gán `default_warehouse_id` / designated warehouse                            | **PASS** (import + DB)                                                                           |
| 3    | Import sản phẩm (CSV/Excel theo `ProductImport`)                                            | **PASS** (không yêu cầu warehouse trên dòng sản phẩm)                                            |
| 4    | Import tồn kho (`ImportInventoryJob` + `warehouse_code`/`warehouse_name`, SKU, batch, HSD…) | **PASS** (có resolve warehouse + cập nhật batch/stock)                                           |
| 5    | PO mua hàng: chọn `warehouse_id`, chuyển `delivery_status` → `delivered`                    | **PASS** nếu có `warehouse_id`; **FAIL** nếu thiếu warehouse (không nhập kho vật lý qua service) |
| 6    | DO nhận hàng: `status = received`, inbound                                                  | **PASS** khi bật config DO; **CẢNH BÁO** nếu vừa bật PO vừa bật DO cho cùng một lượng nhận       |
| 7    | Điều chỉnh tồn trong Purchase Inventory (UI)                                                | **PASS** (đồng bộ `StockMovementService`)                                                        |
| 8    | Chuyển kho (Warehouse Transfer)                                                             | **PASS**                                                                                         |
| 9    | Đơn bán (Order) hoàn thành → trừ tồn theo kho + lô                                          | **FAIL** — không thấy `recordOutbound` gắn Order                                                 |
| 10   | Tạo hóa đơn bán, kiểm tra tồn                                                               | **CẢNH BÁO** — kiểm tra `sum(net_quantity)` theo product, không phân kho                         |
| 11   | Thanh toán invoice → `PaymentObserver` trừ tồn                                              | **FAIL** (đa kho) — `where('product_id')->first()` không xác định kho                            |

---

## 4. Custom fields (trên các module)

### 4.1 Cơ chế

- `CustomFieldsTrait` + nhóm theo `CustomFieldGroup` / model (Client, Product, PurchaseInventory, PurchaseOrder, DeliveryOrder, …).
- Giao diện dùng `<x-forms.custom-field>` — xóa field trong Settings thường **không** làm crash nếu view không hard-code tên field.

### 4.2 Trạng thái theo module (tổng quan)

| Module             | Trạng thái                               | Vấn đề tiềm ẩn                                                                                              |
| ------------------ | ---------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| Client             | Động theo tenant                         | Trùng với cột core (đã tài liệu hóa trong `WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`) — nên dọn CF trùng. |
| Product            | Động                                     | Tương tự (brand, shelf life…) đã có cột core trong nhiều bản cài.                                           |
| Purchase Inventory | Động + cột core (warehouse, batch, HSD…) | Import Inventory merge thêm cột CF từ DB — xóa CF làm mất cột import map được **chỉ** nếu map vào CF đó.    |
| PO / DO            | Có CF                                    | Không ảnh hưởng trực tiếp `StockMovementService` trừ khi nghiệp vụ custom đọc CF.                           |

**Kết luận:** Custom field **không** là nguồn tồn kho vật lý; rủi ro chính là **trùng lặp dữ liệu** và **mapping import** sau khi dọn CF.

---

## 5. CSV / Excel import

### 5.1 Các importer trong repo (`app/Imports`, `Modules/Purchase/Imports`)

| Đối tượng            | File / job                                                         | Kết quả rà soát                                                                                                     |
| -------------------- | ------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------- |
| **Client**           | `ClientImport` + `ClientImportProcessor`                           | **Đạt** — có `client_code`, designated warehouse → `default_warehouse_id`.                                          |
| **Product**          | `ProductImport` + job xử lý chunk (theo codebase hiện tại)         | **Đạt** — nhiều cột map sang cột `products` / thuộc tính; **không** có warehouse trên dòng sản phẩm (đúng mô hình). |
| **Inventory**        | `InventoryImport` (định nghĩa cột) + `ImportInventoryJob`          | **Đạt** — `warehouse_code` / `warehouse_name`, batch, dates, quantity; đồng bộ batch stock.                         |
| **Orders (đơn bán)** | **Không** thấy class `OrderImport` tương đương trong `app/Imports` | **Không áp dụng** — import đơn hàng nếu có sẽ là module/tính năng khác hoặc chưa có.                                |

### 5.2 Lỗi / rủi ro import (mức logic)

- **Đa sheet Excel:** Importer kiểu `ToArray` thường xử lý sheet mặc định — file nhiều sheet cần tách sheet hoặc cấu hình đúng sheet (đã ghi trong tài liệu MAOLIN).
- **Thiếu warehouse trong file:** Job inventory có resolve theo code/tên; thiếu hoặc sai → dòng lỗi hoặc không ghi nhận movement (cần xem log job từng tenant).

---

## 6. Danh sách vấn đề

### 6.1 Nghiêm trọng (Critical)

1. **`PaymentObserver`** (`Modules/Purchase/Observers/PaymentObserver.php`): Cập nhật `PurchaseStockAdjustment` bằng `where('product_id')->first()` — với nhiều dòng cùng `product_id` khác `warehouse_id`, **không xác định** dòng nào bị trừ → sai lệch tồn và không khớp `warehouse_product_batches`.
2. **Đơn bán / Invoice:** Không có luồng `recordOutbound` gắn warehouse khi bán (Order/Invoice) trong phạm vi đã quét — **tồn vật lý và tồn legacy có thể lệch** so với nghiệp vụ “bán trừ đúng kho”.

### 6.2 Cảnh báo (Warning)

1. **Hai nguồn nhập kho:** `inbound_from_purchase_order_delivered` (mặc định true) và `inbound_from_delivery_order_received` (mặc định false). Bật cả hai mà không quy trình rõ → **nhập đôi**.
2. **`InvoiceController`:** Kiểm tra tồn dùng `sum(net_quantity)` theo `product_id` — phù hợp tổng toàn kho adjustment, **không** phản ánh “khả dụng từng kho” hoặc FEFO thực tế.
3. **PO `created` với delivered:** Một số nhánh vẫn dùng `PurchaseStockAdjustment::where('product_id')->first()` không warehouse (legacy) — rủi ro khi tạo PO ở trạng thái delivered ngay từ đầu.

### 6.3 Nhỏ (Minor)

1. Model `PurchaseOrder` trong repo không khai báo rõ `warehouse_id` trong PHPDoc/fillable — vẫn dùng được qua DB nhưng kém rõ ràng cho IDE/static analysis.
2. Tài liệu nội bộ (`WAREHOUSE_ANALYSIS_AND_PLAN.md`) mô tả E2E bán hàng đầy đủ hơn code hiện tại — cần đồng bộ kỳ vọng BA/QA.

---

## 7. Rủi ro nhất quán dữ liệu

| Rủi ro                 | Mô tả                                                                                                                                          |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **Hai sổ tồn**         | `warehouse_product_*` (đa kho) vs `purchase_stock_adjustments` (legacy theo dòng inventory/adjustment) — cần quy ước nguồn báo cáo chính thức. |
| **Tổng theo product**  | Mọi chỗ dùng `sum(net_quantity)` hoặc `first()` theo `product_id` cho **nhiều kho** đều có thể sai.                                            |
| **Không xuất kho bán** | Doanh thu / hóa đơn không giảm `warehouse_product_batches` → lệch với thực tế kho nếu kỳ vọng “bán trừ tồn”.                                   |
| **Import lịch sử**     | File kiểu “Last year net sales” không nằm trong `StockMovementService` — cần bảng báo cáo riêng (đã đề xuất trong `MAOLIN_MASTER_GUIDE.md`).   |

---

## 8. Khuyến nghị

### 8.1 Sửa / củng cố ngắn hạn

1. **PaymentObserver:** Bỏ hoặc sửa: không dùng `first()` không điều kiện; nếu vẫn cần điều chỉnh legacy thì **bắt buộc** `warehouse_id` (từ invoice line / client default / rule) và khóa đúng dòng adjustment — tốt nhất chuyển sang **xuất kho qua `StockMovementService`** tại sự kiện nghiệp vụ rõ ràng (giao hàng / xác nhận xuất), không phụ thuộc payment.
2. **Cấu hình env:** Xác nhận một “chứng từ nhận hàng chuẩn” — chỉ **một** trong PO-delivered hoặc DO-received ghi nhận inbound cho cùng quy trình.
3. **Kiểm tra tồn trên Invoice:** Thay vì chỉ `sum(net_quantity)` theo product, tham chiếu **`warehouse_product_stock`** (và rule kho mặc định của client) nếu nghiệp vụ yêu cầu.

### 8.2 Kiến trúc trung/dài hạn

1. **Đơn bán:** Thêm `warehouse_id` (header hoặc dòng), reservation + `recordOutbound` khi giao/chốt đơn — thống nhất với `StockReservationService` nếu dùng giữ chỗ.
2. **Một nguồn:** Coi `StockMovementService` là **cổng duy nhất** thay đổi tồn vật lý; thu hẹp dần ghi trực tiếp `PurchaseStockAdjustment` ngoài luồng điều chỉnh/import đã chuẩn hóa.
3. **QA tự động:** Bổ sung test tích hợp: PO delivered + DO received flags; payment + multi-row `purchase_stock_adjustments`; import inventory + snapshot batch.

---

## Phụ lục — File cấu hình & tham chiếu code

- `Modules/Warehouse/Config/config.php` — `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`, `WAREHOUSE_ALLOW_NEGATIVE_STOCK`.
- `Modules/Purchase/Observers/PurchaseOrderObserver.php` — `recordPurchaseOrderInbound`.
- `Modules/Purchase/Observers/DeliveryOrderObserver.php` — `recordInboundBatch`, `inbound_stock_applied`.
- `Modules/Purchase/Observers/PaymentObserver.php` — `adjustStock` (cần xem xét lại cho đa kho).
- `Modules/Purchase/Jobs/ImportInventoryJob.php` — import tồn theo warehouse.
- Tài liệu liên quan trong repo: `FUNC_LOGIC/WAREHOUSE_ANALYSIS_AND_PLAN.md`, `FUNC_LOGIC/WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`, `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md`.

---

_Tài liệu này được sinh ra để đáp ứng yêu cầu QA/Kiến trúc; cập nhật khi có thay đổi luồng Order/Invoice/Payment hoặc khi hoàn thiện xuất kho bán hàng._
