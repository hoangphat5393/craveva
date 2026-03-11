# Luồng nghiệp vụ: Thêm dữ liệu Product

**Phạm vi:** Tạo sản phẩm mới qua **form UI** (ProductController::store) hoặc **import Excel** (ImportProductChunkJob::processRow). Tài liệu mô tả các bảng được ghi dữ liệu và quan hệ 1-n.

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

**Lưu ý Import:** product_source, brand, product_grade ghi vào cột DB **products** (không qua custom_fields_data). Các custom field khác (nếu có trong Product group) vẫn có thể ghi qua updateCustomFieldData; hiện buildProductCustomFieldsData trả về rỗng nên import không ghi custom_fields_data. Chỉ form UI mới ghi custom_fields_data khi request có custom_fields_data.

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

| Nội dung           | Form UI      | Import Excel                                                 |
| ------------------ | ------------ | ------------------------------------------------------------ |
| products           | Có           | Có (gồm specification, product_source, brand, product_grade) |
| unit_types         | Không tạo    | Có (tạo mới nếu unit trong file chưa có)                     |
| custom_fields_data | Có (nếu gửi) | Không (product_source, brand, product_grade là cột DB)       |
| employee_activity  | Không        | Có (1 dòng/product)                                          |

---

## 7. Import Excel – chi tiết

| Mục            | Mô tả                                                                                                                                                 |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Chunk size** | Mặc định 100 dòng/job (ProductController, PurchaseProductController). Override qua request `chunk_size`.                                              |
| **SKU cache**  | Mỗi job load toàn bộ SKU của company 1 lần; kiểm tra trùng bằng lookup trong memory (O(1)). Product tạo mới được thêm vào cache trong chunk.          |
| **Unit type**  | Tìm theo tên (ưu tiên unit của company, rồi global). Nếu **chưa có** → tạo mới trong `unit_types` (company_id, default=0), dùng id mới cho product.   |
| **Cột import** | ProductImport::fields() gồm product_name, price, sku, description, specification, product_source, brand, product_grade, unit_type, shelf_life_days, … |
