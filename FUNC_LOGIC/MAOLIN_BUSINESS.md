# MAOLIN Master Guide (Single source of truth)

**Mục tiêu:** Gộp các ghi chú MAOLIN/Miaolin rải rác thành **1 tài liệu dễ đọc**. Hệ thống hiện đã có **multi-warehouse** nên phần kho/lô/HSD ưu tiên theo **core DB**, hạn chế custom fields.

**Tài liệu này thay cho việc phải đọc nhiều file.**

Trước đây các nội dung nằm rải rác trong các file MAOLIN/Miaolin khác; hiện đã **gộp** để dễ đọc. Bảng mapping vận hành vẫn nằm ở [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md).

**Đọc nhanh:**

1. Đọc file này để nắm scope Maolin/Miaolin, Phase 1 DigiWin, daily sync, gap/rủi ro.
2. Mở [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md) khi cần map cột CSV/XLSX vào field import/core DB.
3. Warehouse/multi-warehouse đọc thêm [`MODULE_WAREHOUSE.md`](MODULE_WAREHOUSE.md), [`WAREHOUSE_BUSINESS.md`](WAREHOUSE_BUSINESS.md), [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md).
4. UAT E2E Mua · Bán · Kho đọc [`SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md`](SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md).

---

## Mục lục

1. Trạng thái hiện tại (multi-warehouse đã có)
2. Mô hình tích hợp Phase 1 (DigiWin là system-of-record)
3. Bộ file khách gửi & sheet map (DigiWin export)
4. Keys chuẩn (idempotent) cho sync hằng ngày
5. Mapping chuẩn cho 3 domain: Client / Product / Pricing / Inventory
6. Custom fields: giữ/bỏ sau khi có multi-warehouse
7. UI: chỗ tạo Warehouse & thao tác tồn
8. GAP / rủi ro / quyết định cần chốt với khách
9. Readiness & thứ tự import
10. Checklist vận hành daily sync (sáng import / tối export)
11. Phụ lục: link tài liệu chi tiết

---

## 1) Trạng thái hiện tại (multi-warehouse đã có)

Hệ thống đã có:

- Master kho + chọn kho trên UI (`warehouse_id`)
- Tồn theo kho/lô/hạn (movement + batch)
- Client có `default_warehouse_id`
- Luồng Purchase Inventory/warehouse stock đã có chỗ chọn kho và đồng bộ movement

**Nguyên tắc:** dữ liệu kho/lô/hạn là **core DB**, không lưu trong custom field để vận hành.

---

## 2) Phase 1 integration model (theo contract / note vận hành)

Phase 1 là **file-based daily sync**:

- **Sáng:** DigiWin export → import vào hệ thống này
- **Tối:** hệ thống export → DigiWin import lại
- DigiWin vẫn là **ERP chính** trong Phase 1 (fulfillment / accounting)

Nguồn: `PROJECT MAOLIN New/customer do.txt`, `MIAOLIN_CONTRACT_ANALYSIS_EN.md`, `MIAOLIN_PHASE1_REQUIRED_FILES_EN - FINAL.md`.

---

## 3) Bộ file & sheet quan trọng (DigiWin export)

### 3.1 Product master

- `PROJECT MAOLIN New/Craveva product.xlsx`
    - Sheet `商品 | merchandise`: master SKU + thuộc tính (spec/unit/brand/grade/shelf life/…)
    - Sheet `商品價格 | commodity prices`: giá (một phần)

### 3.2 Pricing + Inventory workbook (multi-sheet)

- `PROJECT MAOLIN New/Quote, unit price, inventory.xlsx`
    - `產品價格表 | Product Price List`: giá theo SKU (chuẩn để fill gap giá)
    - `產品庫存表 | Product Inventory List`: tồn theo kho/lô (có nhiều cột snapshot kỳ)
    - `報價單匯出 | Quotation export`: báo giá (chưa có importer tương ứng; chỉ tham chiếu hoặc làm adapter)

### 3.3 Full batch stock snapshot

- `PROJECT MAOLIN New/Craveva full inventory.csv`
    - Có: `SKU`, `Product Name`, `Expiration Date`, `Lot Number`, `Inventory`, `Warehouse Name`
    - Thiếu: `Warehouse Code` → vẫn import được nếu match theo name, nhưng **khuyến nghị thêm code** để sync ổn định.

### 3.4 Last year net sales (theo ngày, theo khách, theo SKU)

