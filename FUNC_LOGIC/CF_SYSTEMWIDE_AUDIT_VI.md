# Bảng CF trong hệ thống — đối chiếu nghiệp vụ vs core (để xóa tay)

**Mục đích:** Một bảng tra cứu: **module / slug** (theo migration seed trong repo), **nghiệp vụ**, **có trùng core không**, **gợi ý xóa**.  
**DB thực tế** có thể khác (admin tạo thêm, hoặc đã chạy migration dọn) — luôn chạy **§ Cuối: SQL xuất danh sách đang có**.

**Tài liệu liên quan:** mục lục [FUNC_LOGIC/README.md](README.md); chi tiết CF trùng cột (nếu có trong repo): `CUSTOM_FIELDS_GO_BO_TRUNG_COT_PO_DO_SO_CLIENT_VI.md`.

---

## 1) Tóm tắt trạng thái seed trong repo

| Nhóm model                          | Seed field trong migration?                                                                                                                                                                                                | Ghi chú                                                                                                                          |
| ----------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Client**                          | Có (Miaolin)                                                                                                                                                                                                               | § bảng 2                                                                                                                         |
| **Product**                         | Có rồi **xóa nhóm** bởi [`2026_01_21_000001...`](../database/migrations/2026_01_21_000001_remove_all_product_custom_fields_fb.php)                                                                                         | Sau chạy đủ migrate: **không** còn CF Product từ repo; DB cũ chưa chạy có thể vẫn còn slug § bảng 3                              |
| **Inventory** (`PurchaseInventory`) | Có — [`2026_01_14...`](../database/migrations/2026_01_14_130000_add_inventory_custom_fields_fb.php) + [`2026_02_02...`](../Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php) | §4 đầy đủ (4.1–4.4) + map import [MAOLIN_IMPORT_MAPPING](MAOLIN_IMPORT_MAPPING.md)                                               |
| **Order**                           | Chỉ tạo **nhóm** (không seed slug)                                                                                                                                                                                         | CF = **tự tạo** — § bảng 5                                                                                                       |
| **Event**                           | Chỉ nhóm                                                                                                                                                                                                                   | Không seed slug trong file này                                                                                                   |
| **Invoice / PO**                    | File seed **đã xóa** khỏi repo; dữ liệu demo dọn bởi [`2026_03_30_140000...`](../database/migrations/2026_03_30_140000_remove_legacy_invoice_po_cf_seed_migrations_and_demo_fields.php)                                    | § bảng 6 = slug **lịch sử** (đối chiếu nếu còn sót tay)                                                                          |
| **Delivery Order**                  | Đã xóa CF nhóm DO                                                                                                                                                                                                          | [`2026_03_28_100000...`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php) |

---

## 2) Client — `App\Models\ClientDetails`

**Seed:** [`2026_03_09_100000...`](../database/migrations/2026_03_09_100000_add_client_custom_fields_for_miaolin.php), [`2026_03_09_110000...`](../database/migrations/2026_03_09_110000_add_client_custom_fields_last_transaction_payment_terms_closure.php)  
**Core:** [`ClientDetails::$fillable`](../app/Models/ClientDetails.php) (`company_name`, `address`, `gst_number`, `client_code`, `default_warehouse_id`, …)

| Slug                    | Nghiệp vụ / nguồn                              | Trùng core?                                                        | Gợi ý        |
| ----------------------- | ---------------------------------------------- | ------------------------------------------------------------------ | ------------ |
| `salesperson`           | Nhân viên kinh doanh (Miaolin 客戶資料)        | **Không** tên cột                                                  | Giữ / import |
| `department`            | Phòng ban                                      | **Không**                                                          | Giữ          |
| `sales_assistant_name`  | Trợ lý KD                                      | **Không**                                                          | Giữ          |
| `channel_type`          | Loại kênh bán                                  | **Không**                                                          | Giữ          |
| `business_type`         | Loại hình DN                                   | **Không**                                                          | Giữ          |
| `last_transaction_at`   | Ngày giao dịch gần nhất                        | **Không**                                                          | Giữ          |
| `payment_terms`         | Điều khoản thanh toán                          | **Không**                                                          | Giữ          |
| `business_closure_date` | Ngày ngừng hoạt động                           | **Không**                                                          | Giữ          |
| _(CF tự tạo)_           | vd. `company_name`, `gst_number`, `warehouse`… | **Có** — trùng `client_details` / `users` / `default_warehouse_id` | **Nên gỡ**   |

