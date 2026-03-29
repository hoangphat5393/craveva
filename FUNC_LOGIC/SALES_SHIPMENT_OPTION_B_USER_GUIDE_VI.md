# Hướng dẫn sử dụng Sales Shipment (Option B) - UAT/Vận hành

## 1) Mục tiêu

Tài liệu này hướng dẫn team vận hành và QA sử dụng luồng:

- `SO (Order) -> Sales Shipment -> Invoice`
- Cấu hình mode outbound để tránh trừ tồn hai lần.

## 2) Điều kiện trước khi test

1. Đã migrate DB mới:
    - `sales_shipments`
    - `sales_shipment_items`
2. User có quyền:
    - `view_sales_shipment`
    - `create_sales_shipment`
    - `update_sales_shipment`
    - `ship_sales_shipment`
    - `cancel_sales_shipment`
3. Module Warehouse đang bật và có kho active.

## 3) Cấu hình outbound mode

Trong `.env`:

- `WAREHOUSE_SALES_OUTBOUND_ENABLED=true`
- `WAREHOUSE_SALES_OUTBOUND_MODE=shipment` (khuyến nghị cho Option B)

Sau đó chạy:

- `php artisan config:clear`

### Ý nghĩa mode

- `shipment`: khi shipment `shipped` thì trừ tồn, invoice không trừ tồn thêm.
- `invoice`: giữ hành vi cũ, outbound theo invoice.

## 4) Các màn hình UI

### 4.1 Vào menu

- Operations -> `Sales Shipments`

### 4.2 Tạo shipment từ SO

Cách 1:

- Mở `Orders -> Show`
- Chọn action: `Add Sales Shipments`

Cách 2:

- Vào list `Sales Shipments` -> Add
- Chọn `Order`

Hệ thống sẽ:

- Nạp item theo `order_items`
- Tính `Remaining Qty` theo các shipment đã tạo trước đó
- Tự động đề xuất kho mặc định theo client (nếu có `default_warehouse_id`)

## 5) Vòng đời trạng thái

- `draft` -> `confirm` -> `ship` -> `deliver`
- Có thể `cancel` khi cần hủy chứng từ
- Có action `reverse outbound` cho `shipped/delivered`:
    - Hoàn kho
    - Đưa shipment về `confirmed`

## 6) Quy tắc validation quan trọng

- `quantity_shipped` không được vượt `remaining qty`.
- Line hết `remaining qty` sẽ bị disable trên form.
- Shipment đã `shipped/delivered` không cho edit line.
- `shipment_number` unique theo company.

## 7) Checklist UAT nhanh (khuyến nghị)

1. Tạo SO 10.
2. Tạo shipment #1: ship 4.
3. Tạo shipment #2: ship 6.
4. Tạo shipment #3: thử ship >0 -> phải bị chặn.
5. Kiểm tra stock movement outbound đúng kho, đúng số lượng.
6. Thử reverse outbound trên shipment đã shipped -> tồn kho quay lại đúng.
7. Nếu mode=shipment: tạo/sửa invoice không được tạo outbound lần 2.

## 8) Xử lý sự cố thường gặp

- Không thấy menu `Sales Shipments`:
    - Kiểm tra quyền `view_sales_shipment` và module Purchase.
- Ship báo lỗi thiếu tồn:
    - Kiểm tra tồn kho thực tế và `WAREHOUSE_ALLOW_NEGATIVE_STOCK`.
- Invoice vẫn trừ tồn trong mode shipment:
    - Kiểm tra `WAREHOUSE_SALES_OUTBOUND_MODE=shipment` và chạy `config:clear`.

## 9) File kỹ thuật tham chiếu

- `FUNC_LOGIC/SALES_SHIPMENT_OPTION_B_IMPLEMENTATION.md`
- `FUNC_LOGIC/QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`
- `Modules/Purchase/Http/Controllers/SalesShipmentController.php`
- `Modules/Warehouse/Services/SalesShipmentStockService.php`
