# MAOLIN - PHÂN TÍCH TẬP TRUNG (Product / Client / Inventory / Tier Pricing)

**Phạm vi cố định (theo yêu cầu):**

- `PROJECT MAOLIN New/Craveva customer.xlsx`
- `PROJECT MAOLIN New/Craveva product.xlsx`
- `PROJECT MAOLIN New/Quote, unit price, inventory.xlsx`
- `PROJECT MAOLIN New/Craveva full inventory.xlsx`

**Chỉ phân tích 4 chức năng:**

1. Product
2. Client
3. Inventory
4. Tier Pricing

> Không mở rộng sang module khác ngoài phạm vi trên.

---

## A) CLIENT

## 1) Cột chính từ file `Craveva customer.xlsx`

- 客戶代號 -> `client_code`
- 客戶簡稱 -> `client_name`
- 統一編號 -> `tax_id`
- 業務員 -> `salesperson`
- 業務助理名稱 -> `sales_assistant_name`
- 客戶(集團)分級 -> `customer_grade`
- 通路別 -> `channel_type`
- 型態別 -> `business_type`
- 地區別 -> `region`
- 送貨地址 -> `shipping_address`
- TEL_NO(一) -> `phone_1`
- TEL_NO(二) -> `phone_2`
- 交易條件 -> `payment_terms`
- 最近交易 -> `last_transaction_at`
- 歇業日期 -> `business_closure_date`
- 指定庫別名稱 -> `designated_warehouse_name`

## 2) Đối chiếu hệ thống

- Đã map tốt: `client_code`, `client_name`, `tax_id`, `shipping_address`, phone, và các custom field (salesperson, customer_grade, channel_type, business_type, payment_terms, last_transaction_at, business_closure_date).
- Chưa map sẵn: `region`, `designated_warehouse_name`.

## 3) Thiếu cột quan trọng

- Thiếu `email` (nếu cần login/contact).
- Thiếu `department` (file cũ có, file mới không có).

---

## B) PRODUCT

## 1) Cột chính từ file `Craveva product.xlsx`

- 品號 -> `sku`
- 品名 -> `product_name`
- 規格 -> `specification`
- 庫存單位 -> `unit_type`
- 商品級別 -> `product_grade`
- 品牌類別 -> `brand`
- 保存天數 -> `shelf_life_days`
- 備貨型態 -> `inventory_type`
- 儲存溫層 -> `storage_condition`
- 失效日期 -> `expiry_date`

## 2) Đối chiếu hệ thống

- Đã map tốt: SKU, tên, quy cách, unit, grade, brand, shelf life, inventory type, storage condition.
- `expiry_date` chưa có mapping trực tiếp trong luồng Product import hiện tại.

## 3) Thiếu cột quan trọng để import sản phẩm ổn định

- `standard_price` hoặc `price` (rất quan trọng)
- `price_per_box`
- `product_source`
- `product_category`, `product_sub_category`
- `status` (active/inactive)

---

## C) INVENTORY

## 1) Nguồn inventory trong file

- `Quote, unit price, inventory.xlsx` -> sheet `產品庫存表`
- `Craveva full inventory.xlsx` -> sheet `庫存明細總表`

## 2) Cột inventory quan trọng

- 品號 -> `sku`
- 品名 -> `product_name`
- 規格 -> `specification`
- 單位 -> `unit`
- 批號 -> `batch_number`
- 有效日期 -> `expiration_date`
- 製造日期 -> `manufacturing_date`
- 庫別 -> `warehouse_code`
- 庫別名稱 -> `warehouse_name`
- 期末庫存 / 庫存量 -> `ending_inventory` / `stock_qty`

## 3) Đối chiếu hệ thống hiện tại

- Đã có trong importer: `sku`, `product_name`, `unit`, `specification`, `manufacturing_date`, `expiration_date`.
- Chưa có sẵn trong importer: `warehouse_code`, `warehouse_name`, `batch_number`, các cột movement kỳ (`期初/本期入/本期出/...`).

## 4) Thiếu cần bổ sung để import inventory đúng

- Mapping kho (`warehouse_code/name` -> `warehouse_id`)
- Mapping theo lô (`batch_number` + `expiration_date`)
- Rule xử lý tồn theo kỳ (snapshot hay movement)

---

## D) TIER PRICING

## 1) Nguồn dữ liệu phù hợp

- `Quote, unit price, inventory.xlsx` -> sheet `產品價格表`

## 2) Cột giá

- 品號 -> `sku`
- 標準售價 -> `standard_price`
- 中盤價 -> `wholesale_price`
- 成箱價 -> `price_per_box`
- 員工價 -> `employee_price`

## 3) Đối chiếu hệ thống

- Các cột trên có thể map vào dữ liệu giá sản phẩm hiện có.
- Để chạy đúng **Tier Pricing theo khách hàng/kênh/số lượng**, cần rule áp giá rõ (source of truth + ưu tiên giữa standard/tier/client-specific).

---

## E) KẾT LUẬN THIẾU SÓT THEO ĐÚNG PHẠM VI 4 CHỨC NĂNG

1. **Client**: thiếu mapping `region`, `designated_warehouse_name`; thiếu cột `email`, `department` trong file mới.
2. **Product**: thiếu cột giá và category/sub-category trong `Craveva product.xlsx`.
3. **Inventory**: thiếu mapping kho + batch; thiếu rule snapshot/movement.
4. **Tier Pricing**: dữ liệu có trong sheet `產品價格表`, nhưng cần chốt rule áp giá để import vận hành ổn định.

