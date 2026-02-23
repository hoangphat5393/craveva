# Gap Analysis: Import Data vs. Current System

## 1. Overview

The provided Excel file `Quotation.Unit Price.Inventory import.xlsx` contains three key sheets:

1.  **Quotation (Sales/CRM):** Customer-specific tiered pricing and sales quotations.
2.  **Standard Pricing (Product Master):** Multi-tier pricing (Standard, Distributor, Carton, Employee).
3.  **Inventory (Stock):** Multi-warehouse stock levels with batch numbers and expiry dates.

The current system (Craveva ERP - Purchase Module focus) lacks several core components to support this data structure.

## 2. Detailed Gap Analysis

### A. Inventory & Warehouse Management (Sheet 3: `產品庫存表 Inventory`)

| Excel Field                     | Current System Status | Gap / Missing Feature                                                           |
| :------------------------------ | :-------------------- | :------------------------------------------------------------------------------ |
| **Warehouse Code (`庫別`)**     | **Missing**           | The system operates on a Single Warehouse model. No `warehouses` table exists.  |
| **Warehouse Name (`庫別名稱`)** | **Missing**           | No `warehouses` table to store names.                                           |
| **Batch Number (`批號`)**       | **Missing**           | `purchase_stock_adjustments` only tracks quantity. No column for Batch/Lot ID.  |
| **Expiry Date (`有效日期`)**    | **Missing**           | No column for Expiry Date in stock adjustments or product inventory.            |
| **Stock per Warehouse**         | **Missing**           | Cannot separate stock by location (e.g., "Main Warehouse" vs. "Damaged Goods"). |

**Required Actions:**

1.  Create `Warehouse` Module/Table (`id`, `name`, `code`, `location`).
2.  Update `purchase_stock_adjustments` to include `warehouse_id`, `batch_number`, `expiry_date`.
3.  Update `products` or a new `product_stock` table to track Quantity _per Warehouse_.

### B. Pricing Structure (Sheet 2: `產品價格表Standard Pricing`)

| Excel Field                      | Current System Status     | Gap / Missing Feature                                                      |
| :------------------------------- | :------------------------ | :------------------------------------------------------------------------- |
| **Standard Price (`標準售價`)**  | Exists (`products.price`) | Compatible.                                                                |
| **Distributor Price (`中盤價`)** | **Missing**               | Only one selling price is supported.                                       |
| **Carton Price (`成箱價`)**      | **Missing**               | No support for unit-based pricing tiers (Price per Box vs. Price per Pcs). |
| **Employee Price (`員工價`)**    | **Missing**               | No support for customer-group specific pricing.                            |

**Required Actions:**

1.  Create `product_prices` table (one-to-many with products) OR add columns to `products` (`price_distributor`, `price_employee`, etc.).
2.  Implement logic to select price based on Customer Group or Context.

### C. Sales & Quotation (Sheet 1: `報價單匯出 Tier Pricing`)

| Excel Field            | Current System Status | Gap / Missing Feature                                                                           |
| :--------------------- | :-------------------- | :---------------------------------------------------------------------------------------------- |
| **Customer Code/Name** | **Partial**           | `Clients` module exists, but linkage to specific pricing tiers is missing.                      |
| **Quotation Number**   | **Partial**           | `Estimates` (Proposals) exist in core, but may not support the "Tiered Pricing" logic directly. |
| **Sales Person**       | Exists (`users`)      | Compatible.                                                                                     |
| **Tier Pricing Logic** | **Missing**           | The logic "Customer A gets Distributor Price" is missing.                                       |

**Required Actions:**

1.  Ensure `Sales` or `Client` module is active.
2.  Implement "Price List" or "Customer Group" feature to map Customers to specific Price Tiers (e.g., Customer A -> Distributor Price).

## 3. Summary of Missing Modules/Tables

To fully support importing this file, the following are needed:

1.  **Module: Warehouse Management** (Crucial)
    -   Table: `warehouses`
    -   Relation: `product_warehouse_stock` (pivot)
2.  **Feature: Advanced Pricing**
    -   Table: `product_prices` (or extra columns in `products`)
    -   Feature: Customer Groups (to map to Price Tiers)
3.  **Feature: Batch & Expiry Tracking**
    -   Columns: Add `batch_number`, `expiry_date` to `purchase_stock_adjustments`.

## 4. Import Strategy (Temporary Workaround)

If we must import _now_ without major code changes:

-   **Inventory:** Sum up all stock from all warehouses in Excel -> Import as single total to "Default Warehouse". _Ignore Batch/Expiry._
-   **Price:** Import `Standard Price` to `products.price`. _Ignore other tiers._
-   **Quotation:** Cannot be imported effectively into `Purchase` module (as it is Sales data). Should be imported into `Estimates` if needed.

## 5. Hiện trạng thiếu (Tóm tắt)

