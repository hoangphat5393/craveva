# Custom field — đề xuất gỡ (trùng cột chuẩn): PO, DO, SO, Invoice, Client

**Mục đích:** Một chỗ tra cứu các **custom field (CF)** đang **trùng nghĩa / trùng dữ liệu** với cột bảng gốc — nên **bỏ CF** (sau khi migrate dữ liệu nếu cần) để tránh hai nguồn lệch nhau.

---

## Trạng thái codebase (đã kiểm tra lại)

| Việc đã làm              | Chi tiết                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Seed migration PO/DO** | [`2026_01_15_000006...`](../database/migrations/2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb.php) **không seed** CF prototype kho đích `destination_warehouse_code` / `destination_warehouse_name` (trước multi-warehouse); kho PO dùng **`purchase_orders.warehouse_id`**. Vẫn **không** seed 3 CF trùng cột (PO `expected_delivery_date`, DO ERP/WMS ref). **CF `delivery_fee` PO:** có thể **đã gỡ** trên Settings (không bắt buộc đồng bộ với DO/Invoice). |
| **Migration dọn DB cũ**  | [`2026_03_27_140000...`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php) — xóa dữ liệu + bản ghi CF: PO `expected_delivery_date`, `destination_warehouse_*`, DO ERP/WMS ref. [`2026_03_28_110000...`](../database/migrations/2026_03_28_110000_remove_po_destination_warehouse_custom_fields.php) — dọn riêng hai slug `destination_warehouse_*` cho DB đã chạy bản `2026_03_27` cũ (idempotent). Chạy: `php artisan migrate`.                    |
| **UI / controller**      | PO: `expected_date` → `purchase_orders.expected_delivery_date`; **kho** → `purchase_orders.warehouse_id` (select trên form create/edit). DO: ERP/WMS ref + **`delivery_fee`** → **cột** `delivery_orders` ([`DeliveryOrderController`](../Modules/Purchase/Http/Controllers/DeliveryOrderController.php)). **Không** đọc các slug đó qua CF.                                                                                                                                         |
| **CF còn lại trên form** | PO: batch, ERP/WMS ref PO; **`delivery_fee` PO** chỉ hiện nếu CF vẫn còn trong DB (có thể **đã xóa** trên Settings). **Order (SO):** **đã gỡ hết CF** — block CF trên form có thể trống; nghiệp vụ SO/HĐ/kho xem **§3.2**. **DO:** không còn nhóm CF; form = cột + `delivery_order_items`. **Invoice:** nhóm CF + `delivery_fee` bán — **§3a**.                                                                                                                                      |
| **DO — native + dọn CF** | [`2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php): cột `delivery_orders.delivery_fee`, `delivery_order_items` (batch/expiry/picking), xóa toàn bộ CF + nhóm Delivery Order. UI: kho, phí giao, lô/HSD/rule theo dòng; observer đọc từ dòng khi nhập kho.                                                                                   |

**Sau khi bạn đã xóa tay trên UI (nếu có):** chạy `php artisan migrate` (gồm `2026_03_27_140000...`, `2026_03_28_100000...`, `2026_03_28_110000...` khi cần).

**Liên quan:** Quy trình nghiệp vụ [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md) · Luồng kỹ thuật bán/mua [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md) · Client/import [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md)

---

## Nguyên tắc trước khi xóa

1. **Sao lưu** `custom_fields_data` (theo `custom_field_id` / `model` / `model_id`) cho từng field sắp xóa.
2. **Copy giá trị** sang cột chuẩn nếu cột đang trống và CF đang có dữ liệu (script SQL hoặc job một lần).
3. Xóa theo thứ tự: dữ liệu CF → bản ghi `custom_fields` → (tuỳ chọn) chỉnh form/import không còn map cột đó.
4. Nhóm CF gắn **model** cố định: PO = `Modules\Purchase\Entities\PurchaseOrder`, DO = `App\Models\DeliveryOrder`, SO = `App\Models\Order`, Client = `App\Models\ClientDetails`.

---

## 1) Purchase Order (PO)

| Slug CF (name)               | Trùng với                                    | Ghi chú                         | Ghi chú kỹ thuật                                                                                                                                                                                                                                                                 |
| ---------------------------- | -------------------------------------------- | ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`expected_delivery_date`** | Cột `purchase_orders.expected_delivery_date` | **Nên gỡ** — chỉ dùng cột bảng. | **Repo:** không còn seed trong [`2026_01_15_000006...`](../database/migrations/2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb.php); dọn DB bằng [`2026_03_27_140000...`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php). |

**CF PO còn được seed trong repo (slug):**  
[`2026_01_15_000006...`](../database/migrations/2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb.php): `batch_tracking_required`, `erp_po_reference`, `wms_po_reference`.  
[`2026_01_18_000100...`](../database/migrations/2026_01_18_000100_add_delivery_fee_custom_fields_for_po_and_do_fb.php): **`delivery_fee` chỉ cho PO** (DO dùng cột bảng, không seed CF DO).

**Đã gỡ khỏi seed / nên xóa khỏi DB (UI Settings có thể vẫn hiện cho tới khi migrate dọn hoặc xóa tay):**  
`destination_warehouse_code`, `destination_warehouse_name` → thay bằng **`purchase_orders.warehouse_id`** trên form PO.

### 1.1 Phí giao (**delivery fee**) — bỏ ở PO hay DO?

| Chứng từ | Trạng thái trong code                                                                                                                                                                                                                                                                            | Khuyến nghị                                                                                                                                                                                                               |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **DO**   | **Đã là cột core** `delivery_orders.delivery_fee` ([`2026_03_28_100000...`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php)); form create/edit bind trực tiếp; **nhóm CF Delivery Order đã bỏ** — **không** giữ CF delivery fee trên DO. | Coi phí giao của **phiếu giao/nhận** là thuộc DO.                                                                                                                                                                         |
| **PO**   | Hiện vẫn có thể là **CF** `delivery_fee` (seed [`2026_01_18...`](../database/migrations/2026_01_18_000100_add_delivery_fee_custom_fields_for_po_and_do_fb.php)); **chưa** có cột `purchase_orders.delivery_fee` trong repo.                                                                      | Nếu nghiệp vụ là “phí vận chuyển gắn hợp đồng mua / PO”: **nên thêm cột PO + UI + bỏ seed CF**; nếu chỉ phát sinh khi có lần giao thì **chỉ lưu trên DO** và có thể **gỡ CF delivery_fee khỏi PO** sau khi thống nhất PM. |

### 1.2 Các CF PO trên Settings — có nên chuyển **core DB**?

| Slug (Settings)                         | Nên core?                                                   | Ghi chú ngắn                                                                                                                                                                                                                                                   |
| --------------------------------------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `batch_tracking_required`               | **Nên** (boolean `purchase_orders` hoặc rule theo sản phẩm) | Dễ validate trước DO `received` / nhập kho; MAOLIN file **không** có cột map trực tiếp — chuyển core là quyết định nghiệp vụ PO.                                                                                                                               |
| `erp_po_reference` / `wms_po_reference` | **Nên** (nullable string trên `purchase_orders`)            | Giống DO đã có `erp_shipment_reference` / `wms_shipment_reference`; hợp sync ERP/WMS, index, API.                                                                                                                                                              |
| `delivery_fee`                          | **Tuỳ** (xem §1.1)                                          | DO đã có cột; PO nên cột riêng **hoặc** bỏ CF nếu không dùng.                                                                                                                                                                                                  |
| `destination_warehouse_*`               | **Không** — đã thay bằng `warehouse_id`                     | Xóa CF trong Settings sau migrate [`2026_03_27...`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php) / [`2026_03_28_110000...`](../database/migrations/2026_03_28_110000_remove_po_destination_warehouse_custom_fields.php). |

---

## 2) Delivery Order (DO) — `App\Models\DeliveryOrder` / bảng `delivery_orders`

| Slug CF (name)               | Trùng với                                    | Ghi chú                     | Ghi chú kỹ thuật                                                                                                                                                                                                                                                            |
| ---------------------------- | -------------------------------------------- | --------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`erp_shipment_reference`** | Cột `delivery_orders.erp_shipment_reference` | **Nên gỡ** — dùng cột bảng. | **Repo:** không còn seed trong [`2026_01_15_000006...`](../database/migrations/2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb.php); dọn DB [`2026_03_27_140000...`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php). |
| **`wms_shipment_reference`** | Cột `delivery_orders.wms_shipment_reference` | **Nên gỡ** — dùng cột bảng. | Cùng migration dọn trên                                                                                                                                                                                                                                                     |

Model tham chiếu cột chuẩn: [`app/Models/DeliveryOrder.php`](../app/Models/DeliveryOrder.php) (`$fillable`).

**Lịch sử:** nhóm CF DO (batch, source warehouse, delivery fee, …) **đã xóa** khỏi DB khi chạy [`2026_03_28_100000...`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php). Chuẩn hiện tại: **`warehouse_id`**, **`delivery_fee`**, lô/HSD/rule **theo dòng** `delivery_order_items`; observer đọc từ item khi nhập kho.

**Lịch sử model CF:** nhóm DO từng là `App\Models\OrderDelivery`, đã đổi sang `DeliveryOrder` — [`2026_01_16_130500...`](../Modules/Purchase/Database/Migrations/2026_01_16_130500_update_custom_field_model_for_delivery_orders.php).

---

## 3) Sales Order (SO) — `App\Models\Order` / bảng `orders`

| Slug CF      | Trùng cột seed trong repo                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| _(không có)_ | Migration chỉ tạo **nhóm** Order ([`database/migrations/2024_11_04_093951_add_orders_module_in_custom_fields.php`](../database/migrations/2024_11_04_093951_add_orders_module_in_custom_fields.php), [`2025_05_12_105705_add_orders_module_in_custom_field_group_table.php`](../database/migrations/2025_05_12_105705_add_orders_module_in_custom_field_group_table.php)) — **không** seed field trùng PO/DO. |

**Việc cần làm thủ công:** Trong **Cài đặt → Custom fields → Order**, rà các CF do admin tạo trùng nghĩa với: `note`, `order_date`, `sub_total`, `total`, `client_id`, `company_address_id`, `custom_order_number`, v.v.

### 3.1 Order — đối chiếu CF (screenshot Settings) với **core** (để Remove / chuyển core)

**Trạng thái môi trường bạn (đã làm):** Đã **gỡ hết CF Order** theo đề xuất §3.1 (kể cả Order Type, HS Code, Storage, Halal, UOM, kho, địa chỉ).

**Tham chiếu schema:** bảng [`orders`](../database/migrations/2018_01_01_000000_create_craveva_new_table.php) (`client_id`, `order_date`, `sub_total`, `total`, `note`, `company_address_id`, `currency_id`, …); [`orders.unit_id`](../database/migrations/2023_02_15_045950_add_unit_id_orders_table.php) → `unit_types`; dòng [`order_items`](../app/Models/OrderItems.php): `product_id`, `unit_id`, `unit_price`, `quantity`, **`hsn_sac_code`**; [`products`](../app/Models/Product.php): `hsn_sac_code`, `storage_condition`, `unit_id`, …; bảng [`warehouses`](../Modules/Warehouse/Entities/Warehouse.php) (`id`, `code`, `name`, `address`, …) — **chưa** có cột `type` / `warehouse_type` trong repo; **chưa** có `orders.warehouse_id` (nếu sau này cần kho cố định trên SO thì thêm cột + UI, không dùng lại CF).

| Label trên UI (gần đúng)                                | Slug trong DB thường là        | Trạng thái (môi trường bạn) | Ghi chú ngắn                                                                                                                     |
| ------------------------------------------------------- | ------------------------------ | --------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Order Type** (radio: Delivery Order / Purchase Order) | vd. `order_type`               | **Đã gỡ CF**                | Không có cột `orders.*` tương ứng; SO vs PO là hai module.                                                                       |
| **HS Code**                                             | `hs_code`                      | **Đã gỡ CF**                | Dùng **`order_items.hsn_sac_code`** / sản phẩm — [`OrderController`](../app/Http/Controllers/OrderController.php) lưu theo dòng. |
| **Storage Condition**                                   | `storage_condition`            | **Đã gỡ CF**                | Lấy từ **`products.storage_condition`** theo `product_id` dòng đơn.                                                              |
| **Halal / Certification Tag**                           | `certification_tag` / tương tự | **Đã gỡ CF**                | Giữ trên **Product** (hoặc CF Product) nếu cần.                                                                                  |
| **Unit of Measure**                                     | `unit_of_measure`              | **Đã gỡ CF**                | Dùng **`orders.unit_id`** / **`order_items.unit_id`** → `unit_types` trên form chuẩn.                                            |
| **Warehouse ID** (text)                                 | `warehouse_id`                 | **Đã gỡ CF**                | Xem **§3.2** — xuất kho bán **không** đọc CF Order.                                                                              |
| **Warehouse Name**                                      | `warehouse_name`               | **Đã gỡ CF**                | Giống trên.                                                                                                                      |
| **Physical address**                                    | vd. `physical_address`         | **Đã gỡ CF**                | Dùng **`company_address_id`** / địa chỉ khách.                                                                                   |
| **Warehouse type** (Central, Regional…)                 | vd. `warehouse_type`           | **Đã gỡ CF**                | Nếu sau cần: cột trên **`warehouses`**, không CF Order.                                                                          |

### 3.2 Kiểm tra chức năng SO sau khi gỡ hết CF — **có đúng nghiệp vụ với code hiện tại không?**

**Kết luận (đã đối chiếu repo):** Gỡ CF Order **không làm hỏng** luồng SO cốt lõi, vì **observer/controller không đọc** các slug CF đó để tính tồn hay tạo HĐ.

| Chủ đề                         | Hệ thống đang làm gì (core)                                                                                                                                                                                                                                                           | CF Order trước đây                                                                               |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| **Xuất kho khi bán (Scope B)** | [`InvoiceWarehouseStockService::resolveWarehouseId()`](../Modules/Warehouse/Services/InvoiceWarehouseStockService.php): **`client_details.default_warehouse_id`** (hợp lệ trong company) → kho **`is_default`** → kho **active** đầu tiên; **không** tham chiếu `Order` hay CF Order. | CF text kho trên Order **không** được nối vào service này — chỉ có giá trị thủ công trên UI/PDF. |
| **Tạo / sửa SO**               | [`OrderController`](../app/Http/Controllers/OrderController.php) lưu `order_items`: `product_id`, `unit_id`, `hsn_sac_code`, …; optional `updateCustomFieldData` **chỉ khi** còn CF — hết CF thì không sao.                                                                           | HS/UOM đã có trên dòng đơn + product.                                                            |
| **SO → Invoice**               | Copy dòng sang `invoice_items` (kèm `product_id`, `unit_id`, `hsn_sac_code` nếu có).                                                                                                                                                                                                  | Không phụ thuộc CF Order cho xuất kho.                                                           |
| **Form / PDF**                 | Blade vẫn có `<x-forms.custom-field>` — **không còn field** thì khu vực CF trống (không lỗi). PDF order loop CF — không có thì không in thêm cột.                                                                                                                                     | —                                                                                                |

**Điều kiện vận hành sau khi gỡ CF (nên UAT lại):**

1. **Khách có `default_warehouse_id` đúng** (hoặc công ty có kho mặc định / ít nhất một kho active) — nếu không, xuất kho từ HĐ có thể báo lỗi / không resolve được kho (xem `WarehouseBusinessException` trong service).
2. **Dòng SO:** chọn **sản phẩm + đơn vị + HSN** trên form chuẩn (không còn CF HS/UOM).
3. Nếu nghiệp vụ cũ là **“mỗi đơn xuất từ kho khác với kho mặc định khách”** mà chỉ ghi trong CF — **code hiện tại vẫn chưa hỗ trợ**; cần **đổi kho mặc định trên Client**, hoặc sau này thêm **`orders.warehouse_id`** + nối vào resolve HĐ (feature mới).

**Tài liệu luồng:** [`SALES_PURCHASE_FLOW.md`](SALES_PURCHASE_FLOW.md) §2–3 · [`QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`](QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md) §4.

---

## 3a) Hóa đơn (Invoice) — `App\Models\Invoice`

**Repo seed nhóm “Invoice”:**

| Nguồn                                                                                                                                                     | Slug CF (name)                                                                                                                                                                                           | Ghi chú                                                                                    |
| --------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| [`2026_01_13_034358_add_invoice_custom_fields_fb.php`](../database/migrations/2026_01_13_034358_add_invoice_custom_fields_fb.php)                         | `batch_number`, `expiry_date`, `storage_condition`, `unit_of_measure`, `cost_per_unit`, `delivery_reference_no`, `purchase_order_reference`, `internal_product_category`, `hs_code`, `certification_tag` | CF gắn **cả chứng từ HĐ** (header), không phải migration seed theo dòng `invoice_items`.   |
| [`2026_01_18_000200_add_invoice_delivery_fee_custom_field_fb.php`](../database/migrations/2026_01_18_000200_add_invoice_delivery_fee_custom_field_fb.php) | `delivery_fee`                                                                                                                                                                                           | Phí giao / vận chuyển **phía bán** (thường thu khách hoặc ghi nhận phí giao hàng trên HĐ). |

### 3a.1 Đối chiếu **PROJECT MAOLIN New** (import thực tế)

Theo [`MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`](MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md) và [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md):

| Nội dung                                                                 | Kết luận đối chiếu Invoice CF                                                                                                                |
| ------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------- |
| **Không có** file `invoices.csv` / importer HĐ chuẩn trong bộ MAOLIN New | Các CF Invoice **không** được map trực tiếp từ CSV khách; chỉ mang tính **mở rộng UI** hoặc nhập tay sau khi tạo HĐ.                         |
| `Quote_unit_price_inventory__報價單匯出.csv` / sheet **報價單匯出**      | **Báo giá / chứng từ kinh doanh** — _không_ xếp vào chuỗi import chuẩn PO/DO/Invoice; cần adapter nếu muốn auto-fill CF.                     |
| `Last_year_net_sales__*.csv`                                             | **Lịch sử doanh thu / snapshot** — không thay thế cấu trúc HĐ + CF.                                                                          |
| `Quote_unit_price_inventory__產品庫存表.csv` / tồn đa kho                | Cột kiểu **庫別 / 批號 / 本期入庫 / 期末庫存**… map vào **Product + Inventory/Warehouse**, không phải nhóm **Invoice** trong migration trên. |

**Ghi chú về screenshot (Ctrl+F “ware”, nhiều dòng Location / Inbound / Expiry…):** danh sách đó **không** khớp bảng seed CF **Invoice** trong repo; thường là CF module **Product** (hoặc tương đương) admin tạo để mirror cột file tồn — **đừng nhầm** với CF HĐ.

### 3a.2 Có nên **bỏ** CF Invoice nào, **chuyển core** chỗ nào?

| Slug                                                                                                                    | Nên bỏ CF? / Core?                                                                                                                                                                                                                                               | Lý do ngắn                                                               |
| ----------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| **`delivery_fee`**                                                                                                      | **Giữ CF** cho tới khi có cột core kiểu `invoices.shipping_total` / dòng phí trên HĐ được chốt thiết kế. Đây là chỗ **đúng tầng bán** (khác `delivery_fee` **DO** = phí nhập). Đã bỏ CF **PO** thì **không** bắt buộc bỏ luôn Invoice — hai nghiệp vụ khác nhau. | Phí giao **khách** / outbound logistics trên HĐ.                         |
| **`batch_number`**, **`expiry_date`**                                                                                   | **Nên gỡ khỏi CF header** nếu nghiệp vụ là FEFO/lô theo **dòng** hoặc đã link `invoice_items.delivery_order_item_id` → lấy lô/HSD từ chuỗi nhập. Header một giá trị **sai cấp** khi một HĐ nhiều SKU/lô.                                                         | Trùng nghĩa **dòng / lô**, không nên một field cho cả HĐ.                |
| **`storage_condition`**, **`unit_of_measure`**, **`internal_product_category`**, **`hs_code`**, **`certification_tag`** | **Ưu tiên dữ liệu trên Product** (`product_id` trên dòng HĐ); CF trùng master **dễ lệch** so với sản phẩm. Có thể **bỏ** CF Invoice nếu master đủ và báo cáo đọc từ product.                                                                                     | Trùng **master sản phẩm** (MAOLIN: 儲存溫層, 品號, … nằm file **商品**). |
| **`cost_per_unit`**                                                                                                     | Tuỳ — thường **margin nội bộ**; có thể giữ CF hoặc báo cáo kế toán khác, không bắt buộc core HĐ khách.                                                                                                                                                           |                                                                          |
| **`delivery_reference_no`**, **`purchase_order_reference`**                                                             | **Ứng viên cột core** `invoices` (nullable string) nếu ERP/WMS cần tra cứu / sync ổn định; CF vẫn dùng được tạm.                                                                                                                                                 | Tham chiếu logistics / mua hàng trên **một HĐ**.                         |

### 3a.3 Invoice — đối chiếu từng field (screenshot Settings) với **core**

**Tham chiếu:** [`invoice_items`](../app/Models/InvoiceItems.php): `product_id`, `unit_id`, `unit_price`, `quantity`, **`hsn_sac_code`**; [`products`](../app/Models/Product.php): `hsn_sac_code`, `storage_condition`, `category_id`, …; HĐ có `order_id`, `company_address_id`; migration seed còn có **`expiry_date`** (có thể không hiện trên UI cũ).

| Label trên UI                 | Slug (repo seed)            | Trùng / lệch core?                                   | Ghi chú & Remove?                                                                                                                          |
| ----------------------------- | --------------------------- | ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **Batch / Lot Number**        | `batch_number`              | **Sai cấp (header)**                                 | Một HĐ nhiều dòng → lô thuộc **dòng** / `delivery_order_item_id` / tồn lô. **Nên gỡ** CF header hoặc chuyển xuống dòng + cột DB tương ứng. |
| **Expiry Date**               | `expiry_date`               | **Sai cấp**                                          | Giống batch — **nên gỡ** khỏi header.                                                                                                      |
| **Storage Condition**         | `storage_condition`         | **Trùng** `products.storage_condition`               | **Gỡ**; lấy từ sản phẩm trên dòng HĐ.                                                                                                      |
| **Unit of Measure**           | `unit_of_measure`           | **Trùng** `invoice_items.unit_id`                    | **Gỡ**; dùng đơn vị trên dòng.                                                                                                             |
| **Purchase Order Reference**  | `purchase_order_reference`  | **Không** có cột `invoices.purchase_order_*`         | Không trùng tên cột; **giữ CF** tạm hoặc thêm cột core khi sync ERP.                                                                       |
| **Cost per Unit**             | `cost_per_unit`             | **Không** trùng `unit_price`                         | `unit_price` là **giá bán**; cost là **nội bộ/margin**. Không xóa chỉ vì “trùng giá” — **tuỳ PM**; có thể chuyển sang báo cáo cost riêng.  |
| **Delivery Reference No.**    | `delivery_reference_no`     | **Không** trùng                                      | **Giữ** hoặc cột core `invoices.delivery_reference_no`.                                                                                    |
| **HS Code**                   | `hs_code`                   | **Trùng**                                            | **`invoice_items.hsn_sac_code`** / product. **Nên gỡ** CF HĐ.                                                                              |
| **Halal / Certification Tag** | `certification_tag`         | **Trùng Product / CF Product**                       | **Nên gỡ** trên Invoice nếu đã có trên Product.                                                                                            |
| **Internal Product Category** | `internal_product_category` | **Trùng** `products.category_id` / `sub_category_id` | **Nên gỡ**; dùng danh mục sản phẩm.                                                                                                        |
| **Delivery Fee**              | `delivery_fee`              | **Không** trùng cột `invoices` chuẩn                 | **Giữ CF** tới khi có cột shipping / dòng phí; khác `delivery_fee` DO (nhập).                                                              |

---

## 4) Client — `App\Models\ClientDetails`

### 4.1 Cột core đã có (không lặp bằng CF)

| Cột / quan hệ                                                                          | Ghi chú                                                                                                                                                                                                                                    |
| -------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `company_name`, `address`, `gst_number`, `client_code`, `note`, `website`, `office`, … | Thuộc `client_details` / `users` — xem [`ClientDetails::$fillable`](../app/Models/ClientDetails.php).                                                                                                                                      |
| **`default_warehouse_id`**                                                             | Cột FK kho mặc định ([`2026_03_23_120100...`](../database/migrations/2026_03_23_120100_add_default_warehouse_id_to_client_details_table.php)); import MAOLIN resolve `designated_warehouse_code` / name → **không** cần CF “kho chỉ định”. |

### 4.2 CF Miaolin (seed migration) — có nên chuyển core?

Các field sau **đang là CF** ([`2026_03_09_100000...`](../database/migrations/2026_03_09_100000_add_client_custom_fields_for_miaolin.php), [`2026_03_09_110000...`](../database/migrations/2026_03_09_110000_add_client_custom_fields_last_transaction_payment_terms_closure.php)). **Không trùng tên cột** → giữ CF là hợp lệ; chuyển core chỉ khi cần **index, báo cáo SQL, filter hệ thống, API cố định**.

| Slug CF                                        | File MAOLIN New (tham chiếu)                                                                                                          | Đề xuất core?                                                                                                            |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| `salesperson`, `sales_assistant_name`          | 客戶資料 — 業務員, 業務助理                                                                                                           | **Tuỳ** — CF đủ cho import; core nếu cần join user/report.                                                               |
| `department`                                   | Cột 部門 — **file .xlsx mới khách gửi có thể thiếu** ([`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md)) | **Tuỳ** — nếu PM chốt “phòng ban là trường hệ thống”: thêm cột `client_details.department` + map import; không bắt buộc. |
| `channel_type`, `business_type`                | 通路別, 型態別                                                                                                                        | **Tuỳ** — thường giữ CF hoặc bảng danh mục sau này.                                                                      |
| `last_transaction_at`, `business_closure_date` | 最近交易, 歇業日期                                                                                                                    | **Nên cân nhắc core** (date nullable) nếu cần sort/filter/toast “khách ngừng hoạt động”; hiện CF date vẫn dùng được.     |
| `payment_terms`                                | 交易條件                                                                                                                              | **Tuỳ** — core string nếu in báo cáo/ERP nhiều.                                                                          |

