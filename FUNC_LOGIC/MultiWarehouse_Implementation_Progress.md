# Tiến Độ Triển Khai Multi-Warehouse (Đa Kho)

Tài liệu này theo dõi quá trình thực hiện module Warehouse riêng biệt.

## 1. Khởi tạo & Cấu trúc (Initialization)

- [x] Tạo Module `Warehouse` (`Modules/Warehouse`)
- [x] Thiết lập cấu trúc thư mục (Entities, Database, Http, etc.)

## 2. Cơ sở dữ liệu (Database)

- [x] Bảng `warehouses`: Lưu danh sách kho (Tên, Mã, Địa chỉ, Loại).
- [x] Bảng `warehouse_product_stock`: Lưu tồn kho theo từng kho (`product_id`, `warehouse_id`, `quantity`).
- [ ] Bảng `stock_movements`: (Sử dụng bảng global `stock_movements` hoặc tạo mới nếu cần biệt lập).

## 3. Models & Entities

- [x] `Warehouse` Model.
- [x] `WarehouseProductStock` Model (Pivot).
- [x] Quan hệ (Relationships): Product `hasMany` WarehouseProductStock.

## 4. Logic Nghiệp vụ (Business Logic)

- [x] CRUD Warehouse (Quản lý kho).
- [x] Logic điều chỉnh tồn kho (Stock Adjustment) theo kho.
- [ ] Logic chuyển kho (Transfer).

## 5. Tích hợp (Integration)

- [ ] Tích hợp vào Purchase (Nhập hàng -> Chọn kho).
- [ ] Tích hợp vào Sales (Bán hàng -> Trừ kho).

---

**Trạng thái hiện tại:** Đã hoàn thành CRUD Warehouse, Stock Adjustment và Transfer. Đang chuẩn bị tích hợp.
