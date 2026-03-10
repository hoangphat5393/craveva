# Luồng nghiệp vụ: Thêm dữ liệu Inventory (Purchase Inventory Adjustment)

**Phạm vi:** Tạo phiếu điều chỉnh tồn kho qua **form UI** (PurchaseInventoryController::store). Mỗi lần thêm = 1 “inventory” (header) + N dòng sản phẩm (stock adjustments). Tài liệu mô tả các bảng được ghi và quan hệ 1-n.

---

## 1. Tổng quan luồng

```
[Request] --> PurchaseInventoryController::store()
                |
                v
        Với mỗi product_id trong form:
                |
        +-------+-------+
        | PurchaseStockAdjustment (đầu tiên tạo header)
        | Nếu chưa có: NEW PurchaseInventory
        | INSERT purchase_inventory_adjustment (1 dòng: date, type, reason_id, warehouse_id, added_by)
        +-------+-------+
                |
                v
        +-------+-------+
        | PurchaseStockAdjustment
        | INSERT hoặc UPDATE purchase_stock_adjustments (inventory_id, product_id, warehouse_id, net_quantity, ...)
        +-------+-------+
                |
                v
        +-------+-------+
        | Product (cập nhật giá nếu có changed_value)
        | UPDATE products.price
        +-------+-------+
                |
        (sau khi lưu hết sản phẩm trong form)
                v
        +-------+-------+
        | Custom fields (nếu có)
        | INSERT custom_fields_data (model = PurchaseInventory, model_id = purchase_inventory_adjustment.id)
        +-------+-------+
```

---

## 2. Các bảng được ghi (khi thêm 1 phiếu inventory)

| #   | Bảng                              | Số dòng thêm/cập nhật                 | Ghi chú                                                                                                                                                                    |
| --- | --------------------------------- | ------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | **purchase_inventory_adjustment** | 1                                     | Header phiếu: date, type, reason_id, warehouse_id, added_by. Model: PurchaseInventory.                                                                                     |
| 2   | **purchase_stock_adjustments**    | N (1 per product trong form)          | Dòng điều chỉnh: inventory_id, product_id, warehouse_id, reason_id, net_quantity, quantity_adjustment hoặc changed_value, manufacturing_date, expiration_date, status, ... |
| 3   | **products**                      | 0 hoặc N (update)                     | Cập nhật product.price khi addStock.changed_value có giá trị                                                                                                               |
| 4   | **custom_fields_data**            | M (nếu request có custom_fields_data) | model = 'Modules\Purchase\Entities\PurchaseInventory', model_id = purchase_inventory_adjustment.id                                                                         |

**Bảng chỉ đọc (metadata):** `purchase_stock_adjustment_reasons`, warehouses (Warehouse module), `products` (đọc để lấy product), `custom_field_groups`, `custom_fields`, `companies`.

---

## 3. Mô hình quan hệ (ASCII) – Inventory và bảng liên quan

```
                    companies (1)
                         |
                         v
        purchase_inventory_adjustment (n)
        (PurchaseInventory)
        warehouse_id, reason_id, date, type, added_by
                         |
                         | id = inventory_id
                         v
        purchase_stock_adjustments (n)
        (PurchaseStockAdjustment)
        inventory_id, product_id, warehouse_id, reason_id,
        net_quantity, quantity_adjustment, changed_value, ...
                         |
         +---------------+---------------+
         |               |               |
         v               v               v
   products (1)   warehouses (1)   purchase_stock_adjustment_reasons (1)
   product_id     warehouse_id     reason_id
         ^
         | UPDATE products.price (khi có changed_value)

        purchase_inventory_adjustment (1)
                         |
                         | model_id (polymorphic)
                         v
               custom_fields_data (n)
               model = PurchaseInventory
                         |
                         v
               custom_fields --> custom_field_groups
```

---

## 4. Quan hệ 1-n (tóm tắt)

| Bảng cha                          | Quan hệ | Bảng con                      | Khóa ngoại                              |
| --------------------------------- | ------- | ----------------------------- | --------------------------------------- |
| companies                         | 1-n     | purchase_inventory_adjustment | company_id (BaseModel/HasCompany)       |
| purchase_inventory_adjustment     | 1-n     | purchase_stock_adjustments    | purchase_stock_adjustments.inventory_id |
| products (PurchaseProduct)        | 1-n     | purchase_stock_adjustments    | purchase_stock_adjustments.product_id   |
| warehouses                        | 1-n     | purchase_stock_adjustments    | purchase_stock_adjustments.warehouse_id |
| purchase_stock_adjustment_reasons | 1-n     | purchase_inventory_adjustment | reason_id                               |
| purchase_stock_adjustment_reasons | 1-n     | purchase_stock_adjustments    | reason_id                               |
| PurchaseInventory (model + id)    | 1-n     | custom_fields_data            | custom_fields_data.model, model_id      |
| custom_fields                     | 1-n     | custom_fields_data            | custom_fields_data.custom_field_id      |

---

## 5. Nguồn code tham chiếu

| Bước            | File / method                                                                    |
| --------------- | -------------------------------------------------------------------------------- |
| Form UI         | `Modules\Purchase\Http\Controllers\PurchaseInventoryController::store()`         |
| Header          | `PurchaseInventory` → bảng `purchase_inventory_adjustment`                       |
| Dòng tồn kho    | `PurchaseStockAdjustment` → bảng `purchase_stock_adjustments`                    |
| Custom field    | `App\Traits\CustomFieldsTrait::updateCustomFieldData()` (trên PurchaseInventory) |
| Cập nhật giá SP | `PurchaseProduct::findOrFail()->save()` (price = addStock.changed_value)         |

---

## 6. Lưu ý

- Một phiếu (1 record `purchase_inventory_adjustment`) gắn nhiều dòng `purchase_stock_adjustments` (mỗi dòng = 1 product + warehouse).
- Nếu đã tồn tại `PurchaseStockAdjustment` cho cặp (product_id, warehouse_id) thì tái sử dụng và gán `inventory_id` cho phiếu hiện tại; nếu chưa thì tạo mới PurchaseStockAdjustment.
- Import inventory (nếu có) dùng luồng khác (ImportInventoryJob / importJobProcess); có thể tham chiếu thêm tài liệu import riêng.