**CF import MAOLIN nhưng không có migration seed trong repo:**  
`customer_grade` — import hỗ trợ ([`ClientImportProcessor.php`](../app/Services/ClientImportProcessor.php)); mapping [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) §1.1 — hoặc tạo CF tay, hoặc map sang **`pricing_tier_id`** nếu đã có tier.

**Cột có trong file khách, chưa có trong hệ (xem phân tích file):**  
**地區別 (region)** — [`PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`](PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md): hiện **chưa** có trường tương ứng; lựa chọn: CF `region` hoặc cột `client_details.region` khi chốt PM.

**Rủi ro CF do admin tự tạo — nên gỡ nếu trùng nghĩa cột chuẩn:** `company_name`, `address`, `gst_number`, `client_code`, `email`, `mobile`, `note`, `website`, `office`, `default_warehouse_id`, v.v. Chi tiết: [`ClientImportProcessor.php`](../app/Services/ClientImportProcessor.php).

---

## 5) SQL gợi ý (kiểm tra trước khi xóa — MySQL)

Đặt `:company_id` nếu cần lọc theo công ty.

```sql
-- Liệt kê CF PO / DO / Order / Client: slug + label + group id
SELECT cf.id, cf.name, cf.label, cfg.name AS group_name, cfg.model
FROM custom_fields cf
JOIN custom_field_groups cfg ON cfg.id = cf.custom_field_group_id
WHERE cfg.model IN (
  'Modules\\Purchase\\Entities\\PurchaseOrder',
  'App\\Models\\DeliveryOrder',
  'App\\Models\\Order',
  'App\\Models\\Invoice',
  'App\\Models\\ClientDetails'
)
ORDER BY cfg.model, cf.name;

-- Sau khi gỡ đúng: không còn các slug trùng cột / kho text thừa (kết quả 0 dòng)
SELECT cf.id, cf.name, cfg.model
FROM custom_fields cf
JOIN custom_field_groups cfg ON cfg.id = cf.custom_field_group_id
WHERE (cfg.model = 'Modules\\Purchase\\Entities\\PurchaseOrder' AND cf.name = 'expected_delivery_date')
   OR (cfg.model = 'Modules\\Purchase\\Entities\\PurchaseOrder'
       AND cf.name IN ('destination_warehouse_code', 'destination_warehouse_name'))
   OR (cfg.model IN ('App\\Models\\DeliveryOrder', 'App\\Models\\OrderDelivery')
       AND cf.name IN ('erp_shipment_reference', 'wms_shipment_reference'));
```

