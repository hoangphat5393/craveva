# Luồng nghiệp vụ: Thêm dữ liệu Product

**Cập nhật:** 2026-05-21  
**Phạm vi:** Tạo sản phẩm mới qua **form UI** (`ProductController::store` hoặc **Purchase** `PurchaseProductController::store`) hoặc **import Excel** (`ImportProductChunkJob::processRow`). Tài liệu mô tả các bảng được ghi dữ liệu và quan hệ 1-n.

**Đa đơn vị (P2-UOM):** Màn **Purchase → Products** có `product_unit_conversions` — xem [`FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md`](../FUNC_IMPROVE/P2_PRODUCT_UOM_KIOTVIET_PLAN_VI.md).

---

## 1. Tổng quan luồng

```
[Request] --> ProductController::store() hoặc ImportProductChunkJob::processRow()
                |
                v
        +-------+-------+
        | Product
        | INSERT products (name, price, sku, unit_id, category_id, sub_category_id, ...)
        +-------+-------+
                |
                v
        +-------+-------+
        | Custom fields (nếu có - chỉ từ UI)
        | INSERT custom_fields_data (model = Product, model_id = products.id)
        +-------+-------+
```

**Lưu ý Import:** product_source, brand, product_grade ghi vào cột DB **products** (không qua custom_fields_data). Hiện tại buildProductCustomFieldsData trả về rỗng (danh sách custom field dùng khi import rỗng) nên import **không ghi** custom_fields_data. Chỉ form UI mới ghi custom_fields_data khi request có custom_fields_data. Nếu sau này bổ sung custom field cho Product và muốn import theo cột Excel thì cần mở lại buildProductCustomFieldsData + ProductImport::fields() (xem IMPORT_CHUNK_AND_BULK_INSERT.md §6).

---

## 2. Các bảng được ghi (khi thêm 1 product)

| #   | Bảng                   | Số dòng thêm      | Ghi chú                                                                                                                                                                                                  |
| --- | ---------------------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | **products**           | 1                 | company_id, name, price, sku, description, specification, **product_source**, **brand**, **product_grade**, unit_id, category_id, sub_category_id, shelf_life_days, storage_condition, inventory_type, … |
| 2   | **custom_fields_data** | N (chỉ UI)        | N = số custom field có giá trị (model = Product). product_source, brand, product_grade là cột DB, không lưu ở đây.                                                                                       |
| 3   | **unit_types**         | 0 hoặc 1 (Import) | Chỉ Import: khi unit type trong file **chưa có** trong DB thì tự tạo (ImportProductChunkJob::createUnitType). Form UI không tạo unit.                                                                    |

**Bảng chỉ đọc (metadata):** `product_category`, `product_sub_category`, `custom_field_groups`, `custom_fields`, `companies`. **unit_types** vừa đọc vừa có thể ghi (Import tạo mới khi thiếu).

**Import thêm:** `employee_activity` (1 dòng) khi có user – `ImportProductChunkJob` gọi `createEmployeeActivity(user_id, 'product-created', product_id, 'product')`.

---

## 3. Mô hình quan hệ (ASCII) – Product và bảng liên quan

```
                    companies (1)
                         |
                         v
                   products (n)
                    company_id
                         |
         +---------------+---------------+
         |               |               |
         v               v               v
   unit_types (1)   product_category   product_sub_category
   unit_id              (1) category_id   (1) sub_category_id
                         |
         +---------------+---------------+
         |                               |
         v                               v
   custom_fields_data (n)         employee_activity (n)
   model = Product, model_id            (chỉ import, khi có user)
         |
         v
   custom_field_id --> custom_fields (n) --> custom_field_groups (1)
```

**Ghi chú:** Import có thể INSERT vào `unit_types` khi unit trong file chưa tồn tại (sau đó product trỏ unit_id tới bản ghi mới).

---

## 4. Quan hệ 1-n (tóm tắt)

| Bảng cha              | Quan hệ | Bảng con           | Khóa ngoại                                      |
| --------------------- | ------- | ------------------ | ----------------------------------------------- |
| companies             | 1-n     | products           | products.company_id                             |
| unit_types            | 1-n     | products           | products.unit_id                                |
| product_category      | 1-n     | products           | products.category_id                            |
| product_sub_category  | 1-n     | products           | products.sub_category_id                        |
| products (model + id) | 1-n     | custom_fields_data | custom_fields_data.model, model_id              |
| custom_field_groups   | 1-n     | custom_fields      | custom_fields.custom_field_group_id             |
| custom_fields         | 1-n     | custom_fields_data | custom_fields_data.custom_field_id              |
| users                 | 1-n     | employee_activity  | employee_activity.user_id (log product-created) |

