# Miaolin Import: Product & Inventory Column Mapping vs Module Support

**Purpose:** Compare column layout of customer files (`Miaolin Product.xlsx`, `import_product  full.xlsx`, `import_inventory full.xlsx`) with current Product and Inventory import modules; list gaps and recommendations.  
**Language:** English (with original Vietnamese terms in parentheses where useful).

**Main product file:** The primary product import file is **Miaolin Product.xlsx**. The other files (import_product full.xlsx, import_inventory full.xlsx) are used only for comparison. Mapping and field additions for the product module are aligned with **Miaolin Product.xlsx** first.

---

## 1. Files checked

| File                           | Data rows (approx) | Notes                                                                                               |
| ------------------------------ | ------------------ | --------------------------------------------------------------------------------------------------- |
| **Miaolin Product.xlsx**       | 2463               | Product master; many headers become empty after slug formatter (Chinese).                           |
| **import_product full.xlsx**   | 5839               | Product import; headers already in English/slug (sku, standard_price, whole_sale_price, etc.).      |
| **import_inventory full.xlsx** | 2933               | Inventory import; 25 columns (sku, specification, unit, batch_number, warehouse, quantities, etc.). |

---

## 2. Miaolin Product.xlsx – columns and mapping

### 2.1. Columns in file (from first ~100 rows)

| Index | Header (row 1, after formatter) | Sample values        | Meaning (inferred)                  |
| ----- | ------------------------------- | -------------------- | ----------------------------------- |
| 0     | sku                             | A0101001, A0101002   | SKU / product code (品號)           |
| 1     | product_name                    | 日清山茶花強力粉25K  | Product name (品名)                 |
| 2     | _(empty)_                       | 25KG/包, 1KG分裝     | **規格** – specification            |
| 3     | _(empty)_                       | 包, KG               | **庫存單位** – stock unit           |
| 4     | _(empty)_                       | S+級                 | **商品級別** – product grade        |
| 5     | _(empty)_                       | 自行進口商品, 分裝品 | **商品來源** – product source       |
| 6     | _(empty)_                       | 日清製粉             | **品牌類別** – brand                |
| 7     | _(empty)_                       | 270, 240, 180        | **保存天數** – shelf life (days)    |
| 8     | _(empty)_                       | 常備                 | **備貨型態** – stock/inventory type |
| 9     | _(empty)_                       | 常溫                 | **儲存溫層** – storage temp         |
| 10    | _(empty)_                       | 1530, 100, 1500      | **標準價** – standard price         |
| 11    | price_per_box                   | null, …              | **成箱價** – price per box          |

**Note:** Row 1 may be Chinese headers; the heading formatter (e.g. slug) turns many into empty → only a few columns (e.g. sku, product_name, price_per_box) match; the rest show as “Unmatched” unless we add labels or change formatter.

---

### 2.2. DB column vs custom field (Miaolin Product.xlsx)

Rule of thumb: **DB column** = used in queries, filters, reports, or core business logic (e.g. pricing, inventory, expiry); **custom field** = client-specific, variable list of values, mainly for display or optional logic.

| Column in file (meaning)        | Recommended                          | Reason                                                                                                                                     |
| ------------------------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **sku** (品號)                  | **DB** (already exists)              | Unique key, search, duplicate check.                                                                                                       |
| **product_name** (品名)         | **DB** (already `name`)              | Core attribute.                                                                                                                            |
| **規格** (specification)        | **DB**                               | Map to existing **description** (or add `specification` column if description is used for something else). Used in listings and inventory. |
| **規格** (specification) bổ sung      | **DB** (`specification`)             | Cột `specification` đã thêm (migration 2026_03_11, đổi tên 2026_03_11_000002); có trong Product form (main + Purchase) và ProductImport.   |
| **庫存單位** (stock unit)       | **DB** (already `unit_id`)           | Unit is used in orders and inventory.                                                                                                      |
| **標準價** (standard price)     | **DB** (already `price`)             | Core pricing.                                                                                                                              |
| **成箱價** (price per box)      | **DB** (already `price_per_box`)     | Pricing.                                                                                                                                   |
| **儲存溫層** (storage temp)     | **DB** (already `storage_condition`) | F&B logic, filtering.                                                                                                                      |
| **備貨型態** (inventory type)   | **DB** (already `inventory_type`)    | Inventory logic.                                                                                                                           |
| **保存天數** (shelf life, days) | **DB** (đã có)                       | Cột `shelf_life_days` đã thêm (migration 2026_03_09); có trong Product form (main + Purchase).                                             |
| **商品級別** (product grade)    | **Custom field** (user đã tạo)       | Tạo trong Settings > Custom Fields; map cột 商品級別 trong file → custom field tương ứng.                                                  |
| **商品來源** (product source)   | **Custom field** (user đã tạo)       | Tạo trong Settings > Custom Fields; map cột 商品來源 trong file → custom field tương ứng.                                                  |
| **品牌類別** (brand)            | **Custom field** (user đã tạo)       | Tạo trong Settings > Custom Fields; map cột 品牌類別 trong file → custom field tương ứng.                                                  |