Sau khi có `id` của từng CF sẽ xóa:

```sql
-- Xóa dữ liệu CF (chạy trước khi xóa dòng custom_fields)
-- DELETE FROM custom_fields_data WHERE custom_field_id IN (...);

-- DELETE FROM custom_fields WHERE id IN (...);
```

---

## 6) Mục lục nhanh file liên quan

| File                                                                                                                                                                                        | Nội dung                                                                                      |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| [`database/migrations/2026_01_15_000006...`](../database/migrations/2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb.php)                                                 | Seed CF PO: batch + ERP/WMS ref (không seed `destination_warehouse_*`)                        |
| [`database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php`](../database/migrations/2026_03_27_140000_remove_redundant_po_do_custom_fields.php)                       | Xóa CF trùng cột + prototype kho đích PO (idempotent)                                         |
| [`database/migrations/2026_03_28_110000_remove_po_destination_warehouse_custom_fields.php`](../database/migrations/2026_03_28_110000_remove_po_destination_warehouse_custom_fields.php)     | Dọn `destination_warehouse_*` nếu DB đã chạy `2026_03_27` trước khi mở rộng slug (idempotent) |
| [`database/migrations/2026_01_18_000100_add_delivery_fee_custom_fields_for_po_and_do_fb.php`](../database/migrations/2026_01_18_000100_add_delivery_fee_custom_fields_for_po_and_do_fb.php) | CF `delivery_fee` **PO** (DO dùng cột); có thể **đã xóa** CF PO trên Settings                 |
| [`database/migrations/2026_01_13_034358_add_invoice_custom_fields_fb.php`](../database/migrations/2026_01_13_034358_add_invoice_custom_fields_fb.php)                                       | Seed CF nhóm **Invoice** (10 field) — xem **§3a**                                             |
| [`database/migrations/2026_01_18_000200_add_invoice_delivery_fee_custom_field_fb.php`](../database/migrations/2026_01_18_000200_add_invoice_delivery_fee_custom_field_fb.php)               | CF **`delivery_fee`** nhóm **Invoice** (phía bán)                                             |
| [`app/Models/DeliveryOrder.php`](../app/Models/DeliveryOrder.php)                                                                                                                           | Cột chuẩn DO                                                                                  |
| [`Modules/Purchase/Entities/PurchaseOrder.php`](../Modules/Purchase/Entities/PurchaseOrder.php)                                                                                             | Model PO + cast `expected_delivery_date`                                                      |
| [`app/Services/ClientImportProcessor.php`](../app/Services/ClientImportProcessor.php)                                                                                                       | Map import + danh sách CF client                                                              |
| [`Modules/Purchase/Observers/DeliveryOrderObserver.php`](../Modules/Purchase/Observers/DeliveryOrderObserver.php)                                                                           | Nhập kho từ DO `received` — batch/expiry/rule lấy từ `delivery_order_items`                   |

