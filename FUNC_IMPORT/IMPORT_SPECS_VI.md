# FUNC_IMPORT — Đặc tả map cột (Maolin / Craveva)

> **Gộp (2026-05-12):** trước đây tách `IMPORT_PRODUCT.md`, `IMPORT_CLIENT.md`, `IMPORT_INVENTORY.md`, `IMPORT_SALE_ORDER.md`, `IMPORT_QUOTATION.md`. Mục lục theo domain; anchor GitHub-style: `#5-báo-giá-quotation--estimates`.

---

## 1. Product (品號 / CSV)

Cột CSV | Field import | Ghi chú
品號 | SKU | sku Bắt buộc khi map — file có đủ.
品名 | Product Name | product_name Bắt buộc — file có đủ.
規格 | Specification | specification
庫存單位 | Inventory Units | unit_type Giá trị phải trùng tên đơn vị đã có trong hệ thống (vd. 包, KG).
商品級別 | Product Grade | product_grade
品牌類別 | Brand Category | brand Map vào trường brand (không có cột “brand category” riêng).
保存天數 | Storage Days | shelf_life_days Là số ngày bảo quản/shelf life; không map vào storage_condition.
備貨型態 | Inventory type | inventory_type Chuỗi tự do (vd. 常備, 客訂).
儲存溫層 | Storage temperature layer | storage_condition vd. 常溫.
失效日期 | Expiry Date | (không có field core) Model Product không có cột expiry theo từng dòng; muốn lưu phải custom field hoặc bỏ qua.

---

## 2. Client (客戶)

客戶代號 | Customer Code → client_code
客戶簡稱 | Customer Short Name → name (bắt buộc)
統一編號 | Tax ID | gst_number → gst_number
業務員 | Salesperson → salesperson (custom field)
業務助理名稱 | Sales Assistant Name → sales_assistant_name (custom field)
客戶(集團)分級 | Customer Grade → customer_grade (custom field)
通路別 | Channel Type → channel_type (custom field)
地區別 | Geographical distinction → geographical_distinction
型態別 | Business Type → business_type (custom field)
送貨地址 | Shipping Address → address
TEL_NO(一) | mobile → mobile
TEL_NO(二) | company_phone → company_phone
交易條件 | Payment Terms → payment_terms (custom field)
最近交易 | last_transaction_at → last_transaction_at (custom field)
歇業日期 | Business Closure Date → business_closure_date (custom field)
指定庫別名稱 | designated_warehouse_name → designated_warehouse_name

Tier 1 (nên chuyển ngay)
-- business_closure_date
Vì đã có tác động nghiệp vụ thật: import có giá trị này thì hệ thống set users.status = inactive.
Nếu để custom field lâu dài sẽ khó kiểm soát nhất quán.
Tier 2 (chuyển khi bắt đầu dùng cho rule/filter/report chính thức)
-- payment_terms — 交易條件 | Payment Terms | cách thức và thời hạn thanh toán: 30 ngày, 60 ngày, 90 ngày, 120 ngày, 180 ngày
-- customer_grade — 客戶(集團)分級 | Customer Grade (Phân cấp / phân hạng khách)
-- channel_type — 通路別 | Channel Type | bán lẻ, đại lý, siêu thị, chuỗi, đơn lẻ
-- business_type — 型態別 | Business Type | sỉ / lẻ / nhà hàng / spa / chuỗi / đơn lẻ  
Lý do: có ý nghĩa vận hành/report rõ, nhưng hiện chưa thấy query/join/rule xuyên module dùng trực tiếp.

Giữ custom (hiện tại)
-- salesperson
-- sales_assistant_name
-- geographical_distinction
-- last_transaction_at (nên tính từ giao dịch thực tế thay vì nhập tay)

---

## 3. Inventory — Purchase / Quote_inventory

- **Đường dẫn:** `PROJECT MAOLIN New/Quote_inventory.csv`
- **Định dạng:** CSV phân tách bằng dấu phẩy; dòng đầu là tiêu đề song ngữ (ZH | EN).
- **Lưu ý:** Một số ô số có dấu phẩy nghìn và được bọc ngoặc kép (ví dụ `"1,220"`). Job import đã hỗ trợ `parseImportNumber` cho kiểu này.

# Cột trong file (22 cột, thứ tự theo header dòng 1)

