# MAOLIN - Chinese Columns Translation & System Mapping (Import Readiness)

**Scope files requested:**

- `PROJECT MAOLIN New/Craveva customer.xlsx`
- `PROJECT MAOLIN New/Craveva product.xlsx`
- `PROJECT MAOLIN New/Quote, unit price, inventory.xlsx`
- `PROJECT MAOLIN New/Craveva full inventory.xlsx`

**Checked against current import structure:**

- `app/Imports/ClientImport.php`
- `app/Imports/ProductImport.php`
- `Modules/Purchase/Imports/InventoryImport.php`

---

## 1) Sheet structure found

## 1.1 Craveva customer.xlsx

- Sheet: `苗實清單資料 | Seedling inventory dat`

## 1.2 Craveva product.xlsx

- Sheet: `商品 | merchandise`
- Sheet: `商品價格 | commodity prices`

## 1.3 Quote, unit price, inventory.xlsx

- Sheet: `報價單匯出` (Quotation export)
- Sheet: `產品價格表` (Product price table)
- Sheet: `產品庫存表` (Product inventory table)

## 1.4 Craveva full inventory.xlsx

- Sheet: `庫存明細總表` (Batch inventory summary)

---

## 2) Data dictionary + mapping to current system

## 2.1 Customer file (`Craveva customer.xlsx`)

| Chinese column | English column name (recommended) | Purpose                                  | Current mapping in system    | Status              |
| -------------- | --------------------------------- | ---------------------------------------- | ---------------------------- | ------------------- |
| 客戶代號       | `client_code`                     | Unique customer code                     | `client_details.client_code` | Supported           |
| 客戶簡稱       | `client_name`                     | Customer display name                    | `users.name`                 | Supported           |
| 統一編號       | `tax_id`                          | Tax identifier                           | `client_details.gst_number`  | Supported           |
| 業務員         | `salesperson`                     | Owner/sales rep                          | Client custom field          | Supported           |
| 業務助理名稱   | `sales_assistant_name`            | Sales assistant                          | Client custom field          | Supported           |
| 客戶(集團)分級 | `customer_grade`                  | Customer grade/segment                   | Client custom field          | Supported           |
| 通路別         | `channel_type`                    | Sales channel type                       | Client custom field          | Supported           |
| 型態別         | `business_type`                   | Business type                            | Client custom field          | Supported           |
| 地區別         | `region`                          | Region/geography                         | Not mapped yet               | **Missing mapping** |
| 送貨地址       | `shipping_address`                | Delivery address                         | `client_details.address`     | Supported           |
| TEL_NO(一)     | `phone_1`                         | Contact phone 1                          | `users.mobile`               | Supported           |
| TEL_NO(二)     | `phone_2`                         | Contact phone 2 / office                 | `client_details.office`      | Supported           |
| 交易條件       | `payment_terms`                   | Payment terms                            | Client custom field          | Supported           |
| 最近交易       | `last_transaction_at`             | Last transaction date                    | Client custom field          | Supported           |
| 歇業日期       | `business_closure_date`           | Closure date                             | Client custom field          | Supported           |
| 指定庫別名稱   | `designated_warehouse_name`       | Preferred/default warehouse for customer | Not mapped yet               | **Missing mapping** |

**Important missing columns for better import quality:**

- `email` (currently not in file)
- `department` (old file had it, new file does not)

---

## 2.2 Product file (`Craveva product.xlsx` -> sheet `商品 | merchandise`)

| Chinese column | English column name (recommended) | Purpose               | Current mapping in system           | Status              |
| -------------- | --------------------------------- | --------------------- | ----------------------------------- | ------------------- |
| 品號           | `sku`                             | Product code / key    | `products.sku`                      | Supported           |
| 品名           | `product_name`                    | Product name          | `products.name`                     | Supported           |
| 規格           | `specification`                   | Product specification | `products.specification`            | Supported           |
| 庫存單位       | `unit_type`                       | Inventory unit        | `products.unit_id` via `unit_types` | Supported           |
| 商品級別       | `product_grade`                   | Product grade         | `products.product_grade`            | Supported           |
| 品牌類別       | `brand`                           | Brand                 | `products.brand`                    | Supported           |
| 保存天數       | `shelf_life_days`                 | Shelf-life days       | `products.shelf_life_days`          | Supported           |
| 備貨型態       | `inventory_type`                  | Stocking type         | `products.inventory_type`           | Supported           |
| 儲存溫層       | `storage_condition`               | Storage condition     | `products.storage_condition`        | Supported           |
| 失效日期       | `expiry_date`                     | Expiry date           | No direct field in Product import   | **Missing mapping** |

