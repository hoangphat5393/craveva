# MAOLIN Import Mapping Template

**Mục lục MAOLIN:** [`MAOLIN_BUSINESS.md`](MAOLIN_BUSINESS.md) — mở file đó nếu không biết bắt đầu từ đâu.

> Bản gộp nghiệp vụ nằm ở [`MAOLIN_BUSINESS.md`](MAOLIN_BUSINESS.md). File này chỉ giữ bảng mapping chi tiết.

Tài liệu này dùng để map cột khi import, theo bộ file trong `PROJECT MAOLIN New/`. Mục tiêu: import nhanh, đúng core multi-warehouse, giảm phụ thuộc custom fields.

---

## 0. Nguyên Tắc Chung

Import theo thứ tự:

1. Warehouse master (nếu chưa có đủ)
2. Client
3. Product
4. Pricing
5. Inventory

Khóa chính:

- Client: `client_code`
- Product: `sku`
- Warehouse: `warehouse_code` (name chỉ fallback)

Inventory ưu tiên lưu vào core:

- `warehouse_id` resolve từ `warehouse_code/name`
- `batch_number`
- `manufacturing_date`
- `expiration_date`

---

## 1. Workbook `Quote, unit price, inventory.xlsx`

| Sheet | Mục đích | Map vào hệ thống |
| ----- | -------- | ---------------- |
| `報價單匯出` | Báo giá / quotation export | Chưa có importer chuẩn; chỉ tham chiếu hoặc cần adapter riêng. |
| `產品價格表` | Bảng giá theo SKU | Cập nhật giá sản phẩm: `products.price`, `wholesale_price`, `price_per_box`, `employee_price`. |
| `產品庫存表` | Tồn theo kho/lô | Map sang Inventory import: `sku`, `warehouse_code`, `batch_number`, `expiration_date`, `quantity`. |

Cách dùng với màn import hiện tại:

1. Tách sheet cần dùng thành một file `.xlsx` hoặc `.csv` riêng.
2. Giá: import/update product theo SKU và cột giá.
3. Tồn: dùng sheet `產品庫存表`, ưu tiên cột `warehouse_code`.

---

## 2. CLIENT — `Craveva customer.xlsx`

### 2.1 Mapping Cột

| Cột file | Import field id | Bắt buộc | Ghi chú |
| -------- | --------------- | -------- | ------- |
| 客戶代號 | `client_code` | Nên có | Khóa update/upsert |
| 客戶簡稱 | `name` | Bắt buộc | Tên hiển thị |
| 統一編號 | `gst_number` | Tùy chọn | Tax ID |
| 送貨地址 | `address` | Tùy chọn | Địa chỉ |
| TEL_NO(一) | `mobile` | Tùy chọn | SĐT chính |
| TEL_NO(二) | `company_phone` | Tùy chọn | SĐT văn phòng |
| 業務員 | `salesperson` | Tùy chọn | Custom field |
| 業務助理名稱 | `sales_assistant_name` | Tùy chọn | Custom field |
| 客戶(集團)分級 | `customer_grade` | Tùy chọn | Core DB: `client_details.customer_grade` |
| 通路別 | `channel_type` | Tùy chọn | Core DB: `client_details.channel_type` |
| 型態別 | `business_type` | Tùy chọn | Core DB: `client_details.business_type` |
| 最近交易 | `last_transaction_at` | Tùy chọn | Custom field date nếu PM cần |
| 交易條件 | `payment_terms` | Tùy chọn | Core DB: `client_details.payment_terms` |
| 歇業日期 | `business_closure_date` | Tùy chọn | Core DB; có giá trị thì client inactive |
| 指定庫別代碼 | `designated_warehouse_code` | Nên có | Map sang `default_warehouse_id` |
| 指定庫別名稱 | `designated_warehouse_name` | Fallback | Dùng nếu không có code |

### 2.2 Rule Map Kho Ưu Tiên Client

- Tìm kho theo `designated_warehouse_code` trước.
- Nếu không có, fallback `designated_warehouse_name`.
- Nếu vẫn không khớp: để `default_warehouse_id = null`, ghi log dòng lỗi để đối soát.

### 2.3 Nếu Bỏ Hết Client Custom Fields

Vẫn import được phần core:

- `client_code`
- `name`
- `gst_number`
- `address`
- `mobile`
- `company_phone`
- `default_warehouse_id`
- `customer_grade`
- `channel_type`
- `business_type`
- `payment_terms`
- `business_closure_date`