| #   | Tiêu đề (ZH \| EN)                            | Gợi ý map sang import                                                                                                                         |
| --- | --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | 品號 \| SKU                                   | `sku`                                                                                                                                         |
| 2   | 品名 \| Product Name                          | `product_name`                                                                                                                                |
| 3   | 規格 \| Specification                         | `specification` (core); đồng thời CF _Specification_ nếu có trong company                                                                     |
| 4   | 單位 \| unit                                  | `unit` (tạo/khớp UnitType)                                                                                                                    |
| 5   | 小單位 \| Small unit                          | CF `small_unit` → cột `field_{id}`                                                                                                            |
| 6   | 包裝單位 \| Packaging unit                    | CF `packaging_unit` → `field_{id}`                                                                                                            |
| 7   | 批號 \| Batch number                          | `batch_number`                                                                                                                                |
| 8   | 有效日期 \| Expiration Date                   | `expiration_date` (core). Dữ liệu có dạng YYYYMMDD hoặc cần parse tương thích Carbon / Excel date                                             |
| 9   | 製造日期 \| Manufacturing date                | `manufacturing_date`                                                                                                                          |
| 10  | 結案碼 \| Closing Code                        | CF `closing_code` → `field_{id}`                                                                                                              |
| 11  | 庫別 \| warehouse_code                        | `warehouse_code`                                                                                                                              |
| 12  | 庫別名稱 \| warehouse_name                    | `warehouse_name`                                                                                                                              |
| 13  | 期初庫存 \| Beginning Inventory               | CF `beginning_inventory` → `field_{id}`                                                                                                       |
| 14  | 本期入庫 \| Inbound                           | CF `inbound_quantity` → `field_{id}`                                                                                                          |
| 15  | 本期出庫 \| Outbound                          | CF `outbound_quantity` → `field_{id}`                                                                                                         |
| 16  | 期末庫存 \| Ending Inventory                  | `ending_inventory` (ưu tiên hơn `quantity` khi tính tồn)                                                                                      |
| 17  | 期初包裝庫存 \| Beginning Packaging Inventory | CF `beginning_package_inventory` → `field_{id}`                                                                                               |
| 18  | 本期包裝入庫 \| Packaging Inbound Quantity    | CF `packaging_inbound_quantity` → `field_{id}`                                                                                                |
| 19  | 本期包裝出庫 \| Packaging Outbound Quantity   | CF `packaging_outbound_quantity` → `field_{id}`                                                                                               |
| 20  | 期末包裝庫存 \| Ending Packaging Inventory    | **Chưa có** CF chuẩn trong migration gộp (`ending_package_inventory`). Muốn lưu: tạo Custom Field (nhóm Inventory) rồi map sang `field_{id}`. |
| 21  | 最近入庫日 \| Recent Inbound                  | CF `recent_inbound_date` → `field_{id}`                                                                                                       |
| 22  | 批號最近入庫日 \| Batch Recent Inbound        | CF `batch_recent_inbound_date` → `field_{id}`                                                                                                 |

# Trường import core (InventoryImport + ImportInventoryJob)

| Field import                        | Nguồn cột Quote_inventory | Ghi chú                                                       |
| ----------------------------------- | ------------------------- | ------------------------------------------------------------- |
| sku                                 | 1                         | Bắt buộc để khớp/tạo sản phẩm                                 |
| product_name                        | 2                         |                                                               |
| warehouse_code                      | 11                        | Khớp `warehouses.code` trước, sau đó mới `warehouse_name`     |
| warehouse_name                      | 12                        |                                                               |
| ending_inventory                    | 16                        | Ưu tiên cho `net_quantity` / điều chỉnh tồn                   |
| quantity                            | —                         | Chỉ dùng nếu không map `ending_inventory`                     |
| specification                       | 3                         | Có thể ghi vào mô tả sản phẩm nếu đang trống                  |
| batch_number                        | 7                         |                                                               |
| manufacturing_date                  | 9                         |                                                               |
| expiration_date                     | 8                         | Core `expiration_date` trên dòng tồn (không cần CF)           |
| reserved_quantity                   | —                         | Tùy file; map cột nếu có (core `reserved_quantity`)           |
| date, type, cost_price, description | —                         | File không có; job mặc định ngày = hôm nay, `type` = quantity |

Các cột chỉ nằm trong **Custom Field** (sau khi chạy migration / cấu hình company): đặt header template import trùng **nhãn đã dịch** (`__($customField->label)`) hoặc dùng cột `field_{id}` xuất từ màn hình export/template.

# Cột file không có field core tương ứng (chỉ CF hoặc cần thêm CF)