---

## 7) Warehouse / DO / PO — trạng thái so với **Settings → Custom fields** (screenshot PO 6 field)

Trên UI có thể vẫn thấy đủ 6 dòng PO (**Batch tracking**, **Destination warehouse ×2**, **ERP/WMS ref**, **Delivery fee**) cho tới khi **chạy migrate dọn** hoặc **Delete** tay hai CF kho đích. Repo **không còn seed** `destination_warehouse_*`.

### 7.1 Delivery Order — đã chuyển core (không còn CF trong migration mới)

| Chủ đề           | Trạng thái                                                                                                                                                                                                                                |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Kho              | **`delivery_orders.warehouse_id`** + form ([`DeliveryOrderController`](../Modules/Purchase/Http/Controllers/DeliveryOrderController.php)); observer ([`DeliveryOrderObserver`](../Modules/Purchase/Observers/DeliveryOrderObserver.php)). |
| Lô / HSD / rule  | **Theo dòng** `delivery_order_items`; observer đọc từ item.                                                                                                                                                                               |
| **Delivery fee** | **Cột** `delivery_orders.delivery_fee` — **bỏ CF ở DO** ([`2026_03_28_100000...`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php)).                                               |

### 7.2 Purchase Order — phần còn CF (đối chiếu screenshot)

