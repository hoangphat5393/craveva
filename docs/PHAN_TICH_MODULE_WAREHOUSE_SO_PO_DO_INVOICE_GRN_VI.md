# Phân tích Module Warehouse + SO/PO/DO/Invoice/GRN

## 1) Tổng quan chức năng

### Mục đích module

Module này quản lý chuỗi nghiệp vụ end-to-end giữa bán hàng, mua hàng, kho và hóa đơn:

- Sales side: `SO -> Sales DO (outbound) -> Sales Invoice`
- Purchase side: `PO -> GRN (inbound) -> Purchase Bill`
- Inventory side: đồng bộ tồn kho theo sự kiện xuất/nhập

### Bài toán business giải quyết

- Đồng bộ dữ liệu nghiệp vụ và tồn kho theo từng bước vận hành.
- Tránh trừ/nhập kho trùng lặp trong các flow giao/nhận.
- Bảo toàn tính toàn vẹn của chứng từ (order, do/grn, invoice/bill).

## 2) Tính năng chính

### Sales (SO -> DO -> Invoice)

- Tạo và cập nhật `Sales Order`.
- Tạo `Sales DO` từ SO, hỗ trợ giao một phần.
- Lifecycle Sales DO: `draft -> confirmed -> shipped -> delivered`, có `reverse` và `cancel`.
- Tạo `Sales Invoice` liên kết với order và giữ đúng logic xuất kho theo chế độ cấu hình.

### Purchase (PO -> GRN -> Bill)

- Tạo và cập nhật `Purchase Order`.
- Tạo `GRN` từ PO, nhập `quantity_received`, `batch_number`, `expiry_date`.
- Lifecycle GRN: `draft -> inbound -> received`.
- Liên kết tiếp sang `purchase bill` cho nghiệp vụ tài chính.

### Warehouse/Inventory

- Ghi nhận stock movement qua `stock_movements`.
- Cập nhật tồn tổng hợp theo kho/sản phẩm qua `warehouse_product_stock`.
- Có guard idempotent để ngăn post trùng lặp.

## 3) Luồng nghiệp vụ

### Luồng 1: SO -> DO (outbound) -> Sales Invoice

1. User tạo SO, nhập item/qty/price/tax.
2. User tạo Sales DO từ SO.
3. Confirm Sales DO.
4. Ship Sales DO:
    - Hệ thống post outbound stock.
    - Set cờ `outbound_stock_applied=true` để idempotent.
5. Deliver Sales DO.
6. Tạo Sales Invoice (liên kết order).
7. Nếu reverse/cancel DO, hệ thống post inbound bù và trả lại tồn.

### Luồng 2: PO -> GRN (inbound) -> Purchase Invoice/Bill

1. User tạo PO.
2. User tạo GRN từ PO.
3. Chuyển trạng thái GRN đến `received`.
4. Observer post inbound stock theo item nhận.
5. Set cờ `inbound_stock_applied=true` để idempotent.
6. Tạo Purchase Bill từ flow mua hàng.

## 4) Dữ liệu và cơ sở dữ liệu

### Bảng/model chính (hiện tại)

- Sales:
    - `orders`, `order_items` (`App\Models\Order`, `App\Models\OrderItems`)
    - `sales_dos`, `sales_do_items` (`Modules\Purchase\Entities\SalesDo`, `SalesDoItem`)
    - `invoices`, `invoice_items` (`App\Models\Invoice`, `InvoiceItems`)
- Purchase:
    - `purchase_orders`, `purchase_items` (`PurchaseOrder`, `PurchaseItem`)
    - `grns`, `grn_items` (`App\Models\Grn`, `Modules\Purchase\Entities\GrnItem`)
    - `purchase_bills` (flow bill mua)
- Warehouse:
    - `stock_movements`
    - `warehouse_product_stock`
    - `warehouse_product_batches`
    - `invoice_warehouse_stock_postings`

### Quan hệ dữ liệu tiêu biểu

