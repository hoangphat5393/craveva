# Cơ chế import, poll tiến độ và queue — ghi chú & giải pháp

Tài liệu này tóm tắt **khác nhau giữa các luồng import** trong code, **vì sao poll / thanh tiến độ** có thể lỗi trên staging, và **giải pháp** vận hành. Liên quan trực tiếp: `ImportController@getImportProgress`, `ImportExcel`, `config/app.php` (`import_progress_*`).

---

## 1. Ba nhóm cơ chế trong code

### A. `importJobProcessChunked` (chunk theo dòng)

- **Trait:** `ImportExcel::importJobProcessChunked()`
- **Cách làm:** Đọc file → gom dữ liệu → `array_chunk($excelData, $chunkSize)` (mặc định thường **100 dòng/job**) → batch tên kiểu `{ShortClassName}-chunked`, queue = short name class (vd. `ClientImport`, `ProductImport`).
- **Đặc điểm:** File **càng dài** → **càng nhiều job** trong batch; mỗi job xử lý một **khối dòng**, không phải một dòng (trừ khi chunk = 1).

**Module đang dùng (tham chiếu code):**

| Module                             | Controller (gợi ý)                               | Queue / import class                            |
| ---------------------------------- | ------------------------------------------------ | ----------------------------------------------- |
| **Client**                         | `ClientController@importProcess`                 | `ClientImport` → `ImportClientChunkJob`         |
| **Product**                        | `ProductController`, `PurchaseProductController` | `ProductImport` → `ImportProductChunkJob`       |
| **Sales Order**                    | `OrderController`                                | `SalesOrderImport` → `ImportSalesOrderChunkJob` |
| **Warehouse** (import kho / chunk) | `WarehouseController` (module Warehouse)         | `WarehouseImport` → `ImportWarehouseChunkJob`   |

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

## 1bis. Ưu / nhược điểm từng cơ chế

### A. `importJobProcessChunked`

| Ưu điểm                                                                 | Nhược điểm                                                                                                                                                                                                                               |
| ----------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Ít job hơn** so với 1 dòng/job → queue gọn, overhead nhỏ hơn.         | Bước **`Excel::import` + gom `getProcessedData()`** thường **nạp cả workbook** vào bộ nhớ trong **một request** trước khi dispatch — file **rất lớn** (nhiều sheet / nhiều dòng) dễ **OOM** hoặc chạm `memory_limit` / thời gian upload. |
| Mỗi job xử lý **khối dòng** → batch DB / bulk phù hợp (Client/Product). | Cần class `*Import` + `*ChunkJob` đồng bộ; tăng chunk quá lớn → một job nặng, quá nhỏ → quá nhiều job.                                                                                                                                   |
| Tiến độ poll nhìn “mượt” hơn khi số job vừa phải.                       | Với **Excel nhiều sheet**: phụ thuộc **cách class Import đọc file** — nếu chỉ map/đọc sheet đầu, các sheet sau có thể **không vào** luồng (tùy code từng module).                                                                        |

### B. `importJobProcess` (1 job = 1 dòng)

| Ưu điểm                                            | Nhược điểm                                                                                                                          |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| Logic **theo dòng** đơn giản, dễ map lỗi “dòng N”. | **Số job = số dòng** → file lớn tạo **hàng nghìn job** → DB `jobs` phình, worker lâu, poll dễ “nặng” nếu chạy worker trong request. |
| Phù hợp file **nhỏ / vừa**.                        | Cùng vấn đề **parse full file một lần** trước khi tạo batch → RAM cao với workbook lớn.                                             |
|                                                    | Với **nhiều sheet**: vẫn phụ thuộc Import class gom sheet thế nào; thường **không tối ưu** bằng stream từng sheet.                  |

### C. Sales History — stream (sheet + range dòng)

| Ưu điểm                                                                                                                                                    | Nhược điểm                                                                                                |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Thiết kế cho **nhiều sheet**: metadata từng sheet → job theo **sheet + khoảng dòng** → **không** cố gom hết workbook vào một mảng khổng lồ trong một bước. | Code phức tạp hơn (reader, filter, job stream).                                                           |
| Bước **map cột** chỉ cần **sheet đầu** (nhẹ); phần nặng **chia job**.                                                                                      | Mỗi job vẫn **mở/đọc lại file** theo range (trade-off đã ghi trong `ORDER_HISTORY_IMPROVE_PLAN`).         |
| Chunk có thể cấu hình (`SALES_HISTORY_IMPORT_ROWS_PER_JOB`) → cân bằng job vs thời gian.                                                                   | Chỉ áp cho **nghiệp vụ Sales History** trong codebase hiện tại — không thay thế trực tiếp Client/Product. |

