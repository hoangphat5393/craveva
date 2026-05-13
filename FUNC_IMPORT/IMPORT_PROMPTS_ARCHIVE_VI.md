# FUNC_IMPORT — Archive prompt triển khai (đã có code)

> **Mục đích:** gộp hai file prompt cũ (`PROMPT_IMPLEMENT_QUOTATION_IMPORT.md`, `SALES_HISTORY_IMPLEMENTATION_PROMPT.md`). Tính năng đã triển khai — giữ để tham chiếu hand-off / tái triển khai module tương tự.
>
> **Spec cột / mapping:** `FUNC_IMPORT/IMPORT_SPECS_VI.md`
>
> **Cơ chế poll/queue + tracker kho:** `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md`

---

## A. Import Quotation (Estimate)
# Prompt triển khai: Import Quotation (Estimate) — parity với Import Client

Dán prompt dưới đây cho dev / AI Agent. Ngữ cảnh codebase: **Laravel + Maatwebsite Excel + queue `database` + Bus batch chunk** như module Client.

---

## Prompt (copy từ đây)

Bạn đang làm việc trên repo **Craveva staging** (Worksuite-style). Hãy triển khai **import Quotation** (model `App\Models\Estimate` + `EstimateItem`), **UX/UI và cơ chế import giống Import Client**: upload file → màn hình map cột (shared `import.process-form`) → Start import → progress poll → (tuỳ chọn) log batch.

### 1) Chuẩn tham chiếu — Import Client (bắt buộc đối chiếu)

Đọc và mirror pattern sau:

| Thành phần            | File / vị trí                                                                                                                                                  |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Route                 | `routes/web.php`: `clients/import`, `clients/import/store`, `clients/import/process`, `clients/import-log`                                                     |
| Controller            | `ClientController::importClient`, `importStore`, `importProcess` — dùng trait `ImportExcel`                                                                    |
| Upload + map          | `importFileProcess($request, ClientImport::class)`                                                                                                             |
| Chunk batch           | `importJobProcessChunked($request, ClientImport::class, ImportClientChunkJob::class, $chunkSize)` + `Cache::put('import_metrics_' . $batchId, ...)`            |
| FormRequest           | `App\Http\Requests\Admin\Employee\ImportRequest` (file), `ImportProcessRequest` (process)                                                                      |
| Import class          | `App\Imports\ClientImport` — `fields()`, `mergeDynamicColumns()` (CF Client), `ToArray`, `getProcessedData()`                                                  |
| Job                   | `App\Jobs\ImportClientChunkJob` — `Batchable`, `StoresImportBatchMetrics`                                                                                      |
| Processor             | `App\Services\ClientImportProcessor`                                                                                                                           |
| Views                 | `resources/views/clients/ajax/import.blade.php`, `clients/ajax/import_progress.blade.php` (@include `import.process-form`), nút trên `clients/index.blade.php` |
| Import log (optional) | `ClientImportLogController` + `storage/import-logs/clients/{company_id}/*.json`                                                                                |

### 2) UX/UI parity (bắt buộc)

- Trên **`resources/views/estimates/index.blade.php`** (hoặc vị trí tương đương danh sách Quotation): thêm **hai nút** giống Client:
    - **Import** → `openRightModal`, route GET import (form upload).
    - (Tuỳ chọn) **Import log** nếu implement log như Client.
- Modal bước 1: clone cấu trúc `clients/ajax/import.blade.php`:
    - `x-form` id riêng, `x-forms.file` `import_file`, link **sample** (`public/sample-import/quotation-sample.xlsx` hoặc `.csv`), nút **Upload next** gọi POST store bằng `apiHttp.postForm` (giống client).
