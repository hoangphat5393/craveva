# Multi-Warehouse Custom Fields Rationalization (Chi tiết cột nên giữ/bỏ)

**Liên quan MAOLIN:** [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) (mục lục), [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) (map cột import).

**Cách làm:** Xóa custom field **trong hệ thống** (Settings → Custom Fields theo từng module). **Không cần tạo migration** — admin tự gỡ khi đã chắc dữ liệu nằm ở cột/form chuẩn.

---

## Quy ước cập nhật tài liệu (áp dụng cho mọi việc sau này)

- Mọi thay đổi liên quan **đa kho**, **custom field**, **import MAOLIN**, hoặc **quyết định nghiệp vụ** tương tự → **ghi chú hoặc sửa file** trong `FUNC_LOGIC/` (không chỉ trao đổi miệng).
- **Ưu tiên cập nhật:** file này (CF), [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) (nếu thêm/đổi tài liệu MAOLIN), và file chuyên đề tương ứng (ví dụ import → `MAOLIN_IMPORT_MAPPING.md`).
- Khi **đổi danh sách CF trên UI** (thêm/xóa field): cập nhật mục **Snapshot UI** bên dưới hoặc thêm một dòng vào **Lịch sử cập nhật**.

---

Tài liệu này chốt: sau khi đã có multi-warehouse và cột DB tương ứng, **custom field nào trùng lặp nên gỡ**, cái nào **giữ** cho BI/legacy.

**Lưu ý:** Custom field lưu theo **từng company**. Bảng slug bám migration seed trong repo; trên tenant có thể có thêm field tạo tay — đối chiếu **Settings → Custom Fields**.

---

## Snapshot UI — Staging (đối chiếu nhãn màn hình)

_Bản chốt theo ảnh chụp Settings → Custom Fields (Inventory ~20 field, Product 3 field, Client 9 field). Khi danh sách UI đổi, cập nhật lại mục này._

### Inventory — nhãn hiển thị (Module Label)

| Nhãn UI                     | Nên bỏ?  | Lý do                                                                                    |
| --------------------------- | -------- | ---------------------------------------------------------------------------------------- |
| Expiry Date                 | **Bỏ**   | Trùng `purchase_stock_adjustments.expiration_date` (CF tên có thể là `expiry_date`).     |
| Near-Expiry Status          | **Bỏ**   | Suy ra từ `expiration_date` + ngưỡng / báo cáo; không nhập tay.                          |
| Reserved Quantity           | **Bỏ**   | Tồn giữ chỗ lấy từ module Warehouse / reservation, không lưu CF.                         |
| Specification               | **Bỏ**   | Trùng thông tin master sản phẩm (`products` / `PurchaseProduct.specification`).          |
| Packaging Unit              | **Bỏ**\* | Trùng nghiệp vụ đơn vị sản phẩm (`unit_id` / quy cách); CF text dễ lệch.                 |
| Small Unit                  | **Bỏ**\* | Cùng lý do đơn vị master.                                                                |
| Beginning Inventory         | **Bỏ**   | Snapshot kỳ (file ERP), không phải input movement/điều chỉnh tồn đa kho.                 |
| Inbound Quantity            | **Bỏ**   | Nhập/xuất thật nằm trong movement / chứng từ.                                            |
| Outbound Quantity           | **Bỏ**   | Cùng lý do.                                                                              |
| Ending Inventory            | **Bỏ**   | Tồn lấy từ bảng tồn/lô; kiểu text càng không phù hợp.                                    |
| Recent Inbound Date         | **Bỏ**   | Ngày nhập gần nhất nên tính từ dữ liệu.                                                  |
| Beginning Package Inventory | **Bỏ**   | Snapshot kỳ theo gói — không dùng làm core tồn.                                          |
| Batch Recent Inbound Date   | **Bỏ**   | Cùng lý do “ngày nhập theo lô”.                                                          |
| Closing Code                | **Bỏ**   | Mã kỳ/đóng sổ file khách; không gắn movement (trừ khi nghiệp vụ bắt buộc giữ).           |
| Location Code               | **Tùy**  | Giữ chỉ khi thật sự quản lý vị trí kệ/bin và chưa có module location; không dùng thì bỏ. |

\*Nếu team vẫn cần hai đơn vị song song ngoài master SP, tạm giữ đến khi chuẩn hóa — mặc định vẫn **nên bỏ** khi đã thống nhất `unit_id`.

**Các slug thường gặp khác trên Inventory (nếu còn trong list ~20 field):** `warehouse_code`, `warehouse_name`, `batch_number`, `manufacturing_date` → **bỏ** (đã có `warehouse_id` + cột lô/ngày trên chứng từ điều chỉnh).

### Product — nhãn hiển thị (3 field)

| Nhãn UI        | Nên bỏ? | Lý do                                |
| -------------- | ------- | ------------------------------------ |
| Product Source | **Bỏ**  | Trùng cột `products.product_source`. |
| Brand          | **Bỏ**  | Trùng cột `products.brand`.          |
| Product Grade  | **Bỏ**  | Trùng cột `products.product_grade`.  |

### Client — nhãn hiển thị (9 field)

| Nhãn UI                                                                                                                                                 | Nên bỏ? | Lý do                                                                                                                                 |
| ------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| Channel Type, Salesperson, Department, Sales Assistant Name, Customer Grade, Business Type, Last Transaction Date, Payment Terms, Business Closure Date | **Giữ** | Không trùng với đa kho; thuộc tính KH. Kho ưu tiên dùng **`default_warehouse_id`** trên `client_details`, không nằm trong các CF này. |

---

## 0) Nguồn đối chiếu trong code (migrations)