-   Đa kho (Warehouses): Thiếu bảng/khái niệm kho và phân tách tồn kho theo từng kho.
-   Theo dõi lô & hạn dùng: Thiếu `batch_number`, `expiry_date` trong dòng điều chỉnh tồn kho.
-   Đa giá (Tier Pricing): Thiếu các mức giá: trung gian (distributor), nguyên thùng (carton), nhân viên; chưa có ánh xạ nhóm khách hàng → mức giá.
-   Liên kết báo giá (Sales/Quotation): Thiếu logic áp dụng mức giá theo khách hàng/nhóm trong quá trình tạo báo giá/đơn hàng.

## 6. Ưu tiên triển khai (Roadmap đề xuất)

### P1. Nền tảng Đa Kho (ưu tiên cao)

-   Tạo bảng `warehouses` (code, name, location, company_id).
-   Thêm `warehouse_id` vào `purchase_stock_adjustments` để ghi nhận kho nguồn.
-   Tạo bảng/pivot `product_warehouse_stock` để lưu tồn kho theo từng kho.

### P2. Theo dõi Lô & Hạn Dùng (ưu tiên cao)

-   Thêm cột `batch_number`, `expiry_date` vào `purchase_stock_adjustments`.
-   Chuẩn hóa import Inventory: map SKU + mã kho + số lô + hạn dùng.

### P3. Đa Giá Cơ Bản (ưu tiên trung bình → cao)

-   Tạo `product_prices` (hoặc thêm cột vào `products`) cho các mức giá: `standard`, `distributor`, `carton`, `employee`.
-   Thêm khái niệm "Customer Group" và ánh xạ nhóm → mức giá.

### P4. B2B Pricing (Corporate + Volume) (ưu tiên trung bình)

-   Áp dụng đề xuất từ B2B Pricing: pricing tiers, corporate pricing, volume discount.
-   Xây dựng `PricingService` theo thứ tự áp dụng giá (resolution order).

### P5. Tích hợp Import theo cấu trúc mới (ưu tiên trung bình)

-   Điều chỉnh import để hỗ trợ đa kho và đa giá.
-   Chuẩn hóa mapper từ các sheet Excel vào bảng mới.

### P6. (Tuỳ chọn) Warehouse‑Aware Pricing

-   Mở rộng PricingService nhận `warehouseId` để cho phép giá theo kho nếu nghiệp vụ yêu cầu.

## 7. Khuyến nghị triển khai theo giai đoạn

1. P1 + P2: Hoàn thiện dữ liệu nền (đa kho, lô/hạn dùng) để đảm bảo tồn kho chính xác.
2. P3: Bổ sung đa giá cơ bản để phản ánh bảng giá từ Excel.
3. P4: Áp dụng corporate/volume pricing nhằm tối ưu chính sách bán B2B.
4. P5: Cập nhật luồng import tương thích kiến trúc mới.
5. P6: Cân nhắc giá theo kho nếu cần sự khác biệt giữa kho.

---

## 8. Kế hoạch triển khai chi tiết — Multi Warehouse & Price Tier

### 8.1 Mục tiêu kinh doanh

-   Quản lý tồn kho theo từng kho, từng lô, và hạn dùng để phản ánh thực tế vận hành.
-   Thiết lập nhiều mức giá bán (tier pricing) và áp dụng theo nhóm khách/công ty/khối lượng.
-   Chuẩn hóa import dữ liệu từ Excel vào mô hình mới, giảm sai sót do trùng tên và thiếu kho.

### 8.2 Phạm vi (Scope)

-   Module áp dụng: `Modules/Purchase` (tuân thủ Module-Only Development).
-   Loại thay đổi: Migrations mới, bổ sung quan hệ Models, mở rộng import, tối thiểu hóa thay đổi UI.
-   Không sửa migrations cũ; không đụng tới thư mục cấm.

### 8.3 Thiết kế dữ liệu & Migrations (tuân thủ Quy tắc 4)

-   Tạo mới trong: `Modules/Purchase/Database/Migrations/`

1. Warehouses

    - Table: `purchase_warehouses`
    - Columns: `id`, `company_id`, `code` (unique), `name`, `location` (nullable), `status` (active/inactive), `timestamps`
    - Quan hệ: Company → Warehouses (1:N)

2. Stock per Warehouse (Pivot tồn kho)

    - Table: `purchase_product_warehouse_stock`
    - Columns: `id`, `company_id`, `product_id`, `warehouse_id`, `on_hand` (double), `reserved` (double, default 0), `updated_at`
    - Mục đích: Lưu tồn kho hiện tại theo từng kho.

3. Stock Adjustments mở rộng

    - Bổ sung cột vào `purchase_stock_adjustments`: `warehouse_id` (FK → `purchase_warehouses`), `batch_number` (string, nullable), `expiry_date` (date, nullable)
    - Không sửa dữ liệu cũ; thêm cột mới an toàn.