---

## 3) Product — `App\Models\Product` (slug từng được seed; sau `2026_01_21` nhóm thường bị xóa hết)

**Seed:** [`2026_01_12...`](../database/migrations/2026_01_12_190624_add_product_custom_fields_fb.php), [`2026_01_14_120000...`](../database/migrations/2026_01_14_120000_add_additional_product_custom_fields_fb.php)  
**Core:** [`Product::$fillable`](../app/Models/Product.php)

| Slug                            | Nghiệp vụ                 | Trùng core?             | Cột / nguồn chuẩn                        |
| ------------------------------- | ------------------------- | ----------------------- | ---------------------------------------- |
| `storage_condition`             | Điều kiện bảo quản        | **Có**                  | `products.storage_condition`             |
| `certification`                 | Chứng nhận                | **Có**                  | `products.certification`                 |
| `brand`                         | Thương hiệu               | **Có**                  | `products.brand`                         |
| `shelf_life_days`               | Hạn dùng (ngày)           | **Có**                  | `products.shelf_life_days`               |
| `erp_sku_mapping`               | Map SKU ERP               | Trùng **nghĩa** mã hàng | `products.sku`                           |
| `wms_sku_mapping`               | Map SKU WMS               | Trùng **nghĩa**         | `products.sku`                           |
| `batch_tracking_enabled`        | Có theo dõi lô            | Trùng **nghĩa** tồn     | `products.track_inventory` (+ loại hàng) |
| `inventory_issue_rule`          | FIFO/FEFO                 | **Không** cột cùng tên  | Tuỳ — có thể giữ CF hoặc settings sau    |
| `near_expiry_days_threshold`    | Ngưỡng cận HSD            | **Không**               | Tuỳ                                      |
| `near_expiry_discount_eligible` | Đủ điều kiện giảm cận HSD | **Không**               | Tuỳ                                      |

**Gợi ý xóa tay:** các dòng **Trùng core / trùng nghĩa sku** nếu vẫn thấy trên Settings sau khi đã chạy `2026_01_21`.

---

## 4) Inventory — `Modules\Purchase\Entities\PurchaseInventory`

**Seed / cập nhật trong repo**

- [`2026_01_14_130000...`](../database/migrations/2026_01_14_130000_add_inventory_custom_fields_fb.php) — bộ đầu: kho, lô, NSX/HSD, cận HSD, giữ chỗ, v.v.
- [`2026_02_02_150000...`](../Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php) — thêm slug Maolin / snapshot kỳ; đồng thời bổ sung schema `warehouse_id` trên phiếu và pivot.

**Core (nguồn tồn chuẩn):** `purchase_inventory_adjustment.warehouse_id`; pivot `purchase_stock_adjustments` (`warehouse_id`, `batch_number`, `manufacturing_date`, `expiration_date`, …); module Warehouse — `warehouse_product_batches` (gồm `quantity`, `reserved_quantity`, …), `StockMovementService`, `StockReservationService`, (tuỳ config) `InvoiceWarehouseStockService`.

**Import Excel:** cột cố định trong [`InventoryImport`](../Modules/Purchase/Imports/InventoryImport.php) (`sku`, `warehouse_code`, `ending_inventory`, …); các cột CF thêm được merge từ DB theo nhóm Inventory — nếu xóa CF mà file vẫn map theo **label** cũ, cần đổi template import.

### 4.1) Trùng Warehouse / phiếu — ưu tiên **không** dùng CF làm tồn thật

| Slug                 | Nghiệp vụ         | Trùng core?                 | Đề xuất                                                                  |
| -------------------- | ----------------- | --------------------------- | ------------------------------------------------------------------------ |
| `warehouse_code`     | Mã kho (text)     | **Trùng nghĩa**             | **Xóa CF** — dùng `warehouses.code` + `warehouse_id`                     |
| `warehouse_name`     | Tên kho           | **Trùng nghĩa**             | **Xóa CF** — dùng `warehouses.name`                                      |
| `batch_number`       | Số lô             | **Trùng nghĩa**             | **Xóa CF** — pivot / `warehouse_product_batches`                         |
| `manufacturing_date` | NSX               | **Trùng nghĩa**             | **Xóa CF** — cột pivot / batch                                           |
| `expiration_date`    | HSD (slug merged) | **Trùng nghĩa**             | **Xóa CF** — pivot `expiration_date` / batch                             |
| `expiry_date`        | HSD (slug legacy) | **Trùng nghĩa**             | **Gộp / xóa** — cùng nghĩa `expiration_date` (migrate chỉnh label)       |
| `near_expiry_status` | Cận HSD (select)  | Trùng **nghĩa** (tính được) | **Xóa** — báo cáo tính từ HSD + ngưỡng, không lưu select tĩnh            |
| `reserved_quantity`  | SL giữ chỗ        | **Trùng nghĩa**             | **Xóa CF** — `warehouse_product_batches.reserved_quantity` + reservation |