---

## 5. Nguồn code tham chiếu

| Bước              | File / method                                                                    |
| ----------------- | -------------------------------------------------------------------------------- |
| Form UI           | `App\Http\Controllers\ProductController::store()`                                |
| Import            | `App\Jobs\ImportProductChunkJob::processRow()`                                   |
| Unit auto-create  | `ImportProductChunkJob::createUnitType()` (khi unit trong file chưa có trong DB) |
| Custom field (UI) | `App\Traits\CustomFieldsTrait::updateCustomFieldData()` (trên Product)           |
| Activity (Import) | `App\Traits\EmployeeActivityTrait::createEmployeeActivity()`                     |

---

## 6. So sánh UI vs Import

| Nội dung           | Form UI      | Import Excel                                                                                        |
| ------------------ | ------------ | --------------------------------------------------------------------------------------------------- |
| products           | Có           | Có (gồm specification, product_source, brand, product_grade)                                        |
| unit_types         | Không tạo    | Có (tạo mới nếu unit trong file chưa có)                                                            |
| custom_fields_data | Có (nếu gửi) | Không (product_source, brand, product_grade là cột DB; custom field khác hiện không ghi khi import) |
| employee_activity  | Không        | Có (1 dòng/product)                                                                                 |

---

## 7. Import Excel – chi tiết

| Mục                         | Mô tả                                                                                                                                                 |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Chunk size**              | Mặc định 100 dòng/job (ProductController, PurchaseProductController). Override qua request `chunk_size`.                                              |
| **SKU cache**               | Mỗi job load toàn bộ SKU của company 1 lần; kiểm tra trùng bằng lookup trong memory (O(1)). Product tạo mới được thêm vào cache trong chunk.          |
| **Unit type**               | Tìm theo tên (ưu tiên unit của company, rồi global). Nếu **chưa có** → tạo mới trong `unit_types` (company_id, default=0), dùng id mới cho product.   |
| **Category / sub_category** | Cache theo tên trong chunk (categoryCache, subCategoryCache); không query lặp từng dòng.                                                              |
| **Cột import**              | ProductImport::fields() gồm product_name, price, sku, description, specification, product_source, brand, product_grade, unit_type, shelf_life_days, … |
| **Custom field**            | Hiện không ghi custom_fields_data khi import (buildProductCustomFieldsData trả về []). product_grade, product_source, brand đã là cột DB.             |

## 8. Đối chiếu kế hoạch (IMPORT_CHUNK_AND_BULK_INSERT.md) – đã triển khai

| Phương án trong kế hoạch       | Product import | Ghi chú                                                    |
| ------------------------------ | -------------- | ---------------------------------------------------------- |
| Chunk 10                       | ❌ Không dùng  | Dùng chunk **100** để giảm số job.                         |
| Bulk insert custom_fields_data | ❌ Chưa        | Hiện không ghi custom field khi import.                    |
| Cache metadata trong job       | ✅ Có          | SKU, unit type, category, sub_category.                    |
| Bỏ qua custom field (hiệu quả) | ✅ Có          | Không ghi custom field → không phát sinh query.            |
| Tối ưu riêng đã làm            | ✅ Có          | Chunk 100, SKU cache, unit auto-create, 3 trường → cột DB. |

---

## 9. Purchase Products — SKU tự động & đơn vị phụ (2026-05-21)

| Mục                         | Hành vi                                                                                                                                        |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| **Màn**                     | `Modules/Purchase` — `purchase-products/create`, `edit`                                                                                        |
| **SKU trống / placeholder** | `ProductSkuGenerator` sinh `{PREFIX}-{TYPE}-{6-digit}` theo **`company_id`** (PREFIX từ tên công ty; TYPE: FG/RM/SFG/PKG; service → không SKU) |
| **Bảng phụ**                | `product_sku_sequences` — sequence theo `(company_id, type_prefix)`                                                                            |
| **Validation**              | `sku` nullable khi tạo; `Rule::unique('products','sku')->where('company_id', …)`                                                               |
| **UI**                      | Placeholder `purchase::app.skuAutoGeneratedPlaceholder` — EN: Auto-generated; VI: Tự động                                                      |
| **Đơn vị phụ**              | `ProductUnitConversionSyncService` → `product_unit_conversions` (factor, selling_price, for_sale)                                              |

**Chưa áp dụng:** `App\Http\Controllers\ProductController` (catalog `products` cũ) — chỉ Purchase module.

**Code:** `Modules/Purchase/Services/ProductSkuGenerator.php`, `Http/Requests/Product/Concerns/ResolvesProductSku.php`