**Summary**

- **Chắc chắn cần có trong DB (đã có):** `sku`, `name`, `description` (規格), **specification** (規格, migration 2026_03_11), `unit_id`, `price`, `price_per_box`, `wholesale_price`, `employee_price`, `storage_condition`, `inventory_type`, **shelf_life_days** (migration 2026_03_09).
- **Custom field (user đã tạo qua Settings):** 商品級別 (product_grade), 商品來源 (product_source), 品牌類別 (brand). Cần map trong ProductImport::fields() và gọi updateCustomFieldData trong ImportProductChunkJob để lưu khi import.

---

## 3. import_product full.xlsx – columns and mapping

### 3.1. Columns in file (header row)

| Index | Header                       | Sample (row 2)             | ProductImport / Product model          |
| ----- | ---------------------------- | -------------------------- | -------------------------------------- |
| 0     | sku                          | 品號 \| SKU                | ✓ `sku`                                |
| 1     | product_name                 | 品名 \| Product Name       | ✓ `product_name`                       |
| 2     | inventory_type               | 備貨型態 \| Inventory type | ✓ `inventory_type`                     |
| 3     | standard_price               | 標準售價 \| Standard Price | ⚠ No `standard_price`; use **price**   |
| 4     | whole_sale_price             | 中盤價 \| Whole sale price | ⚠ `wholesale_price` (spelling differs) |
| 5     | price_per_box                | 成箱價 \| Price per box    | ✓ `price_per_box`                      |
| 6     | employee_price_gia_nhan_vien | 員工價 \| Employee price   | ⚠ `employee_price` (id differs)        |

### 3.2. Gaps vs current ProductImport::fields()

- **standard_price** → should map to **price**. Add a field id `standard_price` in `ProductImport::fields()` and in `ImportProductChunkJob::processRow()` map it to `$product->price`, or accept “standard_price” as alias for “price” in the UI.
- **whole_sale_price** → map to **wholesale_price**. Either add `whole_sale_price` as an optional id (and map to `wholesale_price` in the job) or ensure the header can match “wholesale_price” (e.g. normalize “whole_sale_price” → “wholesale_price” when building columns).
- **employee_price_gia_nhan_vien** → map to **employee_price**. Add this string as an option (e.g. label “Employee Price (員工價)”) or accept it as alias for `employee_price`.

So **import_product full** is already close: only **price**, **wholesale_price**, and **employee_price** need to be reachable via these header names (standard_price, whole_sale_price, employee_price_gia_nhan_vien). No new DB columns required if we add these mappings.

### 3.3. Brand in the file: map to vendor or not?

**Recommendation: do not map brand to vendor.**

| Concept    | Meaning in system                                                                                                | In file (e.g. 品牌 / 品牌類別) |
| ---------- | ---------------------------------------------------------------------------------------------------------------- | ------------------------------ |
| **Brand**  | Product attribute: who makes or brands the product (e.g. 日清製粉).                                              | Brand name / brand category.   |
| **Vendor** | Purchase module: supplier (PurchaseVendor) – who you buy from. Linked to orders/bills, not to the Product model. | N/A in current product file.   |