### 4.2) Snapshot kỳ / file tổng hợp (không được movement tự cập nhật)

Dùng cho **đối chiếu import** (Maolin). Nếu đã lấy **tồn thật** từ batch + movements: tránh song song làm nguồn sự thật thứ hai; lâu dài nên báo cáo kỳ từ `stock_movements` hoặc bảng snapshot có `period_id`.

| Slug                          | Nghiệp vụ            | Trùng core?                        | Đề xuất                                                               |
| ----------------------------- | -------------------- | ---------------------------------- | --------------------------------------------------------------------- |
| `beginning_inventory`         | Tồn đầu kỳ           | Không cột CF tương ứng trong batch | **Giữ tạm** (import) / **Xóa** khi đã có báo cáo kỳ từ core           |
| `inbound_quantity`            | Nhập kỳ              | Có **nghĩa** movement              | **Giữ tạm** / **Xóa** — ưu tiên tổng từ inbound thực tế               |
| `outbound_quantity`           | Xuất kỳ              | Có **nghĩa** movement              | **Giữ tạm** / **Xóa** — tương tự                                      |
| `beginning_package_inventory` | Tồn đầu (theo thùng) | Không                              | **Giữ tạm** nếu chỉ phục vụ file nguồn; không dùng làm tồn vật lý     |
| `packaging_inbound_quantity`  | Nhập theo quy cách   | Không                              | **Giữ tạm** / **Xóa** theo policy một nguồn                           |
| `packaging_outbound_quantity` | Xuất theo quy cách   | Không                              | **Giữ tạm** / **Xóa** theo policy một nguồn                           |
| `recent_inbound_date`         | Ngày nhập gần nhất   | Có **nghĩa** (từ movement)         | **Xóa** nếu báo cáo query từ lịch sử nhập                             |
| `batch_recent_inbound_date`   | Ngày nhập theo lô    | Tương tự                           | **Xóa** nếu đủ dữ liệu movement                                       |
| `closing_code`                | Mã khóa sổ / kỳ      | Không                              | **Giữ CF** nếu nghiệp vụ kế toán cần; không bắt buộc chuyển core ngay |

### 4.3) Tuỳ doanh nghiệp — có thể trùng Product / chưa có cột chuẩn

| Slug             | Nghiệp vụ        | Trùng core?                         | Đề xuất                                                               |
| ---------------- | ---------------- | ----------------------------------- | --------------------------------------------------------------------- |
| `location_code`  | Vị trí kệ / bin  | Chưa có bảng location chuẩn         | **Giữ CF** tạm hoặc sau này thêm `warehouse_locations`                |
| `specification`  | Quy cách / mô tả | Có thể trùng `products` / mô tả SP  | **Giữ** nếu khác tầng dòng tồn; **Xóa** nếu trùng thuộc tính sản phẩm |
| `packaging_unit` | Đơn vị đóng gói  | Có thể trùng `unit_id` / quy đổi SP | **Rà** với Product — trùng thì **Xóa**                                |
| `small_unit`     | Đơn vị nhỏ       | Tương tự                            | **Rà** với Product — trùng thì **Xóa**                                |

### 4.4) Luồng PO / DO / SO / Invoice và CF Inventory

- **Chi tiết & bảng PASS/FAIL:** [`multi_warehouse_audit_report.md`](multi_warehouse_audit_report.md).
- **Tóm tắt:** Tồn vật lý chuẩn là **batch + movements**. **PO** (delivered) và **DO** (received, khi bật config) có inbound; **điều chỉnh Inventory** và **chuyển kho** gắn service. **Invoice** có thể xuất kho qua `InvoiceWarehouseStockService` khi bật cấu hình và module Warehouse. **Order (SO)** trong báo cáo rà soát **chưa** được mô tả như luồng outbound chuẩn; **observer thanh toán / legacy adjustment** vẫn cần cảnh báo nếu dùng đa kho.
- **Kết luận cho việc dọn CF:** Chỉ khi đã thống nhất **một nguồn tồn** (batch) và import/báo cáo không còn phụ thuộc cột CF trùng core, mới nên **xóa hàng loạt** các slug mục §4.1; snapshot §4.2 xử lý sau hoặc thay bằng báo cáo kỳ.