- `PROJECT MAOLIN New/Last year net sales.xlsx`
    - Nhiều sheet theo tháng (ví dụ `2024-01`, `2025-03`, …)
    - Dữ liệu dạng **transaction-level**:
        - `Shipment/Return Date`
        - `Customer Number` (client_code)
        - `Product part number` (sku)
        - `Net sales volume (transactions)` (qty)
        - `Net sales (local currency/excluding tax)` (amount)

**File này KHÔNG phải inventory**, nhưng là dữ liệu **bắt buộc** nếu Miaolin muốn hệ thống nhất quán lịch sử bán hàng với DigiWin.

Vai trò chính:

- Nạp **order/sales history** theo ngày + khách + SKU
- Làm nền cho màn history, top SKU, repeat order, gợi ý mua lại
- Đối soát doanh thu giữa Craveva và DigiWin (theo kỳ)

**Kết luận vận hành:** với case Miaolin (DigiWin là ERP chính), nên coi file này là **required import** trong daily/periodic sync, không bỏ qua.

**Nếu muốn import vào hệ thống:** tạo một bảng “sales snapshots” độc lập (reporting), ví dụ:

- `digiwin_sales_lines` (hoặc `maolin_sales_lines`)
    - `company_id`
    - `source_date` (shipment/return date)
    - `client_code` (string) + optional `client_id` (resolve nếu match)
    - `sku` (string) + optional `product_id` (resolve nếu match)
    - `net_sales_qty` (decimal)
    - `net_sales_amount` (decimal)
    - `source_sheet` (yyyy-mm)
    - indexes: (`company_id`,`source_date`), (`company_id`,`client_code`), (`company_id`,`sku`)

Sau đó dùng cho dashboard/report, AI và order history. Import này không cần custom fields.

**Gợi ý lịch import để đảm bảo nhất quán:**

1. **Initial backfill**: import toàn bộ 12-24 tháng lịch sử.
2. **Daily increment**: mỗi tối import thêm sheet/tháng hiện tại hoặc file delta ngày.
3. **Khóa đối soát**: `source_date + client_code + sku` (có thể thêm source doc id nếu DigiWin xuất được).

---

## 4) Keys chuẩn cho sync (idempotent)

### Client

- Key: `client_code`

### Product

- Key: `sku`

### Warehouse

- Key: `warehouse_code` (name chỉ fallback)

### Inventory (tồn theo kho/lô)

- Key đề xuất: `warehouse_code + sku + batch_number + expiration_date` (thiếu batch thì `batch_number = null`)

---

## 5) Mapping chuẩn (đọc nhanh)

### 5.1 Client

**Core DB nên có/đã có:**

- `client_details.client_code`
- `users.name`
- `client_details.address`
- `users.mobile` / `client_details.office`
- `client_details.gst_number`
- `client_details.default_warehouse_id` (map từ designated warehouse code/name)
- `client_details.payment_terms`
- `client_details.customer_grade`
- `client_details.channel_type`
- `client_details.business_type`
- `client_details.business_closure_date` (có giá trị thì client inactive)

**Custom field (nếu còn dùng BI/segmentation):**

- `salesperson`, `department`, `sales_assistant_name`
- `last_transaction_at`, `region`

**Nếu muốn bỏ hết Client CF để sync “đầy đủ” vẫn giữ data:** cân nhắc chuẩn hoá DB theo lookup:

- `payment_term_id`
- `customer_grade_id`
- `channel_type_id`
- `business_type_id`

**Ghi chú 2026-06-16:** các field thương mại `payment_terms`, `customer_grade`, `channel_type`, `business_type`, `business_closure_date` đã là cột DB trong `client_details`; không seed custom field mặc định cho fresh project. Custom field Client chỉ nên dùng cho metadata phụ do PM/company tự tạo trong UI.

### 5.2 Product (master)

**Core DB cần có:**

- `sku`, `name`, `specification`, `unit_id`
- `inventory_type`, `storage_condition`, `shelf_life_days`
- `brand`, `product_grade`, `product_source`

**Pricing (core DB):**

- `price`, `wholesale_price`, `price_per_box`, `employee_price`

**Lưu ý:** `Expiry Date` không nên nằm ở product master; HSD nằm ở **batch/lot**.

### 5.3 Inventory (multi-warehouse + batch)

**Core DB cần có:**

- `warehouse_id` (resolve từ `warehouse_code`/`warehouse_name`)
- `product_id` (resolve từ `sku`)
- `batch_number`
- `manufacturing_date` (nếu có)
- `expiration_date`
- `quantity` (snapshot tồn)

**Các cột “kỳ kế toán / snapshot DigiWin”** (期初/本期入/本期出/最近入庫日/包裝…): không cần lưu vào core để vận hành; nếu muốn giữ phải làm **bảng snapshot/report riêng**, không dùng CF.

