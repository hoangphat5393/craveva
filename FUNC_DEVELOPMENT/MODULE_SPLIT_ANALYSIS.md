# Phân Tích Kiến Trúc Module: Pricing, Warehouse & Delivery

Tài liệu này phân tích ưu/nhược điểm của việc tách riêng 3 module: **Pricing (Định giá)**, **Warehouse (Kho)**, và **Delivery (Vận chuyển)** so với việc gộp chung, dựa trên bối cảnh dự án B2B của Miao Lin Foods.

---

## 1. Khuyến nghị Kiến trúc (Architecture Recommendation)

**Kết luận:** Nên tách thành **3 Modules riêng biệt** nhưng có sự phụ thuộc (Dependencies) rõ ràng.

### Cấu trúc đề xuất:
1.  `Modules/Pricing`: Quản lý Tier, Volume Discount, Corporate Pricing.
2.  `Modules/Warehouse`: Quản lý Multi-Warehouse, Batch/Expiry (Tồn kho).
3.  `Modules/Delivery`: Quản lý Shipping Rates, Shipping Zones, Phí vận chuyển.

---

## 2. Phân tích chi tiết (Pros & Cons)

### Phương án A: Tách 3 Module riêng biệt (Khuyên dùng)

| Tiêu chí | Ưu điểm | Nhược điểm |
| :--- | :--- | :--- |
| **Bảo trì (Maintainability)** | Code sạch, dễ tìm lỗi. Logic định giá không bị lẫn với logic trừ kho hay tính ship. | Cần khai báo dependency kỹ (VD: Delivery cần biết Warehouse nào để tính phí). |
| **Mở rộng (Scalability)** | Nếu sau này muốn nâng cấp `Delivery` lên `Logistics TMS`, chỉ cần sửa module Delivery, không ảnh hưởng Pricing. | Số lượng file và thư mục nhiều hơn ban đầu. |
| **Tái sử dụng (Reusability)** | Có thể bật/tắt từng module. Ví dụ: Bán cho khách hàng khác chỉ cần Pricing mà không cần Delivery. | Cấu hình Module Service Provider phức tạp hơn chút. |
| **Phân quyền (Permission)** | Dễ dàng phân quyền: Nhân viên kho chỉ thấy `Warehouse`, Sales chỉ thấy `Pricing`. | |

### Phương án B: Gộp chung (Modules/B2B_Core)

| Tiêu chí | Ưu điểm | Nhược điểm |
| :--- | :--- | :--- |
| **Tốc độ phát triển đầu dự án** | Nhanh hơn do ít phải tạo file/folder mới. | Code nhanh chóng trở nên rối rắm (Spaghetti code) khi logic phức tạp lên. |
| **Kết nối dữ liệu** | Gọi hàm trực tiếp dễ dàng, không cần qua Service Layer giữa các module. | Khó tách rời sau này. Nếu muốn sửa logic Kho, rất dễ làm hỏng logic Giá. |
| **Quản lý** | Ít thư mục hơn. | Vi phạm nguyên tắc "Single Responsibility" (Đơn nhiệm). |

---

## 3. Mối quan hệ giữa các Module (Dependency Graph)

Mặc dù tách riêng, chúng vẫn cần giao tiếp với nhau:

1.  **Pricing Module**: Độc lập nhất. Input là Product/Customer -> Output là Giá.
2.  **Warehouse Module**: Độc lập. Input là Product -> Output là Tồn kho.
3.  **Delivery Module**: Phụ thuộc vào Warehouse.
    *   *Logic:* Để tính phí ship, cần biết hàng xuất từ **Kho nào** (Warehouse Module) đi đến **Địa chỉ khách** (Core App).

```mermaid
graph TD
    A[Core App (Product/Client)] --> B(Pricing Module)
    A --> C(Warehouse Module)
    C --> D(Delivery Module)
    A --> D
```

## 4. Lộ trình thực hiện (Action Plan)

1.  **Modules/Pricing:** Đã có (đang phát triển Tier/Volume).
2.  **Modules/Warehouse:**
    *   Tạo mới.
    *   Chuyển logic tồn kho (nếu có) từ Core sang đây.
    *   Thêm bảng `warehouses`, `warehouse_products`, `batches`.
3.  **Modules/Delivery:**
    *   Tạo mới.
    *   Thêm bảng `shipping_zones`, `shipping_rates`.
    *   API tính phí gọi sang `Warehouse` để lấy địa chỉ kho xuất phát.

---

**Lưu ý quan trọng:**
Việc tách module phù hợp với kiến trúc Modular của Laravel (nwidart/laravel-modules) mà dự án đang sử dụng. Nó giúp hệ thống bền vững và dễ bán lại (SaaS) cho các khách hàng có nhu cầu khác nhau.
