# Prompt triển khai: `sales_history` (tách khỏi SO vận hành) + Import `Last year net sales.xlsx`

Tài liệu này là **spec để dev/agent triển khai** module lịch sử bán hàng chỉ phục vụ báo cáo / đối soát, **không** đi cùng luồng kho–DO–invoice như Sales Order thật. UI/UX phải **đồng nhất** với chức năng SO hiện có (upload → map cột → queue/batch → progress → log lỗi theo row).

---

## 1) Mục tiêu

1. Lưu dữ liệu “net sales” (kể cả legacy import) vào **bảng snapshot** `sales_history` / `sales_history_lines`, **không** tạo `orders` / `order_items` cho từng dòng file (trừ khi team quyết định migration một lần từ dữ liệu cũ — xem mục 9).
2. Cung cấp **giao diện đầy đủ**: danh sách (DataTable), lọc, xem chi tiết dòng, **Import Excel** giống flow `OrderController::importOrder` + `resources/views/orders/ajax/import*.blade.php`.
3. **Import** file `PROJECT MAOLIN New/Last year net sales.xlsx`: hỗ trợ **1 hoặc nhiều sheet** (đọc toàn bộ sheet có dữ liệu), tối thiểu map các field **bắt buộc** theo bảng dưới (đồng bộ với `FUNC_IMPORT/IMPORT_SALE_ORDER.md`).

---

## 2) Nguyên tắc kiến trúc

| Khía cạnh                       | Quy tắc                                                                                                                                                                                                     |
| ------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Side-effect**                 | Ghi `sales_history` **không** trừ kho, không tạo DO/Invoice, không kích hoạt workflow SO.                                                                                                                   |
| **Nguồn sự thật vận hành**      | Vẫn là `orders` + các module liên quan khi user tạo đơn trong app.                                                                                                                                          |
| **Nguồn báo cáo lịch sử ngoài** | `sales_history_*` + import idempotent theo hash.                                                                                                                                                            |
| **Idempotency**                 | Giữ pattern hash tương tự `order_import_rows` nhưng **trỏ vào** `sales_history_lines.id` (hoặc bảng `sales_import_rows` chỉ dùng cho import — tùy thiết kế, nhưng **không** duplicate dòng khi import lại). |

---

## 3) Schema gợi ý (điều chỉnh theo convention project)

### 3.1 `sales_history` (header theo batch/file — tùy chọn nhưng nên có cho UX)

- `id`, `company_id`, `import_batch_id` (nullable), `source_filename`, `imported_by`, `imported_at`, `notes`, `timestamps`.

### 3.2 `sales_history_lines` (mỗi dòng sau khi normalize)

Tối thiểu:

| Cột                               | Kiểu             | Ghi chú                                                            |
| --------------------------------- | ---------------- | ------------------------------------------------------------------ |
| `company_id`                      | bigint           |                                                                    |
| `sales_history_id`                | bigint nullable  | Nếu dùng header batch                                              |
| `shipment_date`                   | date             | Từ Shipment/Return Date                                            |
| `client_id` / `client_details_id` | bigint           | Resolve từ customer number → `client_details.client_code`          |
| `product_id`                      | bigint           | Resolve từ SKU                                                     |
| `quantity`                        | decimal          | Signed hoặc lưu `is_return` + `quantity_abs` — thống nhất một cách |
| `amount`                          | decimal nullable | Net sales amount                                                   |
| `unit_price`                      | decimal nullable | Suy ra hoặc từ amount/qty                                          |
| `is_return`                       | bool             | true nếu qty/amount âm                                             |
| `currency_id`                     | bigint nullable  | Mặc định `company()->currency_id` nếu không có cột                 |
| `source_sheet_name`               | string nullable  | **Nên có** để truy vết multi-sheet                                 |
| `source_row_hash`                 | string(64)       | **Unique** cùng `company_id` — idempotency                         |
| `raw_*` (optional)                | text/json        | Lưu snapshot raw nếu cần đối soát                                  |

**Index:** `(company_id, source_row_hash)` unique; index `(company_id, shipment_date)`, `(company_id, product_id)`.

---

## 4) Field import tối thiểu (bắt buộc có trong class `::fields()`)

Áp dụng cho map UI và validation row — **khớp** tài liệu hiện có:

| Field ID               | Label (EN hiển thị)       | Required |
| ---------------------- | ------------------------- | -------- |
| `shipment_return_date` | Shipment/Return Date      | Yes      |
| `customer_number`      | Customer Number           | Yes      |
| `product_part_number`  | Product Part Number (SKU) | Yes      |
| `net_sales_volume`     | Net Sales Volume          | Yes      |
| `net_sales_amount`     | Net Sales Amount          | No       |

**Parse:** date (Excel + string), số (dấu phẩy nghìn, âm = return), giống logic đã mô tả trong `ImportSalesOrderChunkJob` / `FUNC_IMPORT/IMPORT_SALE_ORDER.md`.

**Multi-sheet:** Dùng `Maatwebsite\Excel` `WithMultipleSheets` — mỗi sheet append rows vào cùng mảng `processedData` (pattern `SalesOrderImport` + `SalesOrderSheetImport`). Sheet trống bỏ qua.

---

## 5) Backend — cần làm

