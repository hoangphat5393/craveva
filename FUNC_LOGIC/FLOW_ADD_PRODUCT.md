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

**Lưu ý Import:** Import product hiện **không** ghi custom field (ImportProductChunkJob không gọi updateCustomFieldData). Chỉ form UI mới ghi custom_fields_data khi request có custom_fields_data.

---

## 2. Các bảng được ghi (khi thêm 1 product)

| #   | Bảng                   | Số dòng thêm | Ghi chú                                                                                                       |
| --- | ---------------------- | ------------ | ------------------------------------------------------------------------------------------------------------- |
| 1   | **products**           | 1            | `$product->save()` hoặc tương đương; company_id, name, price, sku, unit_id, category_id, sub_category_id, ... |
| 2   | **custom_fields_data** | N (chỉ UI)   | N = số custom field có giá trị; `model` = 'App\Models\Product', `model_id` = products.id                      |

**Bảng chỉ đọc (metadata):** `unit_types`, `product_category`, `product_sub_category`, `custom_field_groups`, `custom_fields`, `companies`.

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

| Bước              | File / method                                                          |
| ----------------- | ---------------------------------------------------------------------- |
| Form UI           | `App\Http\Controllers\ProductController::store()`                      |
| Import            | `App\Jobs\ImportProductChunkJob::processRow()`                         |
| Custom field (UI) | `App\Traits\CustomFieldsTrait::updateCustomFieldData()` (trên Product) |
| Activity (Import) | `App\Traits\EmployeeActivityTrait::createEmployeeActivity()`           |

---

## 6. So sánh UI vs Import

| Nội dung           | Form UI      | Import Excel        |
| ------------------ | ------------ | ------------------- |
| products           | Có           | Có                  |
| custom_fields_data | Có (nếu gửi) | Không               |
| employee_activity  | Không        | Có (1 dòng/product) |
