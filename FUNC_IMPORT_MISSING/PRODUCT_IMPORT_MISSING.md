# Product Import Gap Analysis

This document analyzes the gaps between the provided Excel import files (`import_product.xlsx`, `import_Inventory.xlsx`) and the System Database Schema.

## 1. Field Mapping Status

### A. Fields in `import_product.xlsx`

| Excel Header                 | System Field      | Status        | Notes               |
| :--------------------------- | :---------------- | :------------ | :------------------ |
| `品號 \| SKU`                | `sku`             | ✅ **Mapped** |                     |
| `品名 \| Product Name`       | `name`            | ✅ **Mapped** |                     |
| `備貨型態 \| Inventory type` | `inventory_type`  | ✅ **Mapped** | System field added. |
| `標準售價 \| Standard Price` | `price`           | ✅ **Mapped** | Selling Price.      |
| `中盤價 \| Whole sale price` | `wholesale_price` | ✅ **Mapped** | System field added. |
| `成箱價 \| Price per box`    | `price_per_box`   | ✅ **Mapped** | System field added. |
| `員工價 \| Employee price`   | `employee_price`  | ✅ **Mapped** | System field added. |

### B. Fields in `import_Inventory.xlsx` (Potential Fillers)

| Excel Header                      | System Field    | Status                | Notes                                                                                  |
| :-------------------------------- | :-------------- | :-------------------- | :------------------------------------------------------------------------------------- |
| `單位 \| unit`                    | `unit_id`       | ✅ **Mapped**         | Logic Added: If Product Unit is missing, it will use this value (creates Unit if new). |
| `規格 \| Specification`           | `description`   | ✅ **Mapped**         | Logic Added: If Product Description is missing, it will use this value.                |
| `期初庫存 \| Beginning Inventory` | `opening_stock` | ✅ **Mapped**         | Used for Stock Adjustment.                                                             |
| `庫別名稱`                        | `warehouse_id`  | ⚠️ **Mapping Needed** | Needs logic to map Warehouse Name to ID.                                               |

## 2. Critical Missing Fields (Missing in BOTH Files)

The following fields are required or highly recommended by the System but are **NOT found in either Excel file**.

| Field Name            | Database Column     | Importance      | Action Required                                                  |
| :-------------------- | :------------------ | :-------------- | :--------------------------------------------------------------- |
| **Storage Condition** | `storage_condition` | 🔴 **REQUIRED** | **Add Column to Excel**. Values: `Frozen`, `Chilled`, `Ambient`. |
| **Certification**     | `certification`     | 🟡 Recommended  | **Add Column to Excel**. Text description.                       |
| **Category**          | `category_id`       | 🟠 Important    | **Add Column to Excel**. Product Category Name.                  |
| **Purchase Price**    | `purchase_price`    | 🟠 Important    | **Add Column to Excel**. Cost Price (needed for profit calc).    |

## 3. Recommended Action Plan

1.  **System Updates (Developer Task):**
    - ✅ Create Migration for Custom Fields: `wholesale_price`, `price_per_box`, `employee_price` (Done).
    - ✅ **Update `ImportInventoryJob.php`**: Logic added to populate `unit_id` and `description` from Inventory file (Done).
    - ✅ Update `ProductImport.php` to handle `wholesale_price`, `price_per_box`, `employee_price` (Done).

2.  **Excel Updates (User Task):**
    - **Add Missing Columns**: You MUST add `Storage Condition` to your Product Excel file (`import_product.xlsx`).
    - **Add Category**: Recommended to add `Category` column for better organization.