| Nội dung                                                                                                                         | Ghi chú                                                                                 |
| -------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| 小單位, 包裝單位, 結案碼, kỳ đầu/cuối nhập xuất (trừ 期末庫存 đã map `ending_inventory`), đóng gói nhập xuất, ngày nhập gần nhất | Map sang CF đã seed (bảng dưới) qua `field_{id}` hoặc label khớp                        |
| 期末包裝庫存 (cột 20)                                                                                                            | Chưa có trong migration `setup_purchase_custom_fields_merged`; thêm CF thủ công nếu cần |

---

## Core đã bổ sung trong code (thay các CF đã xóa)

Chạy migration rồi dùng các cột sau — **không** cần CF tương ứng:

| Trước đây (CF)         | Core                                                                                                                                                                                                    |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Expiry Date**        | `purchase_stock_adjustments.expiration_date`. Import: `expiration_date`.                                                                                                                                |
| **Ending Inventory**   | `purchase_stock_adjustments.net_quantity`. Import: `ending_inventory`. DataTable: cột **Ending Inventory**.                                                                                             |
| **Reserved Quantity**  | `purchase_stock_adjustments.reserved_quantity` (migration `2026_04_01_130000_add_reserved_quantity_to_purchase_stock_adjustments`). Import: `reserved_quantity`. Form điều chỉnh SL: ô **Reserved**.    |
| **Near-Expiry Status** | Không lưu DB: accessor `PurchaseStockAdjustment::near_expiry_status` (từ `expiration_date`; ngưỡng ngày: `config('purchase.inventory_near_expiry_days')`, mặc định 30). DataTable: cột **Near Expiry**. |

**Lưu ý:** `warehouse_product_batches.reserved_quantity` vẫn dùng cho luồng kho `products` + movement; cột mới trên `purchase_stock_adjustments` phục vụ **Purchase Inventory** và import.

### Snapshot ERP (Maolin) — tùy bước sau

- **Beginning / Inbound / Outbound / đóng gói** — có thể giữ CF hoặc thêm cột báo cáo core khi chuẩn hóa.

### CF có thể giữ thêm

- **Closing Code**, **Recent / Batch Recent Inbound**, **Location Code**, **Packaging / Small Unit** — xem nhu cầu từng company.

<!-- 1. Nhập / sửa trên màn hình (không qua file)
   Vào Purchase → Inventory (điều chỉnh tồn / purchase inventory), Tạo mới (/purchase-inventory/create).
   Chọn loại điều chỉnh Quantity (số lượng), không phải Value.
   Trên từng dòng sản phẩm:
   Tồn / số lượng trên tay → ô Quantity on hand → lưu vào net_quantity (đúng nghĩa Ending / tồn cuối cho dòng đó).
   Hạn dùng (Expiry) → ô Expiration date → expiration_date.
   NSX → Manufacturing date (nếu dùng).
   Lô → Batch number.
   Reserved → dòng Reserved quantity (optional) → reserved_quantity (đã thêm ở form add_quantity).
   Near-expiry (gần hạn): không có ô nhập — hệ thống tự tính từ ngày Expiration date (và cấu hình số ngày cảnh báo trong purchase.inventory_near_expiry_days).

2. Nhập bằng file (import)
   Purchase → Inventory → Import (route kiểu /purchase-inventory/import).
   Tải template / map cột: sku, product*name, warehouse*\*, ending_inventory, expiration_date, reserved_quantity, batch_number, … (đúng header template import). -->

---

## 4. Sale Order — Last year net sales.xlsx

# IMPORT SALE ORDER (Last year net sales.xlsx)

## 1) File nguồn và phạm vi

- Nguồn: `PROJECT MAOLIN New/Last year net sales.xlsx`
- File có nhiều sheet theo tháng (`2024-01`, `2024-02`, ..., `2026-02`)
- Luồng import SO mới đọc **tất cả sheet** trong workbook.

## 2) Mapping cột chuẩn

| Cột file                                          | Field import           | Bắt buộc | Ghi chú                                                    |
| ------------------------------------------------- | ---------------------- | -------- | ---------------------------------------------------------- |
| 出貨/銷退日(C10) \| Shipment/Return Date (C10)    | `shipment_return_date` | Yes      | Parse theo date Excel hoặc chuỗi `YYYY/MM/DD`.             |
| 客戶編號 \| Customer Number                       | `customer_number`      | Yes      | Match `client_details.client_code` trong company hiện tại. |
| 產品料號 \| Product part number                   | `product_part_number`  | Yes      | Match `products.sku` trong company hiện tại.               |
| 淨銷售量(交易) \| Net sales volume (transactions) | `net_sales_volume`     | Yes      | Hỗ trợ số âm/dương, dấu phẩy nghìn.                        |
| Net sales (local currency/excluding tax)          | `net_sales_amount`     | No       | Rỗng thì tính theo quantity \* unit_price suy ra.          |