---

## F) CẦN BỔ SUNG GÌ ĐỂ IMPORT 4 FILE NÀY

- **Bổ sung mapping field (không bắt buộc module mới):**
    - Client: `region`, `designated_warehouse_name`
    - Inventory: `warehouse_code/name`, `batch_number`
- **Bổ sung adapter import (nên có):**
    - Pricing sheet importer (`產品價格表`) cho Tier Pricing
    - Inventory importer mở rộng cho batch + warehouse

> Trong phạm vi yêu cầu hiện tại, chưa cần bàn sang module ngoài Product/Client/Inventory/Tier Pricing.

---

## G) PHÂN LOẠI RÕ: CỘT DB vs CUSTOM FIELD vs CẦN MIGRATE

## G.1 Client

### Đang là cột DB (bảng chính)

- `client_code` -> `client_details.client_code`
- `client_name` -> `users.name`
- `tax_id` -> `client_details.gst_number`
- `shipping_address` -> `client_details.address`
- `phone_1` -> `users.mobile`
- `phone_2` -> `client_details.office`

### Đang là Custom Field

- `salesperson`
- `sales_assistant_name`
- `customer_grade`
- `channel_type`
- `business_type`
- `payment_terms`
- `last_transaction_at`
- `business_closure_date`

### Chưa có trong hệ thống (chưa map)

- `region` (地區別)
- `designated_warehouse_name` (指定庫別名稱)

### Cột **nên migrate sang bảng chính** (ưu tiên cao)

1. `designated_warehouse_name` -> **`client_details.default_warehouse_id` (FK warehouses)**
    - Lý do: dùng cho phân bổ tồn/giao hàng/tier theo kho thì cần join/filter/index.
2. `payment_terms` -> **`client_details.payment_term_id`** (hoặc cột chuẩn tương đương)
    - Lý do: điều khoản thanh toán dùng nghiệp vụ thường xuyên, không nên để custom text tự do nếu cần báo cáo/đồng bộ.

### Cột có thể giữ custom (không bắt buộc migrate ngay)

- `salesperson`, `sales_assistant_name`, `last_transaction_at`, `business_closure_date`
- `region`, `channel_type`, `business_type`, `customer_grade` (chỉ migrate nếu dùng để rule engine/tier query nặng).

---

## G.2 Product

### Đang là cột DB (bảng chính)

- `sku`, `product_name`, `specification`, `unit_type(unit_id)`
- `product_grade`, `brand`
- `shelf_life_days`, `inventory_type`, `storage_condition`
- giá: `price/standard_price`, `wholesale_price`, `price_per_box`, `employee_price`

### Đang là Custom Field

- Không có custom field bắt buộc trong scope 4 chức năng hiện tại.

### Cột **nên migrate sang bảng chính**

- **Không có cột custom nào bắt buộc migrate ngay** ở Product trong scope hiện tại.

> Ghi chú: `expiry_date` không nên đưa vào bảng product master; nên quản lý theo **batch/lot inventory**.

---

## G.3 Inventory

### Đang là cột DB (hiện có ở luồng inventory import)

- `sku`, `product_name`, `unit`, `specification`
- `manufacturing_date`, `expiration_date`
- `quantity` / `ending_inventory` (mapping theo luồng hiện tại)

### Đang là Custom/hoặc chưa có field chính thức

- `warehouse_code`, `warehouse_name` (hiện chưa map chính thức trong importer)
- `batch_number`
- các cột kỳ: `opening_stock`, `stock_in_period`, `stock_out_period`, `ending_pack_stock`...

### Cột **bắt buộc migrate sang bảng chính** (P0)

1. `warehouse_code` / `warehouse_name` -> map chuẩn vào **`warehouse_id` (FK warehouses)**
2. `batch_number` -> cột chính thức ở bảng tồn kho theo lô/movement
3. `close_status_code` (結案碼) -> trạng thái lô (nếu dùng để lọc tồn hợp lệ)

Lý do chung: đây là khóa nghiệp vụ để join, tránh trùng lô, chạy báo cáo tồn và sync ERP ổn định.

---

## G.4 Tier Pricing

### Đang là bảng chính (không phải custom field)

- `pricing_tiers`
- `pricing_tier_items`
- `client_product_pricing`

### Cột có thể đang nằm custom ở Client nhưng liên quan Tier

- `customer_grade`
- `channel_type`

### Cột **nên migrate sang bảng chính nếu dùng làm rule Tier Pricing**

1. `customer_grade` -> bảng dimension chính thức (vd `client_grade_id`)
2. `channel_type` -> bảng dimension chính thức (vd `channel_type_id`)

Lý do: nếu rule tier phụ thuộc 2 chiều này thì cần cấu trúc chuẩn để index/query nhanh và tránh sai chính tả khi nhập custom text.

---

## H) KẾT LUẬN NGẮN (CÁI GÌ “NHẤT ĐỊNH” NÊN MIGRATE)

Trong scope Product/Client/Inventory/Tier Pricing, các cột nên ưu tiên migrate trước:

1. **Inventory:** `warehouse_code/name` -> `warehouse_id`, `batch_number`, `close_status_code`.
2. **Client:** `designated_warehouse_name` -> `default_warehouse_id`, `payment_terms` -> `payment_term_id`.
3. **Tier Pricing (nếu dùng rule theo phân khúc):** `customer_grade`, `channel_type` -> dimension chuẩn có ID.
