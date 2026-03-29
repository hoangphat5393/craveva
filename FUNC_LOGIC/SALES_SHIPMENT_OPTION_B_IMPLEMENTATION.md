# SALES SHIPMENT Option B (MVP) - Implementation

## 1) Kiến trúc tổng thể

Option B được triển khai theo hướng tách chứng từ giao hàng bán khỏi `delivery_orders` inbound:

- Inbound PO/DO giữ nguyên ở `delivery_orders` + `DeliveryOrderObserver` (không thay đổi semantics).
- Sales outbound đi theo chứng từ mới:
    - Header: `sales_shipments`
    - Lines: `sales_shipment_items`
- Service xuất kho tách riêng: `Modules/Warehouse/Services/SalesShipmentStockService.php`.
- Orchestration mode S/I:
    - `warehouse.sales_outbound_mode=shipment`: xuất kho theo shipment (`SalesShipmentStockService`), invoice không trừ tồn.
    - `warehouse.sales_outbound_mode=invoice`: giữ hành vi legacy trong `InvoiceWarehouseStockService`.

Mục tiêu là đảm bảo không có double deduction giữa shipment và invoice.

## 2) Bảng dữ liệu mới

### `sales_shipments`

- `id`, `company_id`, `order_id`, `warehouse_id`
- `shipment_number` (unique theo `company_id`)
- `shipment_date`
- `status`: `draft|confirmed|shipped|delivered|cancelled`
- `outbound_stock_applied` (idempotency)
- `notes`, `created_by`, `updated_by`, `timestamps`
- index: `(company_id, status, shipment_date)`

### `sales_shipment_items`

- `id`, `sales_shipment_id`, `order_item_id`, `product_id`, `unit_id`
- `quantity_ordered`, `quantity_shipped`
- `batch_number`, `timestamps`

## 3) State machine

- `draft`:
    - cho phép sửa, thay line
    - có thể `confirm`
- `confirmed`:
    - cho phép `ship` hoặc cập nhật line trong MVP
- `shipped`:
    - lock line edits
    - outbound stock được post một lần nếu mode=shipment
- `delivered`:
    - xác nhận giao thành công sau `shipped`
- `cancelled`:
    - cho phép hủy khi chưa ship/chưa post outbound

Action bổ sung:

- `deliver`: `shipped -> delivered`.
- `reverse`: `shipped|delivered -> confirmed` + hoàn kho (reverse outbound).

## 4) Cấu hình mode S/I

Thêm config:

- `WAREHOUSE_SALES_OUTBOUND_MODE=invoice|shipment`

Và xử lý:

- `InvoiceWarehouseStockService` chỉ post/reverse khi mode=`invoice`.
- `SalesShipmentStockService` chỉ post outbound khi mode=`shipment`.

Comment đã được thêm tại điểm quyết định luồng để tránh double deduction.

## 5) MVP UI + permission

### Permission mới

- `view_sales_shipment`
- `create_sales_shipment`
- `update_sales_shipment`
- `ship_sales_shipment`
- `cancel_sales_shipment`

### UI

- Menu Operations có mục `Sales Shipments`.
- Màn hình:
    - List
    - Create
    - Edit
    - Show
- Actions:
    - confirm
    - ship
    - deliver
    - reverse outbound
    - cancel
- Tu `orders.show` co nut "Add Sales Shipments" (Create Shipment from SO).
- UI show/list được canh theo pattern Operation/Purchase (header tab, dropdown actions, status badge).

## 6) Validation và partial shipment

- `quantity_shipped` bị chặn nếu vượt `remaining qty` của `order_items`.
- Cho phép nhiều shipment cho cùng `order_id`.
- Không cho sửa line khi shipment đã `shipped|delivered`.
- Idempotency outbound qua `sales_shipments.outbound_stock_applied` + `lockForUpdate`.

## 7) UAT checklist

- [ ] SO có 10 qty, shipment 1 giao 4.
- [ ] Shipment 2 giao 6.
- [ ] Shipment 3 giao >0 bị chặn.
- [ ] Stock movement outbound ghi đúng kho/reference.
- [ ] Chuyển mode shipment: invoice không tạo outbound lần 2.
- [ ] Luồng PO/DO inbound cũ vẫn hoạt động bình thường.

## 8) Tài liệu sử dụng

- Hướng dẫn thao tác UI và checklist test tay:
    - `FUNC_LOGIC/SALES_SHIPMENT_OPTION_B_USER_GUIDE_VI.md`