---

## 6) Custom fields — giữ/bỏ sau khi có multi-warehouse

**Inventory UI (thường thấy trên tenant):**

- **Nên bỏ:** Beginning/Inbound/Outbound/Ending inventory; Recent Inbound Date; Batch Recent Inbound Date; Near-Expiry Status; Reserved Quantity; Closing Code; các field packaging/small unit nếu đã chuẩn hoá unit.
- **Tùy:** Location Code (giữ nếu có nghiệp vụ bin/shelf thật).

**Product:** các CF trùng cột `products` nên bỏ (bạn đã bỏ trên UI).

Chi tiết bảng quyết định nằm ở `WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`.

---

## 7) UI — chỗ tạo Warehouse & thao tác tồn

Tạo master kho:

- `/warehouse`

Chỉnh tồn có chọn kho:

- Purchase Inventory: `/account/purchase-inventory`
- Warehouse stock: `/warehouse-stock` (và `/warehouse-stock/create`)
- Transfer: `/warehouse-transfer`

Chi tiết thao tác: `WAREHOUSE_MASTER_GUIDE.md`.

---

## 8) GAP / rủi ro / quyết định cần chốt

### Rủi ro

- File tồn chỉ có **warehouse name** (thiếu code) → dễ lệch mapping.
- SKU/client_code không chuẩn → sai match.
- Date format (Excel/TW calendar) → parse sai manufacturing/expiration.

### Quyết định cần PM/khách xác nhận

- Inventory file là **snapshot** hay **movement**?
- Rule overwrite: Product SKU trùng thì update field nào?
- Nguồn giá chuẩn: `商品價格` hay `產品價格表`?
- Có cần import sheet `報價單匯出` (quotation) không?

---

## 9) Readiness & thứ tự import

### 9.1 File dữ liệu chính trong `PROJECT MAOLIN New`

| File | Công dụng chính | Module map |
| ---- | --------------- | ---------- |
| `Craveva customer.csv` | Master khách hàng | Client |
| `Craveva_product__商品.csv` | Master sản phẩm: SKU, tên, quy cách, đơn vị | Product |
| `Craveva_product__商品價格.csv` | Bảng giá sản phẩm theo SKU | Product/Pricing |
| `Quote_unit_price_inventory__產品價格表.csv` | Bảng giá theo SKU, có thể có employee price | Product/Pricing |
| `Quote_unit_price_inventory__產品庫存表.csv` | Tồn đa kho + batch + hạn sử dụng, có warehouse code | Inventory/Warehouse |
| `Craveva full inventory.csv` | Tổng hợp tồn theo lô/hạn/kho, chủ yếu đối soát | Inventory/Warehouse |
| `Quote_unit_price_inventory__報價單匯出.csv` | Dữ liệu báo giá/chứng từ kinh doanh | Tham chiếu hoặc adapter riêng |
| `Last_year_net_sales__*.csv` | Lịch sử doanh thu theo kỳ | Reporting/Snapshot |

Workbook nhiều sheet cần lưu ý:

- `Craveva product.xlsx`: sheet `商品 | merchandise` cho Product master, sheet `商品價格 | commodity prices` cho Pricing.
- `Quote, unit price, inventory.xlsx`: sheet `報價單匯出` chỉ tham chiếu/adapter, sheet `產品價格表` cho Pricing, sheet `產品庫存表` cho Inventory.
- `Last year net sales.xlsx`: dữ liệu theo kỳ/tháng, nên tách CSV hoặc đảm bảo importer chọn đúng sheet.

### 9.2 Kết luận readiness

Hệ thống **đủ để import nhóm dữ liệu cốt lõi**:

- Client
- Product
- Pricing
- Inventory multi-warehouse

Hệ thống **chưa đủ để import trực tiếp file quotation `報價單匯出` vào PO/DO/Invoice** nếu chưa có adapter riêng. File này không nằm trong chuỗi import chuẩn hiện tại.

`Last year net sales` không phải inventory; đây là transaction history/reporting. Nếu cần import, nên đi vào bảng snapshot/reporting riêng sau khi đã có Client + Product để match `client_code` và `sku`.

### 9.3 Thứ tự import chuẩn

Nguyên tắc: master và khóa ngoại trước, snapshot sau; luồng vật lý kho tách khỏi lịch sử bán.