## 3) Quy tắc nghiệp vụ import

### 3.1 Return (âm)

- Nếu `net_sales_volume < 0` hoặc `net_sales_amount < 0` thì coi là dòng trả hàng.
- Tạo SO với `status = refunded`.
- Lưu `quantity` và `amount` theo trị tuyệt đối để tương thích cấu trúc order hiện tại.

### 3.2 Dedupe và idempotent

- Mỗi dòng tạo hash:
    - `company_id|shipment_date|customer_code|sku|net_sales_volume_raw|net_sales_amount_raw`
- Hash lưu vào bảng `order_import_rows` (unique theo `company_id + source_hash`).
- Import lại file không tạo trùng SO cho dòng đã nhập.

### 3.3 Date/timezone

- Date được chuẩn hóa về `Y-m-d` khi lưu `orders.order_date`.
- Import không dùng giờ, nên không lệch timezone hiển thị theo công ty.

### 3.4 Numeric parse

- Hỗ trợ:
    - `1,220`
    - `-1,220`
    - rỗng -> 0 (riêng cột required mà rỗng thì skip/fail theo rule).

## 4) Điều kiện dữ liệu trước khi chạy

- Client phải có `client_code` đúng với `customer_number`.
- Product phải có `sku` đúng với `product_part_number`.
- Nếu thiếu mapping client/product thì job fail theo row và hiển thị ở import exception.

## 5) Checklist chạy import

1. Vào `Orders` -> `Import Excel`.
2. Upload `Last year net sales.xlsx`.
3. Bật `Contains headings`.
4. Match đúng 5 cột theo bảng mapping.
5. Submit và theo dõi progress.
6. Kiểm tra bảng lỗi row (nếu có).

## 6) Thiếu dữ liệu / đề xuất CF (KHÔNG tự tạo)

Hiện tại luồng này **không cần custom field bắt buộc** để import tối thiểu.

Các thông tin có thể cân nhắc bổ sung sau (nếu nghiệp vụ yêu cầu), nhưng **không tự tạo CF**:

1. `return_reason_code` (mã lý do trả hàng)
    - Lý do: phân tích nguyên nhân hoàn trả.
    - Ảnh hưởng: báo cáo chất lượng bán hàng.
    - Gợi ý key/label: `orders.returnReasonCode` / `Return Reason Code`.

2. `source_sheet_month` (tháng nguồn)
    - Lý do: truy vết dữ liệu theo sheet tháng.
    - Ảnh hưởng: đối soát khi một workbook có nhiều kỳ.
    - Gợi ý key/label: `orders.sourceSheetMonth` / `Source Sheet Month`.

## 7) Import vào Sales History (khuyến nghị — snapshot báo cáo)

**Đường dẫn UI:** Operations → **Sales history** → **Import Excel** (routes: `sales-history.index`, `sales-history.import`, `sales-history.import.store`, `sales-history.import.process`).

- Dữ liệu ghi vào bảng `sales_history` / `sales_history_lines`, **không** tạo `orders` / `order_items`.
- Cùng file `Last year net sales.xlsx`, cùng mapping 5 cột, đọc **nhiều sheet** (index 0–59 như import class), idempotent theo `source_row_hash` trên `sales_history_lines`.
- Màn **Orders → Import Excel** có gợi ý chuyển sang Sales history cho file kiểu net sales legacy.

Chi tiết triển khai (archive): `FUNC_IMPORT/IMPORT_PROMPTS_ARCHIVE_VI.md` § Sales history.

---

## 5. Báo giá (Quotation) — Estimates

**Nguồn file trong repo:** `PROJECT MAOLIN New/Craveva Quotation_報價單匯出.csv` (và bản mẫu tải xuống UI: `public/sample-import/quotation-sample.csv`).

**Tài liệu liên quan:** `FUNC_LOGIC/MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md` (báo giá = tham chiếu / adapter, không nằm chuỗi import chuẩn PO/DO/Invoice).

---

## Tóm tắt nhanh