1. **Migration** tạo bảng (và bảng phụ idempotency nếu tách khỏi unique trên `sales_history_lines`).
2. **Models** `SalesHistory`, `SalesHistoryLine` (relationships tới `Company`, `ClientDetails`/`User`, `Product`).
3. **Controller** (ví dụ `SalesHistoryController` hoặc trong module phù hợp): `index`, (optional `show`), `import`, `importStore`, `importProcess` — **copy pattern** từ `OrderController` (`importOrder`, `importStore`, `importProcess`) + trait `ImportExcel`.
4. **Import class** mới: `SalesHistoryImport` (multi-sheet) + sheet import helper; `fields()` như mục 4.
5. **Job** `ImportSalesHistoryChunkJob` (hoặc đổi tên rõ): xử lý chunk, resolve client/product, fail theo row, **ghi vào `sales_history_lines`**, không tạo Order.
6. **`ImportController`** (`ALLOWED_IMPORT_QUEUE_NAMES`): thêm short name class import mới.
7. **Routes** (ví dụ prefix `sales-history` hoặc `reports/sales-history`): `GET/POST` import + `POST` process — mirror `routes/web.php` block `orders/import`.
8. **Permission**: định nghĩa quyền mới (vd. `view_sales_history`, `add_sales_history_import`) hoặc map tạm sang quyền `add_order`/`view_order` — **ghi rõ trong PR**; tránh để route trần.
9. **Policies / abort_403** giống các màn SO.

---

## 6) UI/UX — đồng nhất SO

1. **Trang index**: layout giống `resources/views/orders/index.blade.php`: tiêu đề, nút **Import Excel** (icon `file-upload`), DataTable server-side.
2. **Import step 1**: clone cấu trúc `resources/views/orders/ajax/import.blade.php` — form id khác, route mới, text dùng key lang **sales history** (không tái dùng nhầm “Orders”).
3. **Import step 2 (map cột)**: dùng component/process chung như SO (`import.process-form` nếu project đã có).
4. **Progress**: clone `resources/views/orders/ajax/import_progress.blade.php` với `processRoute` trỏ route import process mới.
5. **Sidebar / menu**: thêm mục **Sales history** (hoặc dưới nhóm Sales/Báo cáo) — `:active` dùng `request()->routeIs('sales-history.*')` (hoặc prefix đã chọn) để **không** bị lệch trạng thái như bug Products trước đây.
6. **Cảnh báo**: banner/info rõ: dữ liệu chỉ phục vụ thống kê / đối soát, **không** thay thế SO vận hành.

---

## 7) Language Pack

- Mọi chuỗi UI mới: thêm vào `Modules/LanguagePack/Languages/modules/...` **và** file runtime tương ứng trong module đang dùng cho Orders/Sales (theo convention dự án).
- Không để lộ raw key `xxx::modules...` trên UI.

---

## 8) Tests (tối thiểu)

1. Import một dòng hợp lệ → có `SalesHistoryLine`, đúng `source_row_hash`.
2. Import lại cùng dòng → không nhân đôi (idempotent).
3. Client hoặc SKU không tồn tại → không tạo line, có exception/log theo row (nếu codebase hỗ trợ).
4. File nhiều sheet → tổng số dòng = tổng từ các sheet (unit test với mock data hoặc fixture nhỏ).

---

## 9) Dữ liệu đã import vào `orders` trước đây (optional)

- Quyết định product: **(A)** giữ nguyên orders cũ + chỉ dùng `sales_history` cho import mới; **(B)** script one-off migrate từ `order_import_rows` → `sales_history_lines` rồi ẩn/xóa SO import cũ.
- Prompt triển khai: ghi rõ option được chọn trong ticket/PR.

---

## 10) Deprecation SO import “Last year net sales”

- Sau khi `sales_history` ổn định: **ẩn hoặc redirect** luồng `orders/import` dành cho file legacy (hoặc ghi chú redirect tới màn mới) để user không nhầm với SO thật.

---

## 11) Tài liệu

- Cập nhật / tách `FUNC_IMPORT/IMPORT_SALE_ORDER.md` → thêm section **“Import vào Sales History (khuyến nghị)”** với link route và checklist.
- Ghi chú file mẫu: `PROJECT MAOLIN New/Last year net sales.xlsx` (multi-sheet).

---

## 12) Acceptance criteria (checklist)

- [ ] Có thể import workbook 1 sheet và nhiều sheet; đủ 5 field map (4 required + 1 optional).
- [ ] Không tạo `orders` khi import vào sales history (trừ migration riêng nếu có).
- [ ] Idempotent theo hash; không duplicate khi chạy lại.
- [ ] UI giống flow SO: upload → map → progress → lỗi row.
- [ ] Menu active đúng; copy phù hợp theme hiện tại.
- [ ] Đa ngôn ngữ qua Language Pack.
- [ ] Test tự động pass; lint/syntax sạch.

---

## 13) Tham chiếu code hiện có (khi implement)

- `App\Http\Controllers\OrderController::importOrder|importStore|importProcess`
- `App\Imports\SalesOrderImport`, `SalesOrderSheetImport`
- `App\Jobs\ImportSalesOrderChunkJob`
- `app\Traits\ImportExcel`
- `app\Http\Controllers\ImportController::ALLOWED_IMPORT_QUEUE_NAMES`
- `routes/web.php` — block `orders/import`
- `resources/views/orders/ajax/import.blade.php`, `import_progress.blade.php`, `orders/index.blade.php`
- `FUNC_IMPORT/IMPORT_SALE_ORDER.md` — mapping & quy tắc return/parse

---

_File tạo để team/PM/dev dùng làm một prompt duy nhất cho phase triển khai `sales_history` + import file Maolin._