- The **Product** model (`app\Models\Product`) has **no `vendor_id`** (or vendor column). Vendor is used in Purchase (orders, bills, payments), not as a direct product field.
- **Brand** is a product-level attribute. It was previously a Product **custom field** (removed in a migration). Keeping **brand** as a product attribute (custom field or column) is correct: “brand” in the file → Product **brand** (custom field or new column), not vendor.
- If the business needs “default supplier per product”, that would be a separate design: e.g. a product–vendor relation (e.g. `default_vendor_id` on products or a pivot) and a **vendor** column in the import that looks up `PurchaseVendor` by name/code. That would be a **vendor** column → vendor, not **brand** → vendor.

**Conclusion:** Map **brand** in the file to a Product **brand** field (custom field or column). Do not map brand to vendor.

---

## 4. import_inventory full.xlsx – columns and mapping

### 4.1. Columns in file (25 columns)

| Index | Header                        | InventoryImport::fields() / module              |
| ----- | ----------------------------- | ----------------------------------------------- |
| 0     | sku                           | ✓ sku                                           |
| 1     | product_name                  | ✓ product_name                                  |
| 2     | specification                 | ✓ specification                                 |
| 3     | unit                          | ✓ unit                                          |
| 4     | small_unit                    | ❌ Not in fields()                              |
| 5     | packaging_unit                | ❌ Not in fields()                              |
| 6     | batch_number                  | ❌ Not in fields()                              |
| 7     | expiration_date               | ✓ expiration_date                               |
| 8     | manufacturing_date            | ✓ manufacturing_date                            |
| 9     | closing_code                  | ❌ Not in fields()                              |
| 10    | warehouse_code                | ❌ Not in fields()                              |
| 11    | warehouse_name                | ❌ Not in fields()                              |
| 12    | beginning_inventory           | ❌ Not in fields() (closest: quantity / ending) |
| 13    | inbound                       | ❌ Not in fields()                              |
| 14    | outbound                      | ❌ Not in fields()                              |
| 15    | ending_inventory              | ✓ ending_inventory                              |
| 16    | beginning_packaging_inventory | ❌ Not in fields()                              |
| 17    | packaging_inbound_quantity    | ❌ Not in fields()                              |
| 18    | packaging_outbound_quantity   | ❌ Not in fields()                              |
| 19    | ending_packaging_inventory    | ❌ Not in fields()                              |
| 20    | recent_inbound                | ❌ Not in fields()                              |
| 21    | batch_recent_inbound          | ❌ Not in fields()                              |
| 22–24 | 22, 23, 24                    | Unnamed / placeholder                           |

### 4.2. Gaps vs InventoryImport and Purchase flow

- **Already supported:** sku, product_name, specification, unit, expiration_date, manufacturing_date, ending_inventory.
- **Not in InventoryImport::fields():** small_unit, packaging_unit, batch_number, closing_code, warehouse_code, warehouse_name, beginning_inventory, inbound, outbound, beginning_packaging_inventory, packaging_inbound_quantity, packaging_outbound_quantity, ending_packaging_inventory, recent_inbound, batch_recent_inbound.
- Current inventory **store** flow (e.g. `PurchaseInventoryController::store`) and **ImportInventoryJob** use: date, type, reason_id, warehouse_id, product_id, quantity/cost, manufacturing_date, expiration_date, etc. So support for “import_inventory full” would require either:
    - Extending **InventoryImport::fields()** and the inventory import job to read and persist these columns (where the schema supports them), or
    - Treating some as custom fields / metadata and storing them in **custom_fields_data** for `PurchaseInventory` if the main tables do not have columns.

---

## 5. ProductImport::fields() and Product model (current)

### 5.1. Standard fields in ProductImport

product_name, price, unit_type, product_category, product_sub_category, sku, description, **specification** (規格), storage_condition, certification, wholesale_price, price_per_box, employee_price, shelf_life_days, track_inventory, inventory_type, status.

### 5.2. products table (fillable)

name, price, sku, description, **specification**, unit_id, category_id, sub_category_id, storage_condition, certification, wholesale_price, price_per_box, employee_price, **shelf_life_days**, track_inventory, inventory_type, status, …

### 5.3. Product custom fields (current state)