| Câu hỏi                            | Trả lời ngắn                                                                                                                                                                                                                                                                                                                                                        |
| ---------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| File phục vụ ERP hay B2B?          | **Cả hai:** chứng từ báo giá (ERP) + tham chiếu giá/điều kiện (B2B).                                                                                                                                                                                                                                                                                                |
| Craveva “Quotation” map model nào? | Module **Estimates** — bảng `estimates` / `estimate_items` (`App\Models\Estimate`).                                                                                                                                                                                                                                                                                 |
| Hiện import được chưa?             | **Có (V1):** luồng giống Client — upload → map cột → queue batch `EstimateImport` / `EstimateImport-chunked` (`EstimateImport`, `ImportEstimateChunkJob`, `EstimateImportProcessor`). UI: `estimates.import` (GET), `estimates.import.store` (POST), `estimates.import.process` (POST). Worker: queue tên `**EstimateImport`** (xem `ImportController` + `Kernel`). |
| Cần thêm cột DB core không?        | **Đã bổ sung (2026-04):** migration `2026_04_03_120000_add_quotation_core_fields_to_estimates_and_items.php` — cột header trên `estimates` + `free_quantity`, `line_effective_date`, `line_expiry_date` trên `estimate_items`. UI tạo/sửa/xem + import ghi các trường này; `sub_total`/`total` vẫn cộng từ dòng.                                                    |


---

## 1) Phạm vi import V1 vs tài liệu mapping đầy đủ

### 1.1 Trước đây thiếu **code & luồng**; hiện V1 đã có importer — CSV vẫn giàu cột hơn schema core

- Đã có `App\Imports\EstimateImport`, job chunk `App\Jobs\ImportEstimateChunkJob`, xử lý `App\Services\EstimateImportProcessor` (forward-fill header theo số báo giá, ngày ROC, lookup client/tiền tệ/sản phẩm tùy map).
- File CSV Maolin **đã đủ thông tin** để *thiết kế* map sang Estimate (gom nhóm theo `報價單號`, forward-fill header, chuẩn hóa số & ngày ROC).

### 1.2 Điều kiện để import **chạy được** (khi làm dev)

1. **Importer** (command/job + queue + quyền storage như runbook import).
2. **Master data:** khách (`客戶代號` → client), sản phẩm (`品號` → product), tiền tệ (`幣別` → `currencies`).
3. **Logic nghiệp vụ:** gom dòng, parse số có dấu phẩy, chuyển ngày ROC → `Y-m-d`, map thuế (`課稅別` / `品號稅別`) ↔ `calculate_tax` + JSON `taxes` trên dòng.
4. **Tùy chọn:** bảng **staging** nếu muốn reconcile trước khi ghi `estimates`.

### 1.3 “Thiếu cột” trên **schema Craveva** nghĩa là gì?

Phần lớn **header** Maolin đã có cột **core** tương ứng (mục 4 & 6). Cột dòng / phụ vẫn có thể thiếu 1:1 → **CF**, `item_summary`, hoặc bỏ qua. Chi tiết ở mục 4–5.

---

## 2) Cấu trúc file CSV (logic nghiệp vụ)

- **Một phiếu báo giá** = nhiều **dòng**; cột **header** (1–22) thường điền ở dòng đầu block, các dòng sau có thể **trống** (cần **forward-fill** theo `報價單號`).
- Cột **dòng hàng** bắt đầu từ `品號` trở đi (SKU, SL, đơn giá, …).

---

## 3) Ký hiệu dùng trong bảng mapping


| Ký hiệu   | Ý nghĩa                                                                                    |
| --------- | ------------------------------------------------------------------------------------------ |
| **Core**  | Map trực tiếp (hoặc sau lookup) vào cột chuẩn `estimates` / `estimate_items`.              |
| **CF**    | Nên lưu qua **Custom Field** gắn model `Estimate` (bảng estimates có `CustomFieldsTrait`). |
| **Text**  | Gộp vào `note`, `description` (header) hoặc `item_summary` (dòng).                         |
| **Logic** | Không cột tĩnh: suy ra từ dòng (tổng SL, tổng tiền) hoặc rule thuế.                        |
| **—**     | Bỏ qua / legacy / ít dùng trừ khi nghiệp vụ yêu cầu.                                       |


**Lưu ý:** `EstimateItem` **không** có CF; các cột dòng **core** mới: `free_quantity`, `line_effective_date`, `line_expiry_date`. Các cột Maolin khác (包裝, 標準價, …) vẫn có thể map **CF Estimate** hoặc gộp `item_summary`.

---

## 4) Mapping Maolin → Craveva (core vs CF) — cấp **HEADER** (cột 1–22)


