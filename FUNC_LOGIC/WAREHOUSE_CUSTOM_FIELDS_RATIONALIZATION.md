# Multi-Warehouse Custom Fields Rationalization (Chi tiết cột nên giữ/bỏ)

**Liên quan MAOLIN:** [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) (mục lục), [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) (map cột import).

**Cách làm:** Xóa custom field **trên server** qua **Settings → Custom Fields** (từng module). Đây là cách đủ để gỡ CF — **không cần** viết migration xóa CF; admin tự bỏ trên UI khi đã chắc dữ liệu nằm ở cột/form chuẩn.

---

## Quy ước cập nhật tài liệu (áp dụng cho mọi việc sau này)

- Mọi thay đổi liên quan **đa kho**, **custom field**, **import MAOLIN**, hoặc **quyết định nghiệp vụ** tương tự → **ghi chú hoặc sửa file** trong `FUNC_LOGIC/` (không chỉ trao đổi miệng).
- **Ưu tiên cập nhật:** file này (CF), [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md) (nếu thêm/đổi tài liệu MAOLIN), và file chuyên đề tương ứng (ví dụ import → `MAOLIN_IMPORT_MAPPING.md`).
- Khi **đổi danh sách CF trên UI** (thêm/xóa field): cập nhật mục **Snapshot UI** bên dưới hoặc thêm một dòng vào **Lịch sử cập nhật**.

### Tự động cập nhật sau khi phân tích (không cần user nhắc)

Sau **mỗi lần phân tích** trong phiên làm việc (đối chiếu file MAOLIN, code, UI, import…) mà có **kết luận mới** (bỏ/giữ CF, field DB, mapping, rủi ro):

1. **Sửa ngay** ít nhất một file trong `FUNC_LOGIC/` phù hợp — thường là file này và/hoặc [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md).
2. Thêm **một dòng** vào **Lịch sử cập nhật** (cuối file này): ngày + nội dung tóm tắt kết luận.
3. Nếu kết luận ảnh hưởng mục lục hoặc thứ tự đọc: cập nhật [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md).

**Không** chỉ trả lời trong chat mà bỏ qua bước ghi chú, trừ khi user **tường minh** chỉ hỏi nhanh và không yêu cầu lưu.

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

| Nhãn UI        | Trạng thái (cập nhật)           | Lý do                                                              |
| -------------- | ------------------------------- | ------------------------------------------------------------------ |
| Product Source | **Đã xóa CF trên UI** (2026-03) | Trùng cột `products.product_source` — dùng form/import ghi cột DB. |
| Brand          | **Đã xóa CF trên UI** (2026-03) | Trùng cột `products.brand`.                                        |
| Product Grade  | **Đã xóa CF trên UI** (2026-03) | Trùng cột `products.product_grade`.                                |

_Các CF khác có thể từng seed từ migration (`storage_condition`, `certification`, `shelf_life_days`, …) — nếu đã gỡ trên UI, cùng nguyên tắc: dữ liệu nằm trên bảng `products`._

### Client — nhãn hiển thị (9 field)

| Nhãn UI                                                                                                                                                 | Nên bỏ? | Lý do                                                                                                                                 |
| ------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| Channel Type, Salesperson, Department, Sales Assistant Name, Customer Grade, Business Type, Last Transaction Date, Payment Terms, Business Closure Date | **Giữ** | Không trùng với đa kho; thuộc tính KH. Kho ưu tiên dùng **`default_warehouse_id`** trên `client_details`, không nằm trong các CF này. |

### Import `Craveva full inventory.xlsx` (DigiWin) — sau khi bỏ các CF ở bảng trên

**Kết luận ngắn:** Vẫn **import được** phần nghiệp vụ **cốt lõi** (tồn theo SKU + kho + lô + ngày + quy cách…) vì luồng import dùng **cột hệ thống** trong `Modules/Purchase/Imports/InventoryImport.php` và `ImportInventoryJob` — **không phụ thuộc** vào việc còn CF trùng tên hay không. Các cột đó ghi vào `purchase_stock_adjustments` (và đồng bộ kho), không bắt buộc lưu thêm trong `custom_fields_data`.

**Cột import đã có sẵn trong code (map khi chuẩn bị file / màn map cột):**  
`sku`, `product_name`, `date`, `type`, `warehouse_code`, `warehouse_name`, `quantity`, `ending_inventory`, `cost_price`, `description`, `unit`, `specification`, `batch_number`, `manufacturing_date`, `expiration_date`.