| Module    | Model / group        | File migration (seed custom fields)                                                                                                                   |
| --------- | -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Inventory | `PurchaseInventory`  | `2026_01_14_130000_add_inventory_custom_fields_fb.php`                                                                                                |
| Product   | `App\Models\Product` | `2026_01_12_190624_add_product_custom_fields_fb.php`, `2026_01_14_120000_add_additional_product_custom_fields_fb.php`                                 |
| Client    | `ClientDetails`      | `2026_03_09_100000_add_client_custom_fields_for_miaolin.php`, `2026_03_09_110000_add_client_custom_fields_last_transaction_payment_terms_closure.php` |

---

## 1) Inventory (`PurchaseInventory` — group "Inventory")

### 1.1 Đã có trên DB core (bảng `purchase_stock_adjustments`)

| Cột DB               | Ghi chú                                   |
| -------------------- | ----------------------------------------- |
| `warehouse_id`       | Khóa kho                                  |
| `batch_number`       | Lô                                        |
| `manufacturing_date` | Ngày SX                                   |
| `expiration_date`    | Hạn dùng (CF cũ có thể tên `expiry_date`) |
| `net_quantity`       | Số lượng dòng điều chỉnh                  |

### 1.2 Custom field do migration tạo — **nên bỏ** (trùng core hoặc không còn vai trò vận hành chính)

| Tên CF (`name`)      | Lý do                                            |
| -------------------- | ------------------------------------------------ |
| `warehouse_code`     | Đã có `warehouse_id` + master `warehouses`.      |
| `warehouse_name`     | Trùng chức năng với master kho + `warehouse_id`. |
| `batch_number`       | Trùng cột DB.                                    |
| `manufacturing_date` | Trùng cột DB.                                    |
| `expiry_date`        | Trùng cột DB `expiration_date`.                  |
| `near_expiry_status` | Tính từ `expiration_date` + ngưỡng / báo cáo.    |
| `reserved_quantity`  | Lấy từ Warehouse / reservation.                  |

### 1.3 Custom field **có thể giữ** (không trùng core trong migration)

| Tên CF          | Ghi chú                                    |
| --------------- | ------------------------------------------ |
| `location_code` | Chỉ giữ nếu quản lý vị trí kệ/bin thật sự. |

### 1.4 Tên chỉ tạo tay / MAOLIN (không trong seed repo)

`beginning_inventory`, `inbound_quantity`, `outbound_quantity`, `ending_inventory`, … — xem bảng **Snapshot UI** phía trên.

---

## 2) Client (group "Client")

### 2.1 Cột core DB (`client_details`)

| Cột                    | Ghi chú      |
| ---------------------- | ------------ |
| `client_code`          | Khóa import  |
| `pricing_tier_id`      | Tier pricing |
| `default_warehouse_id` | Kho ưu tiên  |

### 2.2 Custom field Miaolin — **nên giữ** (chưa có cột DB tương ứng trong scope hiện tại)

`salesperson`, `department`, `sales_assistant_name`, `channel_type`, `business_type`, `last_transaction_at`, `payment_terms`, `business_closure_date`.

### 2.3 `customer_grade` / `region`

Có thể có trên UI (tạo tay hoặc import) — giữ đến khi chuẩn hóa DB. **Không** tạo CF mới cho kho dài hạn — dùng `default_warehouse_id`.

---

## 3) Product (group "Product")

### 3.1 Trùng cột `products` — **nên xóa CF**

`storage_condition`, `certification`, `brand`, `shelf_life_days` (seed migration), và theo UI staging: **Product Source**, **Product Grade** (trùng `product_source`, `product_grade`).

### 3.2 CF không trùng cột DB — có thể **giữ**

`batch_tracking_enabled`, `inventory_issue_rule`, `near_expiry_days_threshold`, `near_expiry_discount_eligible`, `erp_sku_mapping`, `wms_sku_mapping`.

---

## 4) Danh sách xóa nhanh (theo slug — đối chiếu UI)

### Inventory

`warehouse_code`, `warehouse_name`, `batch_number`, `manufacturing_date`, `expiry_date`, `near_expiry_status`, `reserved_quantity`, và các field snapshot/ERP như mục Snapshot UI.

**Tùy chọn:** `location_code`.

### Product

`storage_condition`, `certification`, `brand`, `shelf_life_days`, `product_source` (nếu slug trùng), `product_grade` (nếu slug trùng) — cùng nhãn **Product Source / Brand / Product Grade** trên UI.

### Client

Không xóa bộ Miaolin mặc định cho đến khi có cột DB + chuyển dữ liệu.

---

## 5) Checklist trước khi xóa CF trong UI

- [ ] (Khuyến nghị) Backup DB nếu cần rollback.
- [ ] Đối chiếu nhãn/slug trong **Settings → Custom Fields** với mục 4 và Snapshot UI.
- [ ] Vài bản ghi Product / Inventory: dữ liệu đã ở form/cột chuẩn.
- [ ] Import thử nếu team vẫn dùng import.

---

## 6) Vận hành sau khi dọn

- **Đa kho:** `warehouse_id`, lô `batch_number`, hạn `expiration_date`, movement `stock_movements` / `warehouse_product_batches`.
- **CF:** chỉ thuộc tính phụ / BI — không là nguồn sự thật tồn/lô.

---

## Lịch sử cập nhật

| Ngày (UTC) | Nội dung                                                                                                                         |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------- |
| 2026-03-24 | Thêm quy ước cập nhật `FUNC_LOGIC`; thêm **Snapshot UI Staging** (Inventory/Product/Client) theo ảnh; đồng bộ bảng lý do bỏ/giữ. |