| Cột CSV   | English (header label)   | Tiếng Việt (ý nghĩa)              | Gợi ý lưu Craveva                                                                                              |
| --------- | ------------------------ | --------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| `報價日期`    | Quotation date           | Ngày báo giá (ROC, vd. 114/12/01) | **Core:** `estimates.quotation_date` (import + form); `valid_till` vẫn lấy từ dòng hiệu lực hoặc +30 ngày      |
| `報價單號`    | Quotation number         | Số báo giá, key gom dòng          | **Core:** `estimates.estimate_number`                                                                          |
| `客戶代號`    | Customer code            | Mã khách                          | **Core:** lookup → `client_id` (kèm master Client)                                                             |
| `客戶簡稱`    | Customer short name      | Tên tắt KH                        | **—** / **CF** nếu cần lưu tên gốc ERP                                                                         |
| `單據日期`    | Document date            | Ngày chứng từ                     | **Core:** `estimates.document_date`                                                                            |
| `客戶全名`    | Customer full name       | Tên đầy đủ                        | **—** / **CF/Text** (ưu tiên master client)                                                                    |
| `幣別`      | Currency                 | Loại tiền (NTD, …)                | **Core:** `currency_id`                                                                                        |
| `匯率`      | Exchange rate            | Tỷ giá                            | **Core:** `estimates.exchange_rate`                                                                            |
| `報價金額`    | Quotation / total amount | Tổng tiền báo giá                 | **Core (nguồn):** `estimates.header_quotation_amount`; **tính toán:** `sub_total`/`total` cộng dòng            |
| `稅額`      | Tax amount               | Thuế                              | **Core (nguồn):** `estimates.header_tax_amount`; thuế dòng vẫn **Logic** / `taxes` JSON (chưa auto map Maolin) |
| `總數量`     | Total quantity           | Tổng SL                           | **Core (nguồn):** `estimates.header_total_quantity`                                                            |
| `交貨日`     | Delivery / lead time     | Giao hàng (thường là text)        | **Core:** `estimates.delivery_note`                                                                            |
| `業務員`     | Salesperson              | NVKD                              | **Core:** `estimates.salesperson_name`                                                                         |
| `課稅別`     | Tax type / category      | Loại thuế (vd. 應稅外加)              | **Core (nhãn):** `estimates.tax_type_label`; **Logic** thuế dòng ↔ `taxes` / `calculate_tax` (TODO nâng cao)   |
| `確認`      | Internal confirmed (Y/N) | Xác nhận nội bộ                   | **Core:** `estimates.confirm_internal`                                                                         |
| `客戶確認`    | Customer confirmed (Y/N) | Khách xác nhận                    | **Core:** `estimates.confirm_customer`                                                                         |
| `價格條件`    | Price terms / Incoterms  | Điều kiện giá                     | **Core:** `estimates.price_terms`                                                                              |
| `材積單位`    | Volume unit              | Đơn vị thể tích                   | **Core:** `estimates.volume_unit`                                                                              |
| `付款條件代號`  | Payment terms code       | Mã TTTT                           | **Core:** `estimates.payment_terms_code`                                                                       |
| `付款條件名稱`  | Payment terms name       | Tên TTTT                          | **Core:** `estimates.payment_terms_name`                                                                       |
| `總毛重(Kg)` | Total gross weight (kg)  | Tổng trọng lượng                  | **Core:** `estimates.total_gross_weight_kg`                                                                    |
| `總材積`     | Total volume             | Tổng thể tích                     | **Core:** `estimates.total_volume`                                                                             |


---

## 5) Mapping — cấp **DÒNG** (cột 23–46)