- Bước 2: view `estimates/ajax/import_progress.blade.php` chỉ `@include('import.process-form', [...])` với:
    - `headingTitle`: dùng `__('app.importExcel')` + `__('app.quotation_ui.singular')` hoặc `page_title` (thống nhất copy UI).
    - `processRoute`: `route('estimates.import.process')`.
    - `backRoute`: `route('estimates.index')`.
    - `backButtonText`: lang key mới hoặc reuse pattern `backToClient`.
- Checkbox heading / skip footer giữ nguyên hành vi như Client (request boolean `heading` / `skip_footer` trên store; `has_heading` / `has_skip_footer` trên process — đúng như `importFileProcess` / `ImportProcessRequest` đang truyền).

### 3) Cơ chế import (bắt buộc giống Client)

- `EstimateController` (hoặc controller riêng `EstimateImportController` nếu muốn tách):
    - `importQuotation()` — permission `add_estimates` in `['all','added']`, ajax return view modal.
    - `importStore(ImportRequest $request)` — `importFileProcess($request, EstimateImport::class)`, trả `import_progress` render như Client.
    - `importProcess(ImportProcessRequest $request)` — `importJobProcessChunked(..., EstimateImport::class, ImportEstimateChunkJob::class, $chunkSize)` + `import_metrics_` cache keys tương thích processor.
- Tạo **`App\Imports\EstimateImport`**:
    - `implements ToArray`, `getProcessedData()`, **`fields()`** trả về danh sách cột map được (id slug + name + required), **bám spec** `FUNC_IMPORT/IMPORT_SPECS_VI.md § 5. Báo giá` (header + line: số báo giá, mã KH, SKU, SL, đơn giá, tiền tệ, ngày, v.v.).
    - **`mergeDynamicColumns()`**: merge **Custom Field** nhóm **Estimate** (`CustomFieldGroup` `name` = `Estimate`, `model` = `Estimate::CUSTOM_FIELD_MODEL`) — copy pattern `ClientImport::mergeDynamicColumns`.
- Tạo **`App\Jobs\ImportEstimateChunkJob`**: chunk rows, `StoresImportBatchMetrics`, gọi service xử lý từng row (hoặc nhiều row trong chunk).
- Tạo **`App\Services\EstimateImportProcessor`** (hoặc tên tương tự):
    - Parse số có dấu phẩy, ngày ROC nếu có.
    - **Forward-fill** header theo key nhóm (vd. cột số báo giá Maolin `報價單號`).
    - Resolve `client_id` từ mã khách (đồng bộ với master Client đã import), `product_id` từ SKU, `currency_id`.
    - Tạo/cập nhật `Estimate` + `EstimateItem`; map thuế / `calculate_tax` theo rule đã chốt; ghi **custom_fields_data** nếu map tới CF Estimate.

### 4) Sửa `ImportExcel` trait (bắt buộc để giống Client về performance)

Thêm `EstimateImport::class` vào **cùng các mảng điều kiện** như `ClientImport` ở:

- `importClassUsesLightMap()` — để bước upload không full `Excel::import`.
- `importFileProcess()` — nhánh merge dynamic columns cho Estimate (tương tự block `ClientImport::mergeDynamicColumns`).
- `importJobProcess()` và **`importJobProcessChunked()`** — mảng `loadFirstSheetDataRowsByRowRange(...)` khi dùng light path.

Nếu thiếu bước này, quotation import sẽ **chậm / tốn RAM** khác Client.

### 5) Queue, poll progress, security whitelist

- `app/Http/Controllers/ImportController.php`: thêm **`EstimateImport`** vào `ALLOWED_IMPORT_QUEUE_NAMES`.
- `app/Console/Kernel.php`: thêm **`EstimateImport`** vào `DATABASE_WORKER_QUEUE_NAMES` (comment nói sync với ImportController).
- Batch name queue: short class name **`EstimateImport`** (giống `ClientImport`).

### 6) Quyền & module

- Chỉ user có module `estimates` + `add_estimates` (và không conflict `view_estimates` nếu cần xem log).
- `abort_403` mirror `ClientController` import methods.