**CF tạo tay / slug ngoài bảng:** Mọi field admin thêm trên Settings (hoặc label trùng “Ending Inventory” nhưng kiểu `text`) **không** nằm trong migration — luôn đối chiếu kết quả **§7** (`cfg.model` = `Modules\Purchase\Entities\PurchaseInventory`). Cột **`ending_inventory`** trong template import chuẩn là **cột hệ thống** của [`InventoryImport`](../Modules/Purchase/Imports/InventoryImport.php), không phải slug CF seed repo (trừ khi tạo CF trùng tên hiển thị).

---

## 5) Order — `App\Models\Order`

**Repo:** không seed slug — chỉ nhóm “Order”.  
**CF trên UI = admin tạo** — đối chiếu nhanh:

| Kiểu field (ước lượng)             | Trùng core?   | Core                                                        |
| ---------------------------------- | ------------- | ----------------------------------------------------------- |
| HS / HSN                           | Có            | `order_items.hsn_sac_code`, `products.hsn_sac_code`         |
| UOM                                | Có            | `orders.unit_id`, `order_items.unit_id`                     |
| Storage / cert / category sản phẩm | Có            | `products.*`                                                |
| Kho / địa chỉ kho text             | Có / sai tầng | `client_details.default_warehouse_id`, `company_address_id` |
| Note, ngày, tổng, số đơn           | Có            | Cột `orders`                                                |

---

## 6) Invoice & PO — slug **lịch sử** (đã bỏ seed file + dọn bởi `2026_03_30`)

Chỉ dùng để **đối chiếu** nếu SQL vẫn thấy sót sau migrate.

### Invoice (`App\Models\Invoice`)

| Slug                                                                                                | Trùng / lệch core        | Core thay thế                                       |
| --------------------------------------------------------------------------------------------------- | ------------------------ | --------------------------------------------------- |
| `batch_number`, `expiry_date`                                                                       | Sai cấp header           | Dòng HĐ / lô tồn / DO                               |
| `storage_condition`, `unit_of_measure`, `hs_code`, `certification_tag`, `internal_product_category` | Trùng                    | `products`, `invoice_items.unit_id`, `hsn_sac_code` |
| `cost_per_unit`                                                                                     | Không trùng `unit_price` | Giữ nếu cần cost                                    |
| `delivery_reference_no`, `purchase_order_reference`                                                 | Không cột HĐ             | Giữ nếu cần ref                                     |
| `delivery_fee`                                                                                      | Không cột shipping       | Giữ / CF tay / cột sau                              |

### Purchase Order (`Modules\Purchase\Entities\PurchaseOrder`)

| Slug                                                                              | Trùng / lệch core                                                                                                                                                    |
| --------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `expected_delivery_date`, `destination_warehouse_*`                               | Trùng — migrate dọn [`2026_03_27...`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php)                                             |
| `batch_tracking_required`, `erp_po_reference`, `wms_po_reference`, `delivery_fee` | Đã target dọn demo [`2026_03_30...`](../database/migrations/2026_03_30_140000_remove_legacy_invoice_po_cf_seed_migrations_and_demo_fields.php) — tạo tay lại nếu cần |

---

## 7) SQL — xuất **đúng** CF đang có trên DB của bạn

```sql
SELECT
  cfg.model,
  cfg.name   AS group_name,
  cf.id      AS custom_field_id,
  cf.name    AS slug,
  cf.label,
  cf.type,
  cf.company_id
FROM custom_fields cf
JOIN custom_field_groups cfg ON cfg.id = cf.custom_field_group_id
ORDER BY cfg.model, cf.company_id, cf.name;
```

Export ra Excel/Sheets, cột “Xóa?” tick theo các bảng trên.

---

## 8) Lead (không phải `custom_fields` table)

[`2023_03_17_045842_lead_custom_field.php`](../database/migrations/2023_03_17_045842_lead_custom_field.php) dùng **`lead_custom_forms`** (`product`, `source`) — không nằm bảng `custom_fields`. Xử lý riêng nếu cần.

---

_Cập nhật: 2026-03-27 — mở rộng §4 (Inventory) theo `2026_02_02` merged + phân loại core / snapshot / tuỳ DN + §4.4 luồng kho. Không thay thế truy vấn thực tế §7._
