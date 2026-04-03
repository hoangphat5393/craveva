# Cơ chế import, poll tiến độ và queue — ghi chú & giải pháp

Tài liệu này tóm tắt **khác nhau giữa các luồng import** trong code, **vì sao poll / thanh tiến độ** có thể lỗi trên staging, và **giải pháp** vận hành. Liên quan trực tiếp: `ImportController@getImportProgress`, `ImportExcel`, `config/app.php` (`import_progress_*`).

---

## 1. Ba nhóm cơ chế trong code

### A. `importJobProcessChunked` (chunk theo dòng)

- **Trait:** `ImportExcel::importJobProcessChunked()`
- **Cách làm:** Đọc file → gom dữ liệu → `array_chunk($excelData, $chunkSize)` (mặc định thường **100 dòng/job**) → batch tên kiểu `{ShortClassName}-chunked`, queue = short name class (vd. `ClientImport`, `ProductImport`).
- **Đặc điểm:** File **càng dài** → **càng nhiều job** trong batch; mỗi job xử lý một **khối dòng**, không phải một dòng (trừ khi chunk = 1).

**Module đang dùng (tham chiếu code):**

| Module | Controller (gợi ý) | Queue / import class |
|--------|---------------------|----------------------|
| **Client** | `ClientController@importProcess` | `ClientImport` → `ImportClientChunkJob` |
| **Product** | `ProductController`, `PurchaseProductController` | `ProductImport` → `ImportProductChunkJob` |
| **Sales Order** | `OrderController` | `SalesOrderImport` → `ImportSalesOrderChunkJob` |
| **Warehouse** (import kho / chunk) | `WarehouseController` (module Warehouse) | `WarehouseImport` → `ImportWarehouseChunkJob` |

→ **Client và Product** (và các mục trên) **cùng một “kiểu” chunked** — không phải vì “ít sheet” hay “một file lớn không sheet”, mà vì **cùng hàm** `importJobProcessChunked`.

### B. `importJobProcess` (một job = một dòng)

- **Trait:** `ImportExcel::importJobProcess()`
- **Cách làm:** Đọc file → **mỗi dòng một job** → batch tên = short class name (vd. `InventoryImport`), queue cùng tên.
- **Đặc điểm:** File **N dòng** → **N job** — với file lớn, số job có thể **rất lớn hơn** cả chunked (100 dòng/job).

**Ví dụ:** **Inventory** (Purchase) — `PurchaseInventoryController@importProcess` dùng `importJobProcess` với `ImportInventoryJob`.

→ **Inventory (Purchase) không giống hệt Client:** Client dùng **chunk**; Inventory dùng **1 job / dòng**. Cả hai vẫn dùng **cùng UI poll** (`import.process-form`) và **cùng** `getImportProgress`, nên **cùng chịu ảnh hưởng** khi bật worker trong request poll (mục 3).

### C. Sales History — stream theo sheet + khoảng dòng

- **Controller:** `SalesHistoryController@importProcess`
- **Job:** `ImportSalesHistoryStreamJob` — không qua `importJobProcess` / `importJobProcessChunked` của trait theo cùng một pattern Client.
- **Cách làm:** Đọc metadata sheet → tạo job theo **sheet + range dòng**; queue `SalesHistoryImport`.
- **Đặc điểm:** Có thể nhiều job vì **nhiều sheet** và/hoặc **nhiều dòng**; file nhỏ → ít job.

---

## 2. Vì sao “Sales History ổn” mà “Client / import nặng” lại hỏng poll?

**Không** phải do “Client = 1 sheet lớn, Sales History = nhiều sheet” một cách đơn giản.

- Khi bật **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`**, **mỗi lần GET poll** có thể chạy `queue:work` **trong cùng request HTTP** (xử lý tối đa vài job, có **`--max-time`** để không vượt timeout).
- **Client / Product (chunked)** với **file rất lớn** → **rất nhiều job** → mỗi vòng worker có thể **lâu** → dễ chạm **timeout nginx/php-fpm** nếu không giới hạn thời gian worker trong poll.
- **Trước khi bật** worker trong poll: poll **chỉ đọc** DB → request **nhẹ**; tiến độ nhích nhờ **cron** `schedule:run` / worker nền — cảm giác “ổn” nếu cron chạy đều.
- **Sales History** với file **nhỏ / ít job** → mỗi lần poll (kể cả có worker) thường **nhanh**.

Tóm lại: khác biệt chính là **số job và thời gian xử lý mỗi lần poll**, không phải chỉ “nhiều sheet hay không”.

---

## 3. Giải pháp (đã có trong code + vận hành)

| Mục | Nội dung |
|-----|----------|
| **Giới hạn thời gian worker trong poll** | `ImportController`: `queue:work` thêm `--max-time` (config `IMPORT_PROGRESS_WORKER_MAX_SECONDS`, mặc định **25** trong `config/app.php`). Tránh request poll bị cắt giữa chừng. |
| **Giới hạn số job mỗi poll** | `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` (mặc định 8). |
| **Không chạy worker trong poll** | `IMPORT_PROGRESS_RUN_QUEUE_WORKER=false` — poll chỉ đọc trạng thái; **bắt buộc** cron + `queue:work` trong `app/Console/Kernel.php` có đủ queue (`ClientImport`, `ProductImport`, `InventoryImport`, `SalesHistoryImport`, …). |
| **Cron đúng user** | `schedule:run` nên cùng user với PHP-FPM (vd. `www-data`) để ghi `storage`/cache không lỗi quyền — xem `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md`. |
| **Timeout reverse proxy** | Nếu vẫn bật worker trong poll, cân nhắc tăng `fastcgi_read_timeout` (nginx) cho route poll hoặc giảm `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` / `IMPORT_PROGRESS_WORKER_MAX_SECONDS`. |

---

## 4. Liên quan tài liệu khác

- Staging / Sales History / quyền / Git: `FUNC_LOGIC/ORDER_HISTORY_IMPROVE_PLAN.MD`
- PHP-FPM, quyền thư mục: `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md`
- Mapping cột Client (nghiệp vụ): `FUNC_IMPORT/IMPORT_CLIENT.md`

---

## 5. Tóm tắt một dòng

**Client + Product (+ Sales Order, Warehouse chunk)** dùng **`importJobProcessChunked`**; **Inventory (Purchase)** dùng **`importJobProcess`** (1 job/dòng). **Sales History** là luồng **stream** riêng. **Poll UI** dùng chung; rủi ro timeout khi **bật worker trong poll** + **file lớn / quá nhiều job** — xử lý bằng **`--max-time`**, giảm job mỗi poll, hoặc tắt worker trong poll và dựa **cron/worker nền**.