| Cột CSV    | English (line label)         | Tiếng Việt            | Gợi ý lưu Craveva                                                                                    |
| ---------- | ---------------------------- | --------------------- | ---------------------------------------------------------------------------------------------------- |
| `品號`       | Product code / SKU           | Mã sản phẩm           | **Core:** lookup → `product_id` + `item_name`                                                        |
| `品名`       | Product name                 | Tên SP                | **Core:** `item_name`                                                                                |
| `規格`       | Specification                | Quy cách              | **Core:** `item_summary`                                                                             |
| `數量`       | Quantity                     | Số lượng              | **Core:** `quantity`                                                                                 |
| `贈品量`      | Free / bonus qty             | SL tặng               | **Core:** `estimate_items.free_quantity`                                                             |
| `單位`       | Unit of measure              | Đơn vị                | **Core:** `unit_id` (lookup `unit_types`)                                                            |
| `小單位`      | Sub-unit / alternate UoM     | Đơn vị nhỏ            | **Text** / **CF** (header hoặc ghi chú dòng)                                                         |
| `生效日期`     | Effective date               | Ngày hiệu lực giá     | **Core:** `estimate_items.line_effective_date` (đồng thời ảnh hưởng `valid_till` header khi tạo mới) |
| `失效日期`     | Expiry date                  | Ngày hết hiệu lực     | **Core:** `estimate_items.line_expiry_date`                                                          |
| `允收效期`     | Allowed validity (text)      | Mô tả hiệu lực        | **Text**                                                                                             |
| `天(月)`     | Days (months)                | Chu kỳ ngày/tháng     | **Text** / **CF**                                                                                    |
| `單價`       | Unit price                   | Đơn giá               | **Core:** `unit_price`                                                                               |
| `金額`       | Line amount                  | Thành tiền dòng       | **Core:** `amount`                                                                                   |
| `分量計價`     | Tiered / partial qty pricing | Tính giá theo bậc     | **Text** / **CF**                                                                                    |
| `付款條件_0LD` | Payment terms (legacy)       | Trường cũ / typo      | **—**                                                                                                |
| `包裝方式`     | Packaging method             | Cách đóng gói         | **Text** → `item_summary`                                                                            |
| `包裝名稱`     | Packaging name               | Tên quy cách đóng gói | **Text** → `item_summary`                                                                            |
| `毛重(Kg)`   | Gross weight (kg), line      | Trọng lượng dòng      | **Text** / **CF**                                                                                    |
| `品號稅別`     | Item tax category            | Thuế theo mặt hàng    | **Logic** ↔ `taxes` (JSON id thuế)                                                                   |
| `材積`       | Volume (line)                | Thể tích dòng         | **Text** / **CF**                                                                                    |
| `應稅品未稅金額`  | Taxable amount (excl. tax)   | Tiền hàng chịu thuế   | **Logic** / **Text** hỗ trợ đối soát                                                                 |
| `免稅品金額`    | Tax-exempt amount            | Tiền hàng miễn thuế   | **Logic** / **Text**                                                                                 |
| `標準價`      | Standard / list price        | Giá chuẩn             | **Text** / **CF**                                                                                    |
| `價格判定`     | Price type (std / special)   | Phân loại giá         | **Text** / **CF**                                                                                    |


**Ghi chú kỹ thuật:** Số có dấu phẩy nghìn (`"1,150"`) — chuẩn hóa khi parse. Ngày ROC cần quy tắc cố định (+1911) trước khi lưu DB.

---

## 6) Cột **core** đã thêm & khi nào vẫn dùng **CF**

### 6.1 Đã triển khai trên DB (cùng migration trên)

`**estimates`:** `quotation_date`, `document_date`, `exchange_rate`, `header_quotation_amount`, `header_tax_amount`, `header_total_quantity`, `delivery_note`, `salesperson_name`, `tax_type_label`, `payment_terms_code`, `payment_terms_name`, `confirm_internal`, `confirm_customer`, `price_terms`, `volume_unit`, `total_gross_weight_kg`, `total_volume`.

`**estimate_items`:** `free_quantity`, `line_effective_date`, `line_expiry_date`.

- **Import:** `EstimateImportProcessor::applyQuotationHeaderFromImportRow()` ghi header **chỉ khi tạo estimate mới** (dòng đầu của số báo giá); các dòng tiếp theo không ghi đè header.
- **UI:** form tạo/sửa (`estimates.partials.quotation-extra-fields`, dòng meta từng item), xem (`estimates/ajax/show`).
- **Ngôn ngữ:** `lang/*/modules.php` → nhóm `estimates.`* (`quotationDate`, `lineFreeQuantity`, …).

### 6.2 Khi nào vẫn cần **CF**

- Cột Maolin **không** có cột core tương ứng (vd. `小單位`, `允收效期`, `標準價`, `價格判定`, …) → map **CF** Estimate hoặc `item_summary` / `note`.
- Báo cáo tùy chỉnh theo trường chưa có trên schema → thêm CF hoặc migration bổ sung.

### 6.3 Gợi ý CF (nếu vẫn muốn trùng tên Maolin song song core)

Không bắt buộc vì header đã có core; CF chỉ khi cần **bản sao** theo quy ước tên nội bộ (`maolin_`*).

---

## 7) “Quotation” trên UI Craveva vs export Maolin

- UI **Quotation** trỏ **Estimates** — schema **46 cột Maolin ≠** form Estimate hiện tại; import là **project map + code**, không phải copy 1:1 cột.

---