Chi tiết **delivery fee PO vs DO** và bảng “có nên core” → **§1.1**, **§1.2**. Tóm tắt: **DO không dùng CF delivery fee**; **PO có thể vẫn CF** cho tới khi thêm cột PO hoặc bỏ hẳn nếu không dùng.

### 7.3 Client — tóm tắt (chi tiết §4)

| Chủ đề                           | Ghi chú                                                                                   |
| -------------------------------- | ----------------------------------------------------------------------------------------- |
| **Kho chỉ định**                 | **`default_warehouse_id`** (core) + import — **không** CF trùng nghĩa.                    |
| **9 field Client** trên Settings | Khớp seed Miaolin + `customer_grade` (import); xem **§4.2** về lúc nào nên đưa ra cột DB. |
| **Region / 地區別**              | Chưa có trong core — CF hoặc cột sau khi PM chốt.                                         |

### 7.4 Thứ tự làm việc gợi ý (dev / vận hành)

1. **DO:** kho + lô theo dòng + `delivery_fee` cột — **đã làm**; đảm bảo migrate [`2026_03_28_100000...`](../database/migrations/2026_03_28_100000_delivery_order_native_columns_and_remove_do_custom_fields.php) đã chạy trên mọi môi trường.
2. **PO:** xóa CF **destination warehouse** (migrate hoặc tay); dùng **`warehouse_id`** trên form.
3. **PO:** quyết định PM về **`delivery_fee`**: chỉ DO / thêm cột PO / **đã bỏ CF PO** (như môi trường bạn) — vẫn có thể giữ **`delivery_fee` trên Invoice** (§3a).
4. **PO:** roadmap core cho **`erp_po_reference`**, **`wms_po_reference`**, **`batch_tracking_required`** (§1.2).
5. **Order (SO):** **đã gỡ hết CF** — UAT **§3.2** (kho khách mặc định, dòng đơn, SO→HĐ).
6. **Invoice:** rà **§3a** (CF header vs master Product; MAOLIN không import HĐ chuẩn).
7. **Client:** chỉ nâng cột core khi có yêu cầu báo cáo/API rõ ràng (§4.2).
8. UAT: DO nhiều SKU/lô; PO kho; HĐ + phí bán nếu dùng; client import + `default_warehouse_id`.

---

_Cập nhật theo rà soát codebase; sau khi gỡ CF nên chạy lại UAT form PO/DO/Order/Client và import mẫu._
