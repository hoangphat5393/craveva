# MAOLIN Import Mapping Template (Ready to Use)

**Mục lục MAOLIN:** [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) — mở file đó nếu không biết bắt đầu từ đâu.

Tai lieu nay dung de map cot khi import, theo bo file trong `PROJECT MAOLIN New/`.
Muc tieu: import nhanh, dung core multi-warehouse, giam phu thuoc custom fields.

---

## 0) Nguyen tac chung

- Import theo thu tu:
    1. Warehouse master (neu chua co du)
    2. Client
    3. Product
    4. Pricing
    5. Inventory
- Khoa chinh:
    - Client: `client_code`
    - Product: `sku`
    - Warehouse: `warehouse_code` (name chi fallback)
- Inventory uu tien luu vao core:
    - `warehouse_id` (resolve tu `warehouse_code/name`)
    - `batch_number`
    - `manufacturing_date`
    - `expiration_date`

---

## 1) CLIENT — `Craveva customer.xlsx`

### 1.1 Mapping cot

| Cot file (de xuat) | Import field id             | Bat buoc | Ghi chu                         |
| ------------------ | --------------------------- | -------- | ------------------------------- |
| 客戶代號           | `client_code`               | Nen co   | Khoa update/upsert              |
| 客戶簡稱           | `name`                      | Bat buoc | Ten hien thi                    |
| 統一編號           | `gst_number`                | Tuy chon | Tax ID                          |
| 送貨地址           | `address`                   | Tuy chon | Dia chi                         |
| TEL_NO(一)         | `mobile`                    | Tuy chon | SDT chinh                       |
| TEL_NO(二)         | `company_phone`             | Tuy chon | SDT van phong                   |
| 業務員             | `salesperson`               | Tuy chon | Custom field                    |
| 業務助理名稱       | `sales_assistant_name`      | Tuy chon | Custom field                    |
| 客戶(集團)分級     | `customer_grade`            | Tuy chon | Custom field                    |
| 通路別             | `channel_type`              | Tuy chon | Custom field                    |
| 型態別             | `business_type`             | Tuy chon | Custom field                    |
| 最近交易           | `last_transaction_at`       | Tuy chon | Custom field (date)             |
| 交易條件           | `payment_terms`             | Tuy chon | Custom field                    |
| 歇業日期           | `business_closure_date`     | Tuy chon | Custom field (date)             |
| 指定庫別代碼       | `designated_warehouse_code` | Nen co   | Map sang `default_warehouse_id` |
| 指定庫別名稱       | `designated_warehouse_name` | Fallback | Dung neu khong co code          |

### 1.2 Rule map kho uu tien client

- Tim kho theo `designated_warehouse_code` truoc.
- Neu khong co, fallback `designated_warehouse_name`.
- Neu van khong khop: de `default_warehouse_id = null`, ghi log dong loi de doi soat.

---

## 2) PRODUCT — `Craveva product.xlsx` (sheet hang hoa)

### 2.1 Mapping cot

| Cot file | Import field id     | Bat buoc | Ghi chu                 |
| -------- | ------------------- | -------- | ----------------------- |
| 品號     | `sku`               | Bat buoc | Khoa san pham           |
| 品名     | `name`              | Bat buoc | Ten san pham            |
| 規格     | `specification`     | Tuy chon | Cot core                |
| 庫存單位 | `unit_type`         | Tuy chon | Tu dong map/create unit |
| 商品級別 | `product_grade`     | Tuy chon | Cot core                |
| 品牌類別 | `brand`             | Tuy chon | Cot core                |
| 保存天數 | `shelf_life_days`   | Tuy chon | Cot core                |
| 備貨型態 | `inventory_type`    | Tuy chon | Cot core                |
| 儲存溫層 | `storage_condition` | Tuy chon | Cot core                |

---

## 3) PRICING — `Quote, unit price, inventory.xlsx` sheet `產品價格表`

### 3.1 Mapping cot

| Cot file | Dich nghia     | Di vao he thong            |
| -------- | -------------- | -------------------------- |
| 品號     | SKU            | Tim product theo `sku`     |
| 標準售價 | Standard price | `products.price`           |
| 中盤價   | Wholesale      | `products.wholesale_price` |
| 成箱價   | Price per box  | `products.price_per_box`   |
| 員工價   | Employee price | `products.employee_price`  |

---

## 4) INVENTORY — `產品庫存表` / `庫存明細總表`

### 4.1 Mapping cot toi importer (bat buoc cho multi-warehouse)

| Cot file          | Import field id                    | Bat buoc           | Ghi chu             |
| ----------------- | ---------------------------------- | ------------------ | ------------------- |
| 品號 / 產品料號   | `sku`                              | Bat buoc           | Khoa product        |
| 品名 / 產品名稱   | `product_name`                     | Tuy chon           | Doi soat            |
| 庫別              | `warehouse_code`                   | Nen co             | Resolve kho uu tien |
| 庫別名稱          | `warehouse_name`                   | Fallback           | Dung khi thieu code |
| 批號              | `batch_number`                     | Nen co             | Da la cot DB        |
| 製造日期          | `manufacturing_date`               | Tuy chon           | Da la cot DB        |
| 有效日期          | `expiration_date`                  | Nen co             | Da la cot DB        |
| 期末庫存 / 庫存量 | `ending_inventory` hoac `quantity` | Bat buoc 1 trong 2 | Snapshot so luong   |
| 單位              | `unit`                             | Tuy chon           | Doi soat don vi     |
| 規格              | `specification`                    | Tuy chon           | Doi soat            |

### 4.2 Rule resolve kho (quan trong)

- He thong resolve:
    1. `warehouse_code` (uu tien)
    2. `warehouse_name` (fallback)
- Neu khong resolve duoc: bo qua dong va ghi log (khong cho ghi sai kho).

### 4.3 Cac cot KHONG can map vao custom fields nua

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
- `closing_code` (neu khong co quy trinh nghiep vu)

---

## 5) Checklist truoc khi chay import that

- [ ] Da migrate DB moi nhat
- [ ] Da co warehouse master day du code + name
- [ ] Da map dung 3 cot quan trong inventory: `warehouse_code`, `warehouse_name`, `batch_number`
- [ ] Da test trial 20-50 dong
- [ ] Da doi soat 5 SKU o 2 kho:
    - `purchase_stock_adjustments.warehouse_id`
    - `purchase_stock_adjustments.batch_number`
    - `warehouse_product_batches.quantity`

---

## 6) Checklist sau import

- [ ] Khong co dong loi resolve warehouse
- [ ] Ton kho theo kho khop giua UI (`/warehouse-stock`) va DB
- [ ] Client co `default_warehouse_id` neu file co designated warehouse
- [ ] Khong can dung lai custom fields `warehouse_code/name` tren form Inventory
