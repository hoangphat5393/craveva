# MAOLIN Master Guide (Single source of truth)

**Mục tiêu:** Gộp các ghi chú MAOLIN/Miaolin rải rác thành **1 tài liệu dễ đọc**. Hệ thống hiện đã có **multi-warehouse** nên phần kho/lô/HSD ưu tiên theo **core DB**, hạn chế custom fields.

**Tài liệu này thay cho việc phải đọc nhiều file.**

Trước đây các nội dung nằm rải rác trong các file MAOLIN/Miaolin khác; hiện đã **gộp** để dễ đọc. Bảng mapping vận hành vẫn nằm ở `MAOLIN_IMPORT_MAPPING.md`.

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
9. Checklist vận hành daily sync (sáng import / tối export)
10. Phụ lục: link tài liệu chi tiết

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

**Custom field (nếu còn dùng BI/segmentation):**

- `salesperson`, `department`, `sales_assistant_name`
- `channel_type`, `business_type`, `customer_grade`
- `payment_terms`, `last_transaction_at`, `business_closure_date`, `region`

**Nếu muốn bỏ hết Client CF để sync “đầy đủ” vẫn giữ data:** cân nhắc chuẩn hoá DB theo lookup:

- `payment_term_id`
- `customer_grade_id`
- `channel_type_id`
- `business_type_id`

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

Chi tiết thao tác: `WAREHOUSE_UI_OPERATIONS_GUIDE.md`.

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

## 9) Checklist daily sync (sáng import / tối export)

**Sáng import (DigiWin → hệ thống):**

- [ ] Client (client_code)
- [ ] Product master (sku + thuộc tính)
- [ ] Pricing (`產品價格表`)
- [ ] Inventory snapshot (ưu tiên có `warehouse_code`)
- [ ] Net sales history (`Last year net sales.xlsx`) → import vào bảng reporting riêng để đồng bộ order/sales history với DigiWin

**Tối export (hệ thống → DigiWin):**

- [ ] Chuẩn format theo DigiWin template (Phase 1 contract)
- [ ] Export theo key ổn định + timestamp (delta hoặc full snapshot)

---

## 10) Phụ lục — tài liệu chi tiết (khi cần đào sâu)

- Import mapping (ready-to-use): `MAOLIN_IMPORT_MAPPING.md`
- File-by-file analysis: `PROJECT_MAOLIN_NEW_FILES_ANALYSIS.md`
- Warehouse UI operations: `WAREHOUSE_UI_OPERATIONS_GUIDE.md`
- CF rationalization: `WAREHOUSE_CUSTOM_FIELDS_RATIONALIZATION.md`