**Important missing columns for current product import job:**

- `standard_price` or `price` (critical for import)
- `price_per_box`
- `product_source`
- `product_category`, `product_sub_category`
- `status` (active/inactive)

---

## 2.3 Price table (`Quote, unit price, inventory.xlsx` -> sheet `產品價格表`)

| Chinese column | English column name (recommended) | Purpose         | Current mapping in system         | Status             |
| -------------- | --------------------------------- | --------------- | --------------------------------- | ------------------ |
| 品號           | `sku`                             | Product key     | Product/Pricing lookup            | Supported base key |
| 品名           | `product_name`                    | Product name    | Optional for verification         | Supported          |
| 備貨型態       | `inventory_type`                  | Stocking type   | `products.inventory_type`         | Supported          |
| 標準售價       | `standard_price`                  | Standard price  | `products.price` / product import | Supported          |
| 中盤價         | `wholesale_price`                 | Wholesale price | `products.wholesale_price`        | Supported          |
| 成箱價         | `price_per_box`                   | Price per box   | `products.price_per_box`          | Supported          |
| 員工價         | `employee_price`                  | Employee price  | `products.employee_price`         | Supported          |

**Note:** This sheet is the best source to fill pricing gaps missing in `Craveva product.xlsx`.

---

## 2.4 Inventory table (`Quote, unit price, inventory.xlsx` -> sheet `產品庫存表`)

| Chinese column | English column name (recommended) | Purpose                      | Current mapping in system              | Status              |
| -------------- | --------------------------------- | ---------------------------- | -------------------------------------- | ------------------- |
| 品號           | `sku`                             | Product key                  | Inventory import supports `sku`        | Supported           |
| 品名           | `product_name`                    | Product name                 | Inventory import supports              | Supported           |
| 規格           | `specification`                   | Product spec                 | Inventory import supports              | Supported           |
| 單位           | `unit`                            | Unit                         | Inventory import supports `unit`       | Supported           |
| 小單位         | `sub_unit`                        | Sub-unit                     | No direct field                        | Missing mapping     |
| 包裝單位       | `packaging_unit`                  | Packaging unit               | No direct field                        | Missing mapping     |
| 批號           | `batch_number`                    | Batch/lot number             | Not in default inventory import fields | **Missing mapping** |
| 有效日期       | `expiration_date`                 | Expiration date              | `expiration_date`                      | Supported           |
| 製造日期       | `manufacturing_date`              | Manufacturing date           | `manufacturing_date`                   | Supported           |
| 結案碼         | `close_status_code`               | Closed/open status per batch | No direct field                        | Missing mapping     |
| 庫別           | `warehouse_code`                  | Warehouse code               | Not in default fields                  | **Missing mapping** |
| 庫別名稱       | `warehouse_name`                  | Warehouse name               | Not in default fields                  | **Missing mapping** |
| 期初庫存       | `opening_stock`                   | Opening qty                  | No direct field                        | Missing mapping     |
| 本期入庫       | `stock_in_period`                 | Inbound qty                  | No direct field                        | Missing mapping     |
| 本期出庫       | `stock_out_period`                | Outbound qty                 | No direct field                        | Missing mapping     |
| 期末庫存       | `ending_stock`                    | Closing qty                  | Can map to `ending_inventory`          | Partially supported |
| 期初包裝庫存   | `opening_pack_stock`              | Opening pack qty             | No direct field                        | Missing mapping     |
| 本期包裝入庫   | `pack_in_period`                  | Pack inbound qty             | No direct field                        | Missing mapping     |
| 本期包裝出庫   | `pack_out_period`                 | Pack outbound qty            | No direct field                        | Missing mapping     |
| 期末包裝庫存   | `ending_pack_stock`               | Closing pack qty             | No direct field                        | Missing mapping     |
| 最近入庫日     | `last_inbound_date`               | Last inbound date            | No direct field                        | Missing mapping     |
| 批號最近入庫日 | `batch_last_inbound_date`         | Last inbound date by batch   | No direct field                        | Missing mapping     |