## 8) Ngoài **Quotation / Estimates**, còn chỗ nào trong hệ thống **phù hợp** với file `Craveva Quotetation_報價單匯出.csv`?

Tóm lại: **không có module import sẵn nào khác “khớp 1:1”** format 46 cột Maolin. Các hướng dưới đây là **mục đích nghiệp vụ khác nhau** — chỉ “phù hợp” nếu bạn **chấp nhận** mất bớt thông tin chứng từ hoặc phải **ETL / adapter** riêng.


| Hướng                                                                                                            | Phù hợp?                     | Ghi chú ngắn                                                                                                                                                                                                                                                                                                                                                                                                        |
| ---------------------------------------------------------------------------------------------------------------- | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Estimates** (`estimates` / `estimate_items`)                                                                   | **Cao nhất**                 | Giữ được **số báo giá**, **khách**, **dòng hàng**, **đơn giá/thành tiền**, thuế (sau khi map). Đúng bản chất “chứng từ báo giá”.                                                                                                                                                                                                                                                                                    |
| **Pricing — Client × Product** (`ClientProductPricingImport`: `customer_code`, `product_sku`, `custom_price`, …) | **Một phần**                 | Chỉ hợp lý nếu mục tiêu là **đồng bộ bảng giá riêng cho khách** (lấy từ cột `客戶代號` + `品號` + `單價`, có thể kèm rule **giá mới nhất** / theo `生效日期`). File CSV báo giá **không** trùng template import pricing hiện tại → cần **bước chuyển đổi** hoặc import tùy chỉnh. **Bảng giá list / workbook** trong Maolin vẫn là `Craveva_product__商品價格.csv` / `Quote_unit_price_inventory__產品價格表.csv` (đã có luồng pricing). |
| **Product** (giá chuẩn `標準價`)                                                                                    | **Rất hạn chế**              | Có thể dùng **phụ** để cập nhật/tham chiếu **list price** theo SKU, nhưng **không** thay được báo giá theo khách và **không** nên coi đây là “import quotation”.                                                                                                                                                                                                                                                    |
| **Sales History** (`sales_histories` / `sales_history_lines`)                                                    | **Không khuyến nghị**        | Dữ liệu thiết kế cho **giao dịch đã xảy ra** (ngày giao, số lượng, doanh thu…), không phải **báo giá chưa chốt**. Nhét quotation vào đây sẽ **sai ngữ cảnh** báo cáo.                                                                                                                                                                                                                                               |
| **Sales Order**                                                                                                  | **Không**                    | Đơn đặt hàng — giai đoạn sau báo giá; CSV không phải đơn hàng.                                                                                                                                                                                                                                                                                                                                                      |
| **Proposals** (lead)                                                                                             | **Kém phù hợp**              | Gắn **lead CRM**, trong khi Maolin thường là **khách đã có mã** (`客戶代號`).                                                                                                                                                                                                                                                                                                                                           |
| **Bảng staging / JSON / model riêng**                                                                            | **Phù hợp “lưu tham chiếu”** | Đúng tinh thần `MAOLIN_IMPORT_READINESS_AND_SEQUENCE.md`: báo giá có thể chỉ **adapter + lưu raw** phục vụ đối soát, AI, báo cáo — **không** đẩy vào PO/DO/Invoice.                                                                                                                                                                                                                                                 |


**Kết luận thực tế:** Nếu cần **một chỗ “chính thống” trong app** cho nội dung file này thì vẫn là **Estimates**. Các chỗ khác chỉ là **tách nhánh dữ liệu** (giá KH–SKU, hoặc archive) khi nghiệp vụ chấp nhận.

---

## 9) Checklist trước khi dev import (khi được ưu tiên)

- Mục tiêu: chỉ **lưu lịch sử** vs **tạo Estimate** gửi khách.
- Chuẩn hóa ngày ROC + số có dấu phẩy.
- Rule gom dòng + forward-fill theo `報價單號`.
- Match `客戶代號` / `品號` với master đã import.
- Quy ước thuế Maolin ↔ `calculate_tax` + `taxes` từng dòng.
- Cột core estimates / estimate_items + UI + import header (dòng đầu) + map `EstimateImport::fields()`.
- (Tùy chọn) CF cho cột Maolin chưa có core; map thuế dòng tự động từ `課稅別` / `品號稅別`.
- Test staging + queue/storage theo runbook import.

---

*Cập nhật: 2026-04-03 — migration core quotation fields + UI + import processor; cập nhật bảng mapping mục 4–5–6.*
