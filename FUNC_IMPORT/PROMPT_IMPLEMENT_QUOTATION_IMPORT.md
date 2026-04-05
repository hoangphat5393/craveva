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
    - `implements ToArray`, `getProcessedData()`, **`fields()`** trả về danh sách cột map được (id slug + name + required), **bám spec** `FUNC_IMPORT/IMPORT_QUOTATION.md` (header + line: số báo giá, mã KH, SKU, SL, đơn giá, tiền tệ, ngày, v.v.).
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
- Cập nhật ngắn `FUNC_IMPORT/IMPORT_QUOTATION.md`: mục “đã có import” + link route.

### 8) Import log (optional nhưng nên làm nếu muốn parity đầy đủ)

- Clone pattern `ClientImportLogController` → `EstimateImportLogController`, path `import-logs/estimates/{company_id}/`, routes `estimates/import-log`, views mirror `client-import-log/*` với string lang riêng.

### 9) Kiểm thử

- Feature test hoặc manual: upload CSV Maolin nhỏ, map cột, chạy queue worker `database` queue `EstimateImport`, xác nhận batch hoàn thành và bản ghi `estimates`/`estimate_items` đúng.
- Kiểm tra staging: poll progress (xem `FUNC_IMPORT/IMPORT_MECHANISMS_POLL_AND_QUEUE_VI.md`).

### 10) Giới hạn / không làm

- Không xóa key lang cũ `estimate` / `modules.estimates.*`; UI dùng `quotation_ui` nơi đã có.
- Không đổi tên bảng `estimates` / route `estimates.*`.

---

## Ghi chú nghiệp vụ (tóm tắt file Maolin)

Chi tiết cột: **`FUNC_IMPORT/IMPORT_QUOTATION.md`**. Nhớ: một phiếu nhiều dòng, forward-fill header, FQCN model CF = `App\Models\Estimate`.

---

_File này là prompt hand-off; cập nhật khi route/name class cuối cùng được chốt._