- `orders.id -> sales_dos.order_id`
- `sales_dos.id -> sales_do_items.sales_do_id`
- `orders.id -> invoices.order_id`
- `purchase_orders.id -> grns.purchase_order_id`
- `grns.id -> grn_items.grn_id`
- `stock_movements` lưu sự kiện outbound/inbound theo `reference_type/reference_id`

## 5) Quy tắc nghiệp vụ và logic quan trọng

### Quy tắc stock outbound/inbound

- Outbound mode đang dùng: `warehouse.sales_outbound_mode=shipment`
    - Nghĩa là trừ kho tại bước `DO ship`, không trừ lại ở invoice.
- Inbound từ GRN đang dùng:
    - `warehouse.inbound_from_delivery_order_received=true`
    - `warehouse.inbound_from_purchase_order_delivered=false`

### Idempotent và anti double-post

- Sales DO dùng cờ `outbound_stock_applied`.
- GRN dùng cờ `inbound_stock_applied`.
- Observer có guard để tránh trường hợp nhập kho trùng lặp.

### Validate nghiệp vụ

- Không cho giao vượt số lượng còn lại theo từng order item.
- Chỉ cho phép transition trạng thái hợp lệ.
- Không cho line có `quantity_shipped > 0` mà thiếu `product_id`.

### Refactor naming/cutover

- UI và route đang theo naming mới: `sales-do`, `grn`.
- Trong local hiện tại đã pin runtime bảng mới và đã loại bỏ bảng legacy.

## 6) Phụ thuộc bên ngoài

- Laravel core: Eloquent, Events, Observers, Queue, Artisan commands.
- Service nội bộ:
    - `StockMovementService`
    - `SalesShipmentStockService`
    - `InvoiceWarehouseStockService`
    - `SalesDoService`, `GrnService`
- Payment/integration:
    - Stripe (invoice payment flow)
    - QuickBooks hook (nếu bật cấu hình)

## 7) Rủi ro và vấn đề tiềm ẩn

### Rủi ro kỹ thuật

- Sai lệch env local/staging nếu migration không đồng bộ.
- Nếu không khóa runtime mới ở tất cả env, có thể gặp lỗi query bảng cũ.
- Command migrate data/rollback cần chạy đúng thứ tự và đúng backup.

### Rủi ro hiệu năng

- Các màn preload danh sách lớn (client/product) nếu chưa remote search sẽ chậm.
- DataTable state cache trên browser có thể giữ column cũ nếu deploy chưa đồng bộ.

### Rủi ro vận hành

- Deploy không theo Git-first dễ gây dirty worktree, pull fail.
- Chưa có ký UAT business thì quyết định GO/NO-GO có thể thiếu cơ sở.

## 8) Đề xuất cải tiến

### Kiến trúc và mã nguồn

- Tiếp tục tách logic transition sang state machine rõ ràng hơn.
- Chuẩn hóa payload validation qua FormRequest + DTO.
- Giảm coupling controller bằng service/use-case layer.

### Dữ liệu và migration

- Bổ sung migration guard/checklist tự động trước khi drop/rollback.
- Chuẩn hóa index cho cột truy vấn nhiều (`company_id`, `status`, `order_id`, `purchase_order_id`).

### Hiệu năng và UX

- Áp dụng đồng bộ remote search + infinite scroll cho tất cả màn có dropdown lớn.
- Bổ sung metrics (thời gian load, API latency) để theo dõi sau deploy.

### Vận hành

- Chuẩn hóa runbook deploy:
    - backup -> precheck -> migrate -> smoke -> sign-off.
- Giữ quy trình Git-first, hạn chế hot-upload thủ công.

---

## Trạng thái kết luận hiện tại (local)

- 2 luồng nghiệp vụ chính đã pass test kỹ thuật.
- Warehouse/inventory đã liên kết và post đúng hướng outbound/inbound.
- Bảng legacy đã được loại bỏ trên local.
- Khuyến nghị: đồng bộ staging theo cùng migration + smoke test trước khi chốt production.