Không còn chỗ lưu nếu bỏ hết Client custom fields: các cột metadata/BI như `salesperson`, `department`, `sales_assistant_name`, `last_transaction_at`, `region`, trừ khi PM tạo lại custom field từ UI.

---

## 3. PRODUCT — `Craveva product.xlsx`

| Cột file | Import field id | Bắt buộc | Ghi chú |
| -------- | --------------- | -------- | ------- |
| 品號 | `sku` | Bắt buộc | Khóa sản phẩm |
| 品名 | `name` | Bắt buộc | Tên sản phẩm |
| 規格 | `specification` | Tùy chọn | Cột core |
| 庫存單位 | `unit_type` | Tùy chọn | Tự động map/create unit |
| 商品級別 | `product_grade` | Tùy chọn | Cột core |
| 品牌類別 | `brand` | Tùy chọn | Cột core |
| 保存天數 | `shelf_life_days` | Tùy chọn | Cột core |
| 備貨型態 | `inventory_type` | Tùy chọn | Cột core |
| 儲存溫層 | `storage_condition` | Tùy chọn | Cột core |

---

## 4. PRICING — Sheet `產品價格表`

| Cột file | Dịch nghĩa | Đi vào hệ thống |
| -------- | ---------- | ---------------- |
| 品號 | SKU | Tìm product theo `sku` |
| 標準售價 | Standard price | `products.price` |
| 中盤價 | Wholesale | `products.wholesale_price` |
| 成箱價 | Price per box | `products.price_per_box` |
| 員工價 | Employee price | `products.employee_price` |

---

## 5. INVENTORY — `產品庫存表` / `庫存明細總表`

### 5.1 Mapping Cột Tới Importer

| Cột file | Import field id | Bắt buộc | Ghi chú |
| -------- | --------------- | -------- | ------- |
| 品號 / 產品料號 | `sku` | Bắt buộc | Khóa product |
| 品名 / 產品名稱 | `product_name` | Tùy chọn | Đối soát |
| 庫別 | `warehouse_code` | Nên có | Resolve kho ưu tiên |
| 庫別名稱 | `warehouse_name` | Fallback | Dùng khi thiếu code |
| 批號 | `batch_number` | Nên có | Cột DB |
| 製造日期 | `manufacturing_date` | Tùy chọn | Cột DB |
| 有效日期 | `expiration_date` | Nên có | Cột DB |
| 期末庫存 / 庫存量 | `ending_inventory` hoặc `quantity` | Bắt buộc 1 trong 2 | Snapshot số lượng |
| 單位 | `unit` | Tùy chọn | Đối soát đơn vị |
| 規格 | `specification` | Tùy chọn | Đối soát |

### 5.2 Rule Resolve Kho

Hệ thống resolve:

1. `warehouse_code` (ưu tiên)
2. `warehouse_name` (fallback)

Nếu không resolve được: bỏ qua dòng và ghi log, không cho ghi sai kho.

### 5.3 Các Cột Không Cần Map Vào Custom Fields Nữa

- `beginning_inventory`
- `inbound_quantity`
- `outbound_quantity`
- `reserved_quantity`
- `near_expiry_status`
- `recent_inbound_date`
- `batch_recent_inbound_date`
- `beginning_package_inventory`
- `packaging_inbound_quantity`
- `packaging_outbound_quantity`
- `closing_code` nếu không có quy trình nghiệp vụ

---

## 6. Checklist Trước Khi Chạy Import Thật

- [ ] Đã migrate DB mới nhất.
- [ ] Đã có warehouse master đầy đủ code + name.
- [ ] Đã map đúng 3 cột quan trọng inventory: `warehouse_code`, `warehouse_name`, `batch_number`.
- [ ] Đã test trial 20-50 dòng.
- [ ] Đã đối soát 5 SKU ở 2 kho:
    - `purchase_stock_adjustments.warehouse_id`
    - `purchase_stock_adjustments.batch_number`
    - `warehouse_product_batches.quantity`

---

## 7. Checklist Sau Import

- [ ] Không có dòng lỗi resolve warehouse.
- [ ] Tồn kho theo kho khớp giữa UI (`/warehouse-stock`) và DB.
- [ ] Client có `default_warehouse_id` nếu file có designated warehouse.
- [ ] Không cần dùng lại custom fields `warehouse_code/name` trên form Inventory.