---

## 2.5 Full inventory summary (`Craveva full inventory.xlsx`)

| Chinese column | English column name (recommended) | Purpose              | Current mapping in system                | Status              |
| -------------- | --------------------------------- | -------------------- | ---------------------------------------- | ------------------- |
| 產品料號       | `sku`                             | Product key          | Inventory import supports                | Supported           |
| 產品名稱       | `product_name`                    | Product name         | Inventory import supports                | Supported           |
| 有效日期(C)    | `expiration_date`                 | Expiration date      | `expiration_date`                        | Supported           |
| 批號           | `batch_number`                    | Batch/lot            | Not in default inventory import fields   | **Missing mapping** |
| 庫存量         | `stock_qty`                       | Current quantity     | Can map to `ending_inventory`/`quantity` | Partially supported |
| 剩餘有效天數   | `remaining_shelf_life_days`       | Remaining valid days | No direct field                          | Missing mapping     |
| 庫別名稱       | `warehouse_name`                  | Warehouse name       | Not in default fields                    | **Missing mapping** |

---

## 3) Missing columns vs current import modules (important)

## 3.1 Client import gaps

- Missing in file: `email`, `department`
- New fields not yet mapped in system import:
    - `region`
    - `designated_warehouse_name`

## 3.2 Product import gaps

- `Craveva product.xlsx` missing price-related columns required by current product import flow:
    - `standard_price` or `price` (critical)
    - `price_per_box`
    - `product_source`
- Missing category fields for direct category import:
    - `product_category`, `product_sub_category`

## 3.3 Inventory import gaps

Current `InventoryImport::fields()` does **not** include:

- `warehouse_code` / `warehouse_name`
- `batch_number`
- period movement fields (`opening_stock`, `stock_in_period`, `stock_out_period`, etc.)

So importing `產品庫存表` and `庫存明細總表` currently needs custom mapping extension.

---

## 4) What modules/enhancements are needed to import all files

## 4.1 Already existing modules (can reuse)

- Product module
- Client module
- Purchase/Inventory module
- Warehouse module
- Pricing module

## 4.2 Needed additions (to import these files fully)

1. **Inventory Import Extension (required)**
    - Add field mapping for `warehouse_code/warehouse_name`, `batch_number`, `remaining_shelf_life_days`.
    - Add warehouse resolution (name/code -> `warehouse_id`).

2. **Batch/Lot Inventory Mapping Layer (required)**
    - Map by `(company_id, warehouse_id, sku, batch_number, expiration_date)`.
    - Prevent wrong merge between different batches.

3. **Pricing Sheet Import Adapter (recommended)**
    - Import `產品價格表` to update `standard_price`, `wholesale_price`, `price_per_box`, `employee_price`.

4. **Quote Import Module / Adapter (optional but needed if using `報價單匯出`)**
    - Current system does not have a dedicated importer for quotation-export layout (multi-header business doc).
    - If Maolin requires this sheet import, build quote/order import adapter.

5. **Export Template Module (recommended for daily sync loop)**
    - Nightly export templates aligned with the same key fields and naming convention.

---

## 5) Final recommendation for immediate import readiness

For fastest go-live:

1. Import **Client** from `Craveva customer.xlsx` with 2 new custom fields (`region`, `designated_warehouse_name`).
2. Import **Product master** from `Craveva product.xlsx`.
3. Import **Product pricing** from `產品價格表` (sheet in Quote file).
4. Build/extend **Inventory importer** for `產品庫存表` and `Craveva full inventory.xlsx` with warehouse + batch mapping.

If needed, I can generate the next file with exact proposed DB/custom field names and import field IDs (ready for implementation checklist).