---

## 1ter. So sánh khi upload **Excel nhiều sheet** — cơ chế nào “tốt” hơn?

**Không có một đáp án tuyệt đối** — phụ thuộc **nghiệp vụ** và **cách class Import đọc file**. Trong repo này có thể rút ra:

| Kịch bản                                                                              | Gợi ý trong thực tế codebase                                                                                                                                                   |
| ------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Nhiều sheet, mỗi sheet cùng kiểu cột (báo cáo bán / lịch sử theo tháng, v.v.)**     | **Luồng Sales History (stream)** phù hợp **hơn về kiến trúc**: tách job theo sheet + dòng, tránh một lần parse toàn bộ vào RAM. Đây là hướng đã chọn cho `SalesHistoryImport`. |
| **Nhiều sheet nhưng nghiệp vụ chỉ cần một sheet (hoặc Import class chỉ đọc sheet 1)** | **`importJobProcessChunked`** vẫn dùng được; khi đó “nhiều sheet” trong file **không được khai thác** trừ khi sửa `*Import` để gộp/duyệt sheet — cần kiểm tra từng module.     |
| **Một sheet nhưng cực nhiều dòng (CSV/Excel một trang)**                              | **Chunked** thường **hợp lý hơn** 1 dòng/job: giảm số job, dễ tối ưu bulk.                                                                                                     |
| **File vừa, cần debug từng dòng**                                                     | **`importJobProcess`** (Inventory) có thể chấp nhận được; file lớn thì **không** ưu tiên.                                                                                      |

**Tóm lại ngắn:**

- **Excel nhiều sheet + cần import xuyên suốt các sheet** → trong các cơ chế đang có, **mô hình stream kiểu Sales History** là **hướng đúng** (tách job theo sheet/range).  
- **Excel nhiều sheet nhưng luồng nghiệp vụ chỉ một sheet** hoặc **gom về một bảng** → **chunked** vẫn ổn nếu class Import xử lý đúng.  
- **Chỉ so “tốt hơn” theo số sheet** là không đủ: phải kèm **RAM**, **số job**, và **yêu cầu nghiệp vụ**.

---

## 1quater. Custom field (CF) — ảnh hưởng và phương pháp nào phù hợp khi **nhiều CF**

### CF làm thay đổi gì (trong hệ thống)

- **Bước map cột:** Với Client, `ClientImport::mergeDynamicColumns()` gộp thêm cột từ DB (`CustomField` nhóm Client) vào danh sách map — **càng nhiều CF** → dropdown map **càng dài**, mỗi dòng file **càng nhiều ô** cần xử lý.
- **Payload mỗi dòng / mỗi job:** Mỗi dòng không chỉ cột cố định mà còn **giá trị CF** (thường lưu JSON / bảng trung gian) → **bộ nhớ** và **kích thước serialize job** tăng.
- **Xử lý trong job:** Ví dụ `ImportClientChunkJob` **load một lần** map CF (`ClientImportProcessor::getClientCustomFieldMap`) **cho cả chunk**, rồi xử lý nhiều dòng trong chunk — tránh lặp query CF **từng dòng** trong cùng job.

### So sánh nhanh theo **cơ chế** khi nghiệp vụ có **nhiều CF**