- **Product** `CustomFieldGroup` (model = `App\Models\Product`) tồn tại (migration 2026_02_06).
- **User đã tạo custom fields** qua Settings > Custom Fields cho Product. Các field khuyến nghị cho Miaolin: **product_grade** (商品級別), **product_source** (商品來源), **brand** (品牌類別).
- **shelf_life_days** không dùng custom field nữa – đã chuyển sang cột DB `products.shelf_life_days`.

---

## 6. Gaps summary and recommendations

### 6.1. Product import (main: Miaolin Product.xlsx; reference: import_product full)

| Need                                              | Source file                          | Action                                                                                                                                     |
| ------------------------------------------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
| Map 標準價 / standard_price → price               | Miaolin Product, import_product full | Add `standard_price` in ProductImport::fields() and map to `$product->price` in ImportProductChunkJob.                                     |
| Map whole_sale_price → wholesale_price            | import_product full                  | Add or alias `whole_sale_price` and map to `wholesale_price`.                                                                              |
| Map employee_price_gia_nhan_vien → employee_price | import_product full                  | Add or alias and map to `employee_price`.                                                                                                  |
| 規格 (specification)                              | Miaolin Product col 2                | Map to **description**.                                                                                                                     |
| 規格 (specification) bổ sung                      | (optional column in file)            | **DB** column `specification` (migration 2026_03_11). Field id `specification` in ProductImport::fields(); map in ImportProductChunkJob.    |
| 儲存溫層 (col 9)                                  | Miaolin Product                      | Map to **storage_condition**.                                                                                                              |
| 商品級別, 商品來源, 品牌類別                      | Miaolin Product cols 4–6             | User đã tạo custom fields. Add vào ProductImport::fields() và gọi **updateCustomFieldData** trong ImportProductChunkJob để lưu khi import. |
| 保存天數 (shelf_life_days)                        | Miaolin Product col 7                | Đã có cột DB. Add field id vào ProductImport::fields() và map trong processRow → `$product->shelf_life_days`.                              |
| 備貨型態 (inventory_type)                         | Miaolin Product col 8                | Đã có cột DB. Map trong ProductImport.                                                                                                     |
| Chinese headers → empty                           | Miaolin Product                      | Use heading formatter `none` for Product import and/or add bilingual labels in fields() so users can map by position or by Chinese text.   |

### 6.2. Product import does not save custom fields

- `ImportProductChunkJob::processRow()` only writes to the **products** table; it does **not** call `updateCustomFieldData()`.
- So even if custom fields are added to ProductImport::fields(), their values will **not** be saved until the job is extended (e.g. load Product custom field group, build key-value array from row, then call `$product->updateCustomFieldData(...)` or a bulk insert into `custom_fields_data`).

### 6.3. Inventory import (import_inventory full)

- Add to **InventoryImport::fields()** (and to the inventory import job) any of: small_unit, packaging_unit, batch_number, closing_code, warehouse_code, warehouse_name, beginning_inventory, inbound, outbound, packaging fields, recent_inbound, batch_recent_inbound, if the backend schema and business logic support them.
- If the schema does not have columns for some of these, consider storing them as **PurchaseInventory custom fields** (custom_fields_data) and extending the inventory import job to write them.

---

## 7. Conclusion

- **Miaolin Product.xlsx (main product file):** Cần map 標準價 → price, 規格 → description, 規格 (bổ sung) → specification (DB column, migration 2026_03_11), 儲存溫層 → storage_condition, 備貨型態 → inventory_type, 保存天數 → shelf_life_days (DB column). Custom fields (product_grade, product_source, brand) user đã tạo; cần thêm vào ProductImport::fields() và gọi updateCustomFieldData trong ImportProductChunkJob.
- **import_product full.xlsx (comparison only):** Aligns well with product import; only **standard_price → price**, **whole_sale_price → wholesale_price**, and **employee_price_gia_nhan_vien → employee_price** need to be supported if that file is also used.
- **import_inventory full.xlsx (comparison only):** Only a subset of columns is covered by current InventoryImport::fields(); the rest need to be added to fields() and to the inventory import flow (or to custom fields) for a full match.

Cross-checking with import_product full and import_inventory full was for reference; the primary product import file is **Miaolin Product.xlsx**.