| Bước | Nội dung | Phụ thuộc | Ghi chú |
| ---- | -------- | --------- | ------- |
| 1 | Warehouse master | — | Bắt buộc trước Inventory; khuyến nghị trước Client nếu map designated warehouse vào `default_warehouse_id`. |
| 2 | Client | Bước 1 khuyến nghị | Import `Craveva customer`; map mã/tên kho chỉ định. |
| 3 | Product master | — | Import SKU + thuộc tính; bắt buộc trước Pricing và Inventory. |
| 4 | Pricing | Bước 3 | Chọn một nguồn giá chính để tránh ghi đè hai lần. |
| 5 | Inventory | Bước 1 + 3 | Ưu tiên file có `warehouse_code`; full inventory dùng đối soát/bổ sung. |
| 6 | Last year net sales | Bước 2 + 3 | Tùy chọn reporting/snapshot; không thay thế file tồn kho. |
| 7 | PO/DO/Invoice | Master đã đủ | Tạo trên UI hoặc luồng app; quotation import cần adapter riêng. |

Tóm tắt: `Warehouse -> Client -> Product -> Pricing -> Inventory -> [Last year net sales] -> [PO/DO/Invoice qua UI/app]`.

### 9.4 Trường hợp chưa có file Warehouse master

Khuyến nghị yêu cầu khách bổ sung `warehouse_master.csv` hoặc `.xlsx` với cột tối thiểu:

- `warehouse_code` bắt buộc, unique
- `warehouse_name` bắt buộc
- `status`, `region`, `address` tùy chọn

Phương án tạm cho demo: tạo warehouse master từ distinct `庫別 + 庫別名稱` trong `Quote_unit_price_inventory__產品庫存表.csv`, chốt lại với khách, rồi mới import Inventory. Không ưu tiên map bằng tên kho nếu thiếu code vì dễ trùng/đổi tên.

Mẫu nhắn khách:

> Để đảm bảo import tồn kho đa kho chính xác, vui lòng gửi thêm file Warehouse Master với tối thiểu các cột `warehouse_code`, `warehouse_name`, và nếu có thêm `status`, `region`, `address`. Tạm thời hệ thống có thể khởi tạo danh mục kho từ file tồn kho hiện tại (`庫別` + `庫別名稱`) để demo, nhưng để production cần file master chuẩn để tránh map sai kho.

### 9.5 Import Warehouse hiện có

Hệ thống đã có luồng Import Warehouse theo mẫu upload -> map cột -> progress -> process chunk.

File nguồn tạm có thể dùng: `PROJECT MAOLIN New/Quote_inventory.csv`

Map cột:

- `庫別` -> `warehouse_code`
- `庫別名稱` -> `warehouse_name`

Rule:

- Upsert ưu tiên theo `(company_id, warehouse_code)`.
- Nếu thiếu `warehouse_code`, fallback theo `(company_id, warehouse_name)` và ghi warning.
- Bỏ qua dòng rỗng.
- Validate `status` chỉ nhận `active|inactive` nếu có map cột status.
- Chặn duplicate trong cùng chunk import theo `warehouse_code`, hoặc `warehouse_name` khi không có code.

---

## 10) Checklist daily sync (sáng import / tối export)

**Sáng import (DigiWin → hệ thống):**

- [ ] Client (client_code)
- [ ] Product master (sku + thuộc tính)
- [ ] Pricing (`產品價格表`)
- [ ] Inventory snapshot (ưu tiên có `warehouse_code`)
- [ ] Net sales history (`Last year net sales.xlsx`) → import vào bảng reporting riêng để đồng bộ order/sales history với DigiWin

**Tối export (hệ thống → DigiWin):**

- [ ] Chuẩn format theo DigiWin template (Phase 1 contract)
- [ ] **Đơn bán (SO):** đúng 11 cột file mẫu `PROJECT MAOLIN/(Order Sample) 訂單寫入模板資料_260525.xlsx` — chi tiết: [`../FUNC_IMPROVE/MAOLIN_DIGIWIN_ORDER_EXPORT_TEMPLATE.md`](../FUNC_IMPROVE/MAOLIN_DIGIWIN_ORDER_EXPORT_TEMPLATE.md)
- [ ] Export theo key ổn định + timestamp (delta hoặc full snapshot)

---

## 11) Phụ lục — tài liệu chi tiết (khi cần đào sâu)

- Import mapping (ready-to-use): [`MAOLIN_IMPORT_MAPPING.md`](MAOLIN_IMPORT_MAPPING.md)
- File-by-file analysis (retired): `git log -- FUNC_LOGIC/PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`
- Warehouse docs (UI + CF + runbook): [`WAREHOUSE_MASTER_GUIDE.md`](WAREHOUSE_MASTER_GUIDE.md)