**Phần sẽ _không_ còn lưu được trong ERP qua import** nếu trước đây chỉ gửi vào **CF** và bạn đã xóa CF: các cột kiểu **kỳ kế toán / snapshot DigiWin** (期初庫存, 本期入庫, 本期出庫, 最近入庫日, 結案碼, đơn vị nhỏ/đóng gói tách khỏi `unit`, …) — trong `ImportInventoryJob` **không** có id cố định tương ứng để đẩy vào DB core; chúng chỉ từng có ý nghĩa nếu map sang `field_*` (CF). Bỏ CF = mất chỗ lưu các cột đó **trong hệ thống**, nhưng **không làm hỏng** import phần đã map vào danh sách cột hệ thống ở trên.

**Thực tế với file MAOLIN:** Sheet `庫存明細總表` có nhiều cột — cần map đúng sang các id import chuẩn (ưu tiên **期末庫存** → `ending_inventory` hoặc `quantity`, **批號/日期/庫別** → `batch_number` / `expiration_date` / `warehouse_code`, …). Chi tiết cột file: [`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md), [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md).

---

## Kết luận dựa trên 3 file DigiWin (MAOLIN) — bỏ/giữ CF & core DB

**Nguồn file đã đối chiếu (2026-03):**

- `PROJECT MAOLIN New/Craveva product.xlsx` (sheet `商品`)
- `PROJECT MAOLIN New/Quote, unit price, inventory.xlsx` (sheet `產品價格表`, `產品庫存表`)
- `PROJECT MAOLIN New/Craveva full inventory.csv` (batch stock snapshot: SKU + Expiration Date + Lot + Inventory + Warehouse Name)

### A) Custom field nên bỏ (Inventory)

Các field bên dưới tương ứng trực tiếp các cột “kỳ kế toán / snapshot DigiWin” trong sheet `產品庫存表` và/hoặc các field không nên nhập tay:

- `Beginning Inventory` (期初庫存)
- `Inbound Quantity` (本期入庫)
- `Outbound Quantity` (本期出庫)
- `Ending Inventory` (期末庫存) — số này phải đi vào core stock/batch (không lưu CF rời)
- `Recent Inbound Date` (最近入庫日)
- `Batch Recent Inbound Date` (批號最近入庫日)
- `Beginning Package Inventory` (期初包裝庫存)
- `Packaging Inbound Quantity` (本期包裝入庫)
- `Packaging Outbound Quantity` (本期包裝出庫)
- `Closing Code` (結案碼) — chỉ giữ nếu có quy trình “đóng lô” thật sự trong ERP
- `Near-Expiry Status` — suy ra từ HSD + ngưỡng
- `Reserved Quantity` — lấy từ reservation, không nhập tay

Các field “trùng master” cũng nên bỏ để tránh lệch dữ liệu:

- `Specification` — đã là master Product (`products.specification`)
- `Packaging Unit`, `Small Unit` — chỉ giữ nếu có mô hình chuẩn cho packaging (hiện tại ưu tiên chuẩn hóa về unit/master)

### B) Custom field có thể giữ

- `Location Code` — chỉ giữ nếu có quản lý vị trí kệ/bin thật sự.

### C) Field bắt buộc phải là DB/core (để import được & chạy multi-warehouse đúng)

**Product (từ `Craveva product.xlsx` + giá từ `產品價格表`):**

- `sku` (品號)
- `name` (品名)
- `specification` (規格)
- `unit_id` / unit (庫存單位)
- `inventory_type` (備貨型態)
- `storage_condition` (儲存溫層)
- `shelf_life_days` (保存天數)
- `brand` (品牌類別)
- `product_grade` (商品級別)
- Giá từ sheet `產品價格表`: `price` (標準售價), `wholesale_price` (中盤價), `price_per_box` (成箱價), `employee_price` (員工價)

**Warehouse + Inventory (từ `產品庫存表` + `Craveva full inventory.csv`):**

- Warehouse master: `warehouses.code`, `warehouses.name` → resolve thành `warehouse_id`
- Tồn theo kho/lô (core): `warehouse_id`, `product_id`, `batch_number` (批號), `manufacturing_date` (製造日期, nếu có), `expiration_date` (有效日期), `quantity` (tồn; ưu tiên 期末庫存 hoặc Inventory snapshot)

**Không nên là cột core Product:** `Expiry Date` ở product master (失效日期) — HSD thuộc về **batch/lot**, không thuộc product master.

---

## 0) Nguồn đối chiếu trong code (migrations)

| Module    | Model / group        | File migration (seed custom fields)                                                                                                                   |
| --------- | -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Inventory | `PurchaseInventory`  | `2026_01_14_130000_add_inventory_custom_fields_fb.php`                                                                                                |
| Product   | `App\Models\Product` | `2026_01_12_190624_add_product_custom_fields_fb.php`, `2026_01_14_120000_add_additional_product_custom_fields_fb.php`                                 |
| Client    | `ClientDetails`      | `2026_03_09_100000_add_client_custom_fields_for_miaolin.php`, `2026_03_09_110000_add_client_custom_fields_last_transaction_payment_terms_closure.php` |

---

## 1) Inventory (`PurchaseInventory` — group "Inventory")

### 1.1 Cột DB trên chứng từ tồn — **không phải Custom Field** (sẽ không thấy trong Settings → Custom Fields)

Các cột sau nằm ở bảng `purchase_stock_adjustments` và form **Purchase Inventory** — **không** xuất hiện dưới dạng dòng trong màn **Custom Fields**. Nếu bạn chỉ mở Custom Fields mà không thấy `warehouse_id`, `batch_number`, … là **đúng**: đó không phải CF.

| Cột DB               | Ghi chú                                     |
| -------------------- | ------------------------------------------- |
| `warehouse_id`       | Khóa kho (chọn kho trên form / import map). |
| `batch_number`       | Lô                                          |
| `manufacturing_date` | Ngày SX                                     |
| `expiration_date`    | Hạn dùng                                    |
| `net_quantity`       | Số lượng dòng điều chỉnh                    |

### 1.2 Custom field Inventory — đối chiếu UI vs slug trong code

- Trong **Settings → Custom Fields** bạn chỉ thấy những **Module Label** (nhãn) đã được tạo cho company — ví dụ ảnh chụp: _Location Code_, _Expiry Date_, _Near-Expiry Status_, _Reserved Quantity_, _Specification_, _Beginning Inventory_, …
- Migration `2026_01_14_130000_add_inventory_custom_fields_fb.php` trong repo có thể seed thêm các slug: `warehouse_code`, `warehouse_name`, `batch_number`, `manufacturing_date`, `expiry_date`, … **Trên từng server có thể khác:** đã xóa tay, hoặc tenant tạo thêm field khác (MAOLIN) → **danh sách UI không nhất thiết trùng hết bảng dưới.**

**Nếu slug sau vẫn còn trong Custom Fields** (nhãn UI có thể là "Warehouse Code", "Batch / Lot Number", …) — **nên bỏ** vì trùng hoặc thay được bằng core:

| Slug (`name`) thường gặp | Nhãn UI gợi ý      | Lý do bỏ                                                                                                           |
| ------------------------ | ------------------ | ------------------------------------------------------------------------------------------------------------------ |
| `warehouse_code`         | Warehouse Code     | Đã có `warehouse_id` + master `warehouses`.                                                                        |
| `warehouse_name`         | Warehouse Name     | Trùng chức năng master kho.                                                                                        |
| `batch_number`           | Batch / Lot Number | Trùng cột DB `purchase_stock_adjustments.batch_number`.                                                            |
| `manufacturing_date`     | Manufacturing Date | Trùng cột DB.                                                                                                      |
| `expiry_date`            | **Expiry Date**    | Trùng cột DB `expiration_date` (trên ảnh của bạn vẫn có nhãn _Expiry Date_ — đúng loại cần gỡ nếu đã dùng cột DB). |
| `near_expiry_status`     | Near-Expiry Status | Nên tính từ `expiration_date` + báo cáo.                                                                           |
| `reserved_quantity`      | Reserved Quantity  | Lấy từ Warehouse / reservation.                                                                                    |

### 1.3 Custom field **có thể giữ** (tùy nghiệp vụ)

| Slug / nhãn thường gặp | Ghi chú                                                      |
| ---------------------- | ------------------------------------------------------------ |
| `location_code`        | _Location Code_ — chỉ giữ nếu quản lý vị trí kệ/bin thật sự. |

### 1.4 Field thường thấy trên UI (tạo tay / MAOLIN — không nằm trong migration seed repo)

_Như ảnh: Specification, Packaging Unit, Small Unit, Beginning Inventory, Inbound/Outbound Quantity, Ending Inventory, Recent Inbound Date, Beginning Package Inventory, …_ — xem bảng **Snapshot UI** ở đầu file; **nên bỏ** theo lý do snapshot (trùng master SP, snapshot kỳ, v.v.).

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

## 7) Kiểm tra code — sau khi gỡ CF Product trên UI

**Kết luận (đã rà repo):** Không có chỗ code **bắt buộc** phải còn custom field Product. Có thể giữ nguyên code; các điểm dưới chỉ là **luồng generic** (vẫn chạy khi 0 CF).

| Khu vực                                             | Ghi chú                                                                                                                                                                                                                                                    |
| --------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `ProductController` (create/edit/show)              | Gọi `getCustomFieldGroupsWithFields()` — nếu không còn field thì `$fields` không set hoặc rỗng; view `products/ajax/*.blade.php` dùng `<x-forms.custom-field>` — component chỉ render khi `isset($fields) && count($fields) > 0`.                          |
| `StoreProductRequest` / `UpdateProductRequest`      | `CustomFieldsRequestTrait` chỉ thêm rule khi request có `custom_fields_data` và từng key tồn tại.                                                                                                                                                          |
| `ProductController@store` / `update`                | `updateCustomFieldData()` chỉ gọi khi `$request->custom_fields_data` truthy — không gửi form CF thì bỏ qua.                                                                                                                                                |
| `ProductsDataTable` / `PurchaseProductsDataTable`   | `CustomField::customFieldData`, `CustomFieldGroup::customFieldsDataMerge` — không còn CF thì không thêm cột động; cột `product_source`, `brand`, `product_grade` vẫn là **cột DB** trong `getColumns()`. Đã có `excludeKeys` để tránh trùng tên với CF cũ. |
| `ImportProductChunkJob`                             | `buildProductCustomFieldsData()`: mảng `$customFieldNames` đang **rỗng** → không map thêm CF từ file import.                                                                                                                                               |
| `ImportProductJob` / `ProductImport`                | Map `storage_condition`, `certification`, … vào **cột `products`**, không qua CF.                                                                                                                                                                          |
| Module **Purchase** (`PurchaseProduct` create/edit) | `storage_condition`, `certification` là **input cột DB** (`PurchaseProduct`), không phải CF của `App\Models\Product`.                                                                                                                                      |

**Migration trong repo (tùy chọn, không bắt buộc):** Có file `database/migrations/2026_01_21_000001_remove_all_product_custom_fields_fb.php` — chỉ dùng khi team muốn **dọn DB qua `migrate`** (ví dụ môi trường dev / migrate fresh). **Vận hành production:** bỏ CF bằng **UI trên server** là đủ; **không** cần chạy migration này để thay thế việc xóa tay trong Settings.

**Dữ liệu cũ:** Bản ghi trong `custom_fields_data` gắn `field_id` đã xóa có thể còn sót — thường không lỗi UI; dọn DB là tùy chọn (backup trước).

---

## Lịch sử cập nhật

| Ngày (UTC) | Nội dung                                                                                                                                                                 |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 2026-03-24 | Thêm quy ước cập nhật `FUNC_LOGIC`; thêm **Snapshot UI Staging** (Inventory/Product/Client) theo ảnh; đồng bộ bảng lý do bỏ/giữ.                                         |
| 2026-03-24 | **Product CF:** đánh dấu đã xóa trên UI; thêm **mục 7** — kiểm tra code, không bắt buộc sửa thêm.                                                                        |
| 2026-03-24 | Làm rõ: **bỏ CF = chỉ cần UI trên server**; migration xóa CF trong repo là **tùy chọn**, không bắt buộc thay thế UI.                                                     |
| 2026-03-24 | **Inventory §1:** Phân biệt cột DB (không hiện trong Custom Fields) vs CF trên UI (danh sách từng tenant có thể khác migration).                                         |
| 2026-03-24 | Thêm mục **Import Craveva full inventory / DigiWin sau khi bỏ CF** — import cốt lõi vẫn chạy; cột chỉ từng lưu qua CF thì mất chỗ lưu.                                   |
| 2026-03-25 | Đối chiếu 3 file DigiWin (`Craveva product.xlsx`, `Quote...xlsx`, `Craveva full inventory.csv`) → chốt CF Inventory bỏ/giữ và field core DB bắt buộc.                    |
| 2026-03-25 | Quy ước **tự động cập nhật ghi chú sau phân tích** (không cần user nhắc); cập nhật chéo [`MAOLIN_INDEX.md`](MAOLIN_INDEX.md).                                            |
| 2026-03-25 | Bổ sung kết luận cho `customer do.txt`: bỏ hết Client CF vẫn import core, nhưng mất cột business đang map CF; đề xuất chuẩn hóa DB ở `MAOLIN_IMPORT_MAPPING.md` mục 1.3. |