| Cơ chế | Khi **nhiều CF** | Ghi chú |
|--------|------------------|---------|
| **`importJobProcessChunked`** | **Thường phù hợp nhất** trong các pattern đang có: một job xử lý **nhiều dòng** → amortize chi phí load map CF / lookup role / bulk insert một lần; dễ tối ưu bulk ghi CF theo lô trong chunk. | Cần giữ **chunk size** hợp lý: chunk quá lớn + mỗi dòng rất nhiều CF → một job vẫn có thể nặng (OOM / timeout job). |
| **`importJobProcess` (1 dòng / job)** | **Kém hơn** khi file lớn: số job ≈ số dòng × **chi phí cố định** (resolve CF, permission, …) lặp lại; queue và DB `jobs` phình. | Chỉ chấp nhận được khi **dữ liệu nhỏ** hoặc tạm thời; không lý tưởng cho “nhiều CF + nhiều dòng”. |
| **Sales History (stream)** | Hiện tập trung **cột nghiệp vụ cố định** (MAOLIN); **ít** merge CF động kiểu Client. Nếu sau này **mỗi dòng cần nhiều CF động**, cần thiết kế tương tự (merge cột + job xử lý batch trong range) — **không** tự động “tốt hơn chunked” chỉ vì stream. | Ưu điểm stream vẫn là **chia theo sheet/range**, không phải vì CF. |

### Khuyến nghị thực tế

1. **Module có nhiều CF + import số lượng lớn** → ưu tiên **`importJobProcessChunked`** (hoặc tương đương: nhiều dòng/job), **preload** map CF / lookup **một lần mỗi chunk** (như Client).  
2. **Giảm `chunk_size`** nếu một dòng đã **rất nặng** (nhiều CF + logic phức tạp) để tránh một job chạy quá lâu.  
3. **Tránh** 1 job = 1 dòng khi **vừa nhiều dòng vừa nhiều CF**, trừ file nhỏ.  
4. **Bước upload** vẫn parse cả file trước khi dispatch — CF **không** giảm RAM lúc parse; cần **`memory_limit`** đủ và file không quá khổ (hoặc tách file).

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

| Mục                                      | Nội dung                                                                                                                                                                                                                       |
| ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Giới hạn thời gian worker trong poll** | `ImportController`: `queue:work` thêm `--max-time` (config `IMPORT_PROGRESS_WORKER_MAX_SECONDS`, mặc định **25** trong `config/app.php`). Tránh request poll bị cắt giữa chừng.                                                |
| **Giới hạn số job mỗi poll**             | `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` (mặc định 8).                                                                                                                                                                        |
| **Không chạy worker trong poll**         | `IMPORT_PROGRESS_RUN_QUEUE_WORKER=false` — poll chỉ đọc trạng thái; **bắt buộc** cron + `queue:work` trong `app/Console/Kernel.php` có đủ queue (`ClientImport`, `ProductImport`, `InventoryImport`, `SalesHistoryImport`, …). |
| **Cron đúng user**                       | `schedule:run` nên cùng user với PHP-FPM (vd. `www-data`) để ghi `storage`/cache không lỗi quyền — xem `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md`.                                                                         |
| **Timeout reverse proxy**                | Nếu vẫn bật worker trong poll, cân nhắc tăng `fastcgi_read_timeout` (nginx) cho route poll hoặc giảm `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` / `IMPORT_PROGRESS_WORKER_MAX_SECONDS`.                                         |

---

## 4. Liên quan tài liệu khác

- Staging / Sales History / quyền / Git: `FUNC_LOGIC/ORDER_HISTORY_IMPROVE_PLAN.MD`
- PHP-FPM, quyền thư mục: `docs/LARAVEL_PHP_FPM_QUEUE_PERMISSIONS_VI.md`
- Mapping cột Client (nghiệp vụ): `FUNC_IMPORT/IMPORT_CLIENT.md`
- CF động Client: `ClientImport::mergeDynamicColumns`, job chunk: `ImportClientChunkJob` + `ClientImportProcessor`

---

## 5. Tóm tắt một dòng

**Client + Product (+ Sales Order, Warehouse chunk)** dùng **`importJobProcessChunked`**;
**Inventory (Purchase)** dùng **`importJobProcess`** (1 job/dòng).
**Sales History** là luồng **stream** riêng — **ưu thế khi Excel nhiều sheet** cần xử lý xuyên suốt (xem mục **1ter**).
**Nhiều custom field (CF)** → thường **ưu tiên chunked** + preload CF theo chunk (mục **1quater**).
**Poll UI** dùng chung; rủi ro timeout khi **bật worker trong poll** + **file lớn / quá nhiều job** — xử lý bằng **`--max-time`**, giảm job mỗi poll, hoặc tắt worker trong poll và dựa **cron/worker nền**.
