# Tiến Độ Triển Khai Multi-Warehouse (Đa Kho)

Tài liệu này theo dõi quá trình thực hiện module Warehouse riêng biệt.

## 1. Khởi tạo & Cấu trúc (Initialization)

- [x] Tạo Module `Warehouse` (`Modules/Warehouse`)
- [x] Thiết lập cấu trúc thư mục (Entities, Database, Http, etc.)

## 2. Cơ sở dữ liệu (Database)

- [x] Bảng `warehouses`: Lưu danh sách kho (Tên, Mã, Địa chỉ, Loại).
- [x] Bảng `warehouse_product_stock`: Lưu tồn kho theo từng kho (`product_id`, `warehouse_id`, `quantity`).
- [x] Bảng `purchase_inventory_adjustment`: Thêm `warehouse_id`.
- [x] Bảng `purchase_stock_adjustments`: Thêm `warehouse_id`.

## 3. Models & Entities

- [x] `Warehouse` Model.
- [x] `WarehouseProductStock` Model (Pivot).
- [x] Quan hệ (Relationships): Product `hasMany` WarehouseProductStock.

## 4. Logic Nghiệp vụ (Business Logic)

- [x] CRUD Warehouse (Quản lý kho).
- [x] Logic điều chỉnh tồn kho (Stock Adjustment) theo kho (trong Warehouse Controller).
- [ ] Logic chuyển kho (Transfer).

## 5. Tích hợp (Integration)

- [x] Tích hợp vào Purchase (Nhập hàng -> Chọn kho).
    - Đã thêm `warehouse_id` vào `PurchaseInventory` và `PurchaseStockAdjustment`.
    - Đã cập nhật `PurchaseInventoryController` để xử lý tồn kho theo kho.
    - Đã cập nhật giao diện `create` để chọn kho (nếu module active).
- [ ] Tích hợp vào Sales (Bán hàng -> Trừ kho).

---

**Trạng thái hiện tại:** Đã hoàn thành tích hợp Purchase Module. Hệ thống hỗ trợ chọn kho khi nhập tồn đầu kỳ hoặc điều chỉnh tồn kho. Tính năng này hoạt động song song và không ảnh hưởng nếu tắt module Warehouse.