4. Price Tiers cơ bản

    - Table: `purchase_product_prices`
    - Columns: `id`, `company_id`, `product_id`, `price_type` (enum: `standard`, `distributor`, `carton`, `employee`), `price` (double), `valid_from` (date, nullable), `valid_to` (date, nullable), `timestamps`
    - Mục đích: Lưu nhiều mức giá cho mỗi sản phẩm.

5. Customer Groups → Price Mapping
    - Table: `purchase_customer_groups`
    - Columns: `id`, `company_id`, `name`, `description`, `timestamps`
    - Table: `purchase_customer_group_members`
    - Columns: `id`, `company_id`, `group_id`, `client_id`, `timestamps`
    - Table: `purchase_customer_group_price_rules`
    - Columns: `id`, `company_id`, `group_id`, `price_type` (enum như trên), `timestamps`
    - Mục đích: Gán nhóm khách → loại giá.

### 8.4 Mô hình & Quan hệ (Models)

-   `PurchaseWarehouse` (table `purchase_warehouses`)
-   Mở rộng `PurchaseStockAdjustment` thêm `warehouse(): BelongsTo`
-   `PurchaseProductWarehouseStock` (table `purchase_product_warehouse_stock`)
-   `PurchaseProductPrice` (table `purchase_product_prices`)
-   `PurchaseCustomerGroup`, `PurchaseCustomerGroupMember`, `PurchaseCustomerGroupPriceRule`

### 8.5 Luồng Import (Excel)

-   Inventory Import:
    -   Bổ sung cột bắt buộc: `sku`, `warehouse_code`, `batch_number` (optional), `expiry_date` (optional), `quantity`.
    -   Ánh xạ `warehouse_code` → `purchase_warehouses.code`; nếu không tồn tại, từ chối dòng hoặc log cảnh báo.
    -   Cập nhật `purchase_product_warehouse_stock.on_hand` theo từng kho.
-   Pricing Import:
    -   Bổ sung import từ sheet Price List: tạo bản ghi `purchase_product_prices` với `price_type` tương ứng.
    -   Thiết lập `Customer Group Price Rules` theo cấu hình kinh doanh.

### 8.6 UI (Blade) — Thay đổi tối thiểu (tuân thủ Quy tắc 5)

-   Thêm filter chọn Kho trong trang Inventory để xem tồn theo kho.
-   Hiển thị mức giá theo nhóm tại trang Product chi tiết (nếu user có quyền xem giá nhóm).
-   Không đưa logic vào Blade; logic ở Controller/Service.

### 8.7 Dịch vụ tính giá (PricingService) — Resolution Order

-   Thứ tự áp dụng:
    1. Customer Group → Price Type
    2. Product Prices (theo `price_type`)
    3. Volume Discount (P4 mở rộng)
    4. Base Product Price (fallback)
-   Mở rộng phương thức nhận `warehouseId` nếu cần giá theo kho.

### 8.8 User Stories & Tiêu chí chấp nhận (Acceptance Criteria)

-   Multi Warehouse
    -   Given có SKU và mã kho hợp lệ, When import tồn kho, Then hệ thống cập nhật tồn cho đúng `warehouse_id` và hiển thị tổng theo kho.
    -   Given có lô và hạn dùng, When import với `batch_number`, `expiry_date`, Then hệ thống lưu kèm theo trong điều chỉnh tồn.
-   Price Tiers
    -   Given khách thuộc nhóm `Distributor`, When xem giá sản phẩm, Then hiển thị giá `price_type = distributor`.
    -   Given không thuộc nhóm nào, When xem giá, Then dùng giá `standard`.
    -   Given số lượng đặt vượt ngưỡng volume, When tạo đơn, Then áp dụng giảm giá volume (sau P4).

### 8.9 Kế hoạch thời gian (Phases)

-   P1 (Tuần 1–2): Warehouses + stock per warehouse + cột mới trong stock adjustments.
-   P2 (Tuần 3): Batch & Expiry + mở rộng import Inventory.
-   P3 (Tuần 4–5): Product Prices + Customer Groups + Price Rules.
-   P4 (Tuần 6): Volume Discount cơ bản và tích hợp PricingService.
-   P5 (Tuần 7): Cập nhật Import Price List, chuẩn hóa mapping.
-   P6 (Tuần 8): UI filters (Warehouse), hiển thị mức giá theo nhóm.

### 8.10 Rủi ro & Giảm thiểu

-   Đồng bộ kho: Thiếu mã kho trong Excel → từ chối hoặc tạo mapping trước import.
-   Trùng SKU: Bắt buộc dùng `sku` thay vì tên khi import.
-   Khối lượng dữ liệu lớn: Dùng chunk import (maatwebsite/excel) và giao dịch DB.
-   Quyền truy cập: Kiểm tra permission trước hiển thị giá theo nhóm.

### 8.11 KPI thành công

-   100% dòng tồn kho được gán kho hợp lệ.
-   Tồn kho theo kho khớp với báo cáo Excel.
-   Giá hiển thị đúng theo nhóm khách, sai lệch < 1% so với bảng giá.
-   Thời gian import < 10 phút cho 10k dòng.