### 7) Sample file & tài liệu

- Thêm file mẫu tối thiểu (vài dòng) dưới `public/sample-import/quotation-sample.xlsx` hoặc `.csv` khớp `fields()`.
- Cập nhật ngắn `FUNC_IMPORT/IMPORT_SPECS_VI.md § 5. Báo giá`: mục “đã có import” + link route.

### 8) Import log (optional nhưng nên làm nếu muốn parity đầy đủ)

- Clone pattern `ClientImportLogController` → `EstimateImportLogController`, path `import-logs/estimates/{company_id}/`, routes `estimates/import-log`, views mirror `client-import-log/*` với string lang riêng.

### 9) Kiểm thử

- Feature test hoặc manual: upload CSV Maolin nhỏ, map cột, chạy queue worker `database` queue `EstimateImport`, xác nhận batch hoàn thành và bản ghi `estimates`/`estimate_items` đúng.
- Kiểm tra staging: poll progress (xem `FUNC_IMPORT/IMPORT_POLL_TRACKERS_VI.md`).

### 10) Giới hạn / không làm

- Không xóa key lang cũ `estimate` / `modules.estimates.*`; UI dùng `quotation_ui` nơi đã có.
- Không đổi tên bảng `estimates` / route `estimates.*`.

---

## Ghi chú nghiệp vụ (tóm tắt file Maolin)

Chi tiết cột: **`FUNC_IMPORT/IMPORT_SPECS_VI.md § 5. Báo giá`**. Nhớ: một phiếu nhiều dòng, forward-fill header, FQCN model CF = `App\Models\Estimate`.

---

_File này là prompt hand-off; cập nhật khi route/name class cuối cùng được chốt._
---

## B. Sales history + import Last year net sales
# Prompt triển khai: `sales_history` (tách khỏi SO vận hành) + Import `Last year net sales.xlsx`

Tài liệu này là **spec để dev/agent triển khai** module lịch sử bán hàng chỉ phục vụ báo cáo / đối soát, **không** đi cùng luồng kho–DO–invoice như Sales Order thật. UI/UX phải **đồng nhất** với chức năng SO hiện có (upload → map cột → queue/batch → progress → log lỗi theo row).

---

## 1) Mục tiêu

1. Lưu dữ liệu “net sales” (kể cả legacy import) vào **bảng snapshot** `sales_history` / `sales_history_lines`, **không** tạo `orders` / `order_items` cho từng dòng file (trừ khi team quyết định migration một lần từ dữ liệu cũ — xem mục 9).
2. Cung cấp **giao diện đầy đủ**: danh sách (DataTable), lọc, xem chi tiết dòng, **Import Excel** giống flow `OrderController::importOrder` + `resources/views/orders/ajax/import*.blade.php`.
3. **Import** file `PROJECT MAOLIN New/Last year net sales.xlsx`: hỗ trợ **1 hoặc nhiều sheet** (đọc toàn bộ sheet có dữ liệu), tối thiểu map các field **bắt buộc** theo bảng dưới (đồng bộ với `FUNC_IMPORT/IMPORT_SPECS_VI.md § 4. Sale Order`).

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

**Parse:** date (Excel + string), số (dấu phẩy nghìn, âm = return), giống logic đã mô tả trong `ImportSalesOrderChunkJob` / `FUNC_IMPORT/IMPORT_SPECS_VI.md § 4. Sale Order`.

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

- Cập nhật / tách `FUNC_IMPORT/IMPORT_SPECS_VI.md § 4. Sale Order` → thêm section **“Import vào Sales History (khuyến nghị)”** với link route và checklist.
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
- `FUNC_IMPORT/IMPORT_SPECS_VI.md § 4. Sale Order` — mapping & quy tắc return/parse

---

_File tạo để team/PM/dev dùng làm một prompt duy nhất cho phase triển khai `sales_history` + import file Maolin._