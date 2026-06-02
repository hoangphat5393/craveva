# Cơ chế import, poll tiến độ và queue — ghi chú & giải pháp

Tài liệu này tóm tắt **khác nhau giữa các luồng import** trong code, **vì sao poll / thanh tiến độ** có thể lỗi trên staging, và **giải pháp** vận hành. Liên quan trực tiếp: `ImportController@getImportProgress`, `ImportExcel`, `config/app.php` (`import_progress_*`).

---

## 1. Ba nhóm cơ chế trong code

### A. `importJobProcessChunked` (chunk theo dòng)

- **Trait:** `ImportExcel::importJobProcessChunked()` — tham số tùy chọn thứ 6: **`$allowBatchFailures`** (vd. **Sales History** = `true` để `Bus::batch(...)->allowFailures()`).
- **Cách làm:** Đọc file → gom dữ liệu → `array_chunk($excelData, $chunkSize)` (mặc định thường **100 dòng/job**) → batch tên kiểu `{ShortClassName}-chunked`, queue = short name class (vd. `ClientImport`, `ProductImport`).
- **Đặc điểm:** File **càng dài** → **càng nhiều job** trong batch; mỗi job xử lý một **khối dòng**, không phải một dòng (trừ khi chunk = 1).

**Module đang dùng (tham chiếu code):**

| Module                             | Controller (gợi ý)                               | Queue / import class                                | Batch name (chunk)           |
| ---------------------------------- | ------------------------------------------------ | --------------------------------------------------- | ---------------------------- |
| **Client**                         | `ClientController@importProcess`                 | `ClientImport` → `ImportClientChunkJob`             | `ClientImport-chunked`       |
| **Product**                        | `ProductController`, `PurchaseProductController` | `ProductImport` → `ImportProductChunkJob`           | `ProductImport-chunked`      |
| **Inventory** (Purchase)           | `PurchaseInventoryController@importProcess`      | `InventoryImport` → `ImportInventoryChunkJob`       | `InventoryImport-chunked`    |
| **Sales Order**                    | `OrderController`                                | `SalesOrderImport` → `ImportSalesOrderChunkJob`     | `SalesOrderImport-chunked`   |
| **Warehouse** (import kho / chunk) | `WarehouseController` (module Warehouse)         | `WarehouseImport` → `ImportWarehouseChunkJob`       | `WarehouseImport-chunked`    |
| **Sales History**                  | `SalesHistoryController@importProcess`           | `SalesHistoryImport` → `ImportSalesHistoryChunkJob` | `SalesHistoryImport-chunked` |

- **Chunk size:** mặc định **100**; `Product`, **Inventory**, **Sales History** có thể gửi `chunk_size` trong request hoặc config (Sales History: `craveva_import.sales_history_rows_per_job`). `Product` thêm `options` (`default_unit_id`). **Sales History** tạo bản ghi `sales_histories` trước batch, truyền **`sales_history_id`** trong `options` cho mỗi chunk job.
- **Cùng một “kiểu” chunked** vì **cùng hàm** `importJobProcessChunked` — đọc dữ liệu (ưu tiên **`loadFirstSheetDataRowsByRowRange`** cho Client / Product / Inventory / **Sales History** / **Warehouse**), `normalizeExcelRows`, rồi `array_chunk` trước khi dispatch batch.
- **Inventory (Purchase):** logic từng dòng nằm trong `InventoryImportRowProcessor` (dùng chung cho chunk job). `ImportInventoryJob` vẫn tồn tại nếu có luồng gọi `importJobProcess` với `InventoryImport` (hiện controller chính đã chuyển sang chunk).
- **Sales History:** **chỉ sheet đầu** của workbook; các sheet khác **bị bỏ qua**. Job chunk prefetch client/SKU/hash và **bulk insert** (cùng ý tưởng tối ưu DB trước đây trong luồng stream cũ). Lỗi từng dòng → `import_row_errors_*` + **`invalid_status`**; job **không throw** để `pending_jobs` giảm đúng; batch vẫn **`allowFailures()`** cho lỗi cứng (timeout, v.v.).

### B. `importJobProcess` (một job = một dòng)

- **Trait:** `ImportExcel::importJobProcess()`
- **Cách làm:** Đọc file → **mỗi dòng một job** → batch tên = short class name (vd. `EmployeeImport`), queue cùng tên.
- **Đặc điểm:** File **N dòng** → **N job** — với file lớn, số job có thể **rất lớn** so với chunked (100 dòng/job).

**Module vẫn dùng 1 job / dòng (tham chiếu `grep importJobProcess`):** Deal, Lead, Expense, Employee, Project, Attendance, Job Application (Recruit), Pricing (`ClientProductPricingImport`, `PricingTierItemsImport`), … — **không** gồm Client / Product / Inventory / Sales Order / Warehouse / **Sales History** chunk ở trên.

→ **Client, Product, Inventory (Purchase), Warehouse, Sales History, Sales Order** dùng **chunk** như bảng mục A. Các module mục B vẫn dùng **cùng UI poll** (`import.process-form`) và **`ImportController@getImportProgress`**, nên **cùng chịu ảnh hưởng** khi bật worker trong request poll (mục 3).

### C. ~~Sales History stream (đa sheet)~~ — đã loại bỏ

- Trước đây: job **`ImportSalesHistoryStreamJob`** (metadata nhiều sheet + range dòng). **Không còn** trong codebase; tham chiếu lịch sử git nếu cần.
- Hiện tại: **mục A** (chunk một sheet).

---

## 1bis. Ưu / nhược điểm từng cơ chế

### A. `importJobProcessChunked`

| Ưu điểm                                                                                  | Nhược điểm                                                                                                                                                                                                                                                                                                                                             |
| ---------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Ít job hơn** so với 1 dòng/job → queue gọn, overhead nhỏ hơn.                          | Nếu **không** đọc được theo range (fallback `Excel::import` + `getProcessedData()`), một request có thể **nạp cả workbook** — file **rất lớn** dễ **OOM** / chạm `memory_limit`. Với **Client / Product / Inventory / Sales History / Warehouse**, bước dispatch thường ưu tiên **`loadFirstSheetDataRowsByRowRange`** (sheet đầu) trước khi fallback. |
| Mỗi job xử lý **khối dòng** → ít overhead queue; bulk mạnh nhất ở Client (role/CF bulk). | Cần class `*Import` + `*ChunkJob` + (tuỳ module) service xử lý dòng; chunk quá lớn → một job nặng, quá nhỏ → quá nhiều job.                                                                                                                                                                                                                            |
| Tiến độ poll nhìn “mượt” hơn khi số job vừa phải.                                        | Với **Excel nhiều sheet**: phụ thuộc **cách class Import đọc file** — nếu chỉ map/đọc sheet đầu, các sheet sau có thể **không vào** luồng (tùy code từng module).                                                                                                                                                                                      |

### B. `importJobProcess` (1 job = 1 dòng)

| Ưu điểm                                            | Nhược điểm                                                                                                                          |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| Logic **theo dòng** đơn giản, dễ map lỗi “dòng N”. | **Số job = số dòng** → file lớn tạo **hàng nghìn job** → DB `jobs` phình, worker lâu, poll dễ “nặng” nếu chạy worker trong request. |
| Phù hợp file **nhỏ / vừa**.                        | Cùng vấn đề **parse full file một lần** trước khi tạo batch → RAM cao với workbook lớn.                                             |
|                                                    | Với **nhiều sheet**: vẫn phụ thuộc Import class gom sheet thế nào; thường **không tối ưu** bằng stream từng sheet.                  |

---

## 1ter. So sánh khi upload **Excel nhiều sheet** — cơ chế nào “tốt” hơn?

**Không có một đáp án tuyệt đối** — phụ thuộc **nghiệp vụ** và **cách class Import đọc file**. Trong repo này có thể rút ra:

| Kịch bản                                                                              | Gợi ý trong thực tế codebase                                                                                                                                   |
| ------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sales History (MAOLIN)**                                                            | **Chỉ sheet đầu** được import; workbook nhiều sheet → user cần **gộp vào một sheet** hoặc chỉ đặt dữ liệu ở tab đầu. Luồng **chunked** giống Client/Product.   |
| **Sales Order** (vẫn `WithMultipleSheets` trong class Import)                         | Khác Sales History — xem code `SalesOrderImport` / controller; chưa thống nhất “một sheet” với Sales History.                                                  |
| **Nhiều sheet nhưng nghiệp vụ chỉ cần một sheet (hoặc Import class chỉ đọc sheet 1)** | **`importJobProcessChunked`** + **`loadFirstSheetDataRowsByRowRange`**: các sheet sau **không** được đọc.                                                      |
| **Một sheet nhưng cực nhiều dòng (CSV/Excel một trang)**                              | **Chunked** thường **hợp lý hơn** 1 dòng/job: giảm số job, dễ tối ưu bulk.                                                                                     |
| **File vừa, cần debug từng dòng**                                                     | Có thể tạm **giảm `chunk_size`** (vd. 1–20) trên luồng chunked để lỗi gần với “từng dòng”; hoặc dùng module vẫn là **`importJobProcess`** (Deal, Employee, …). |

**Tóm lại ngắn:**

- **Sales History:** không còn import đa sheet; **một sheet (đầu tiên)** + chunked.
- **Excel nhiều sheet** với các module chỉ đọc sheet 1: dữ liệu trên sheet khác sẽ **không** vào import — cần **tài liệu hóa** cho user.
- **Chỉ so “tốt hơn” theo số sheet** là không đủ: phải kèm **RAM**, **số job**, và **yêu cầu nghiệp vụ**.

---

## 1quater. Custom field (CF) — ảnh hưởng và phương pháp nào phù hợp khi **nhiều CF**

### CF làm thay đổi gì (trong hệ thống)

- **Bước map cột (light map):** Với **Client**, **Product**, **Inventory** (Purchase), bước upload map không gọi `mergeDynamicColumns` sớm trên toàn bộ class khác; trong nhánh light read, trait gọi lần lượt:
    - `ClientImport::mergeDynamicColumns(ClientImport::fields())`
    - `ProductImport::mergeDynamicColumns(ProductImport::fields())`
    - `InventoryImport::mergeDynamicColumns(InventoryImport::fields())`
- **ID cột map với CF (quan trọng khi đọc code):**
    - **Client:** `id` map = **slug** `custom_field.name` (giống label logic trong `ClientImport`).
    - **Product:** `id` map = **slug** `custom_field.name`; job `ImportProductChunkJob::buildProductCustomFieldsData` chỉ ghi các cột đã map, khớp `name` với cột file.
    - **Inventory (Purchase):** `id` map = **`field_{custom_field_id}`** (tránh trùng cột lõi); `InventoryImportRowProcessor` đọc `field_*` và label fallback cho một số cột hệ thống.
- **Payload mỗi dòng / mỗi job:** Mỗi dòng không chỉ cột cố định mà còn **giá trị CF** → **bộ nhớ** và **kích thước serialize job** tăng.
- **Xử lý trong job:** Ví dụ `ImportClientChunkJob` **load một lần** map CF (`ClientImportProcessor::getClientCustomFieldMap`) **cho cả chunk**, rồi bulk insert CF — tránh lặp query CF **từng dòng** trong cùng job. Product/Inventory: CF xử lý trong từng dòng (chunk vẫn giảm **số job** so với 1 dòng/job).

### So sánh nhanh theo **cơ chế** khi nghiệp vụ có **nhiều CF**

| Cơ chế                                | Khi **nhiều CF**                                                                                                                                                                               | Ghi chú                                                                                                             |
| ------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **`importJobProcessChunked`**         | **Thường phù hợp nhất** trong các pattern đang có: một job xử lý **nhiều dòng** → amortize chi phí load map CF / lookup role / bulk insert một lần; dễ tối ưu bulk ghi CF theo lô trong chunk. | Cần giữ **chunk size** hợp lý: chunk quá lớn + mỗi dòng rất nhiều CF → một job vẫn có thể nặng (OOM / timeout job). |
| **`importJobProcess` (1 dòng / job)** | **Kém hơn** khi file lớn: số job ≈ số dòng × **chi phí cố định** (resolve CF, permission, …) lặp lại; queue và DB `jobs` phình.                                                                | Chỉ chấp nhận được khi **dữ liệu nhỏ** hoặc tạm thời; không lý tưởng cho “nhiều CF + nhiều dòng”.                   |
| **Sales History (chunked)**           | **Cột nghiệp vụ cố định** (MAOLIN); **không** merge CF động kiểu Client. Cùng pattern **chunk** + bulk DB như các module A.                                                                    | Nếu sau này cần CF động: thiết kế tương tự Product/Inventory (merge cột + xử lý trong chunk job).                   |

### Khuyến nghị thực tế

1. **Module có nhiều CF + import số lượng lớn** → ưu tiên **`importJobProcessChunked`** (hoặc tương đương: nhiều dòng/job), **preload** map CF / lookup **một lần mỗi chunk** (như Client).
2. **Giảm `chunk_size`** nếu một dòng đã **rất nặng** (nhiều CF + logic phức tạp) để tránh một job chạy quá lâu.
3. **Tránh** 1 job = 1 dòng khi **vừa nhiều dòng vừa nhiều CF**, trừ file nhỏ.
4. **Bước upload** vẫn parse cả file trước khi dispatch — CF **không** giảm RAM lúc parse; cần **`memory_limit`** đủ và file không quá khổ (hoặc tách file).

---

## 2. Vì sao một số import “ổn” poll còn một số dễ timeout?

- Khi bật **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`**, **mỗi lần GET poll** có thể chạy `queue:work` **trong cùng request HTTP** (xử lý tối đa vài job, có **`--max-time`** để không vượt timeout).
- **Chunked** với **file rất lớn** → **nhiều job** (theo số chunk) → mỗi vòng worker có thể **lâu** → dễ chạm **timeout nginx/php-fpm** nếu không giới hạn thời gian worker trong poll.
- **Trước khi bật** worker trong poll: poll **chỉ đọc** DB → request **nhẹ**; tiến độ nhích nhờ **cron** `schedule:run` / worker nền — cảm giác “ổn” nếu cron chạy đều.
- **Sales History** giờ **cùng chunked**; file nhỏ / ít chunk → poll vẫn thường nhẹ hơn file cực lớn.

Tóm lại: khác biệt chính là **số job và thời gian xử lý mỗi lần poll**, và **kích thước file** (bước dispatch đọc **sheet đầu** vào RAM trước khi tạo batch).

---

## 3. Giải pháp (đã có trong code + vận hành)

| Mục                                      | Nội dung                                                                                                                                                                                                                                                                                                                                                                                                                          |
| ---------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Giới hạn thời gian worker trong poll** | `ImportController`: `queue:work` thêm `--max-time` (config `IMPORT_PROGRESS_WORKER_MAX_SECONDS`, mặc định **25** trong `config/app.php`). Tránh request poll bị cắt giữa chừng.                                                                                                                                                                                                                                                   |
| **Giới hạn số job mỗi poll**             | `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` (mặc định 8).                                                                                                                                                                                                                                                                                                                                                                           |
| **Không chạy worker trong poll**         | `IMPORT_PROGRESS_RUN_QUEUE_WORKER=false` — poll chỉ đọc trạng thái; **bắt buộc** cron + `queue:work` trong `app/Console/Kernel.php` có đủ queue (`ClientImport`, `ProductImport`, `InventoryImport`, `SalesHistoryImport`, …).                                                                                                                                                                                                    |
| **Cron đúng user**                       | `schedule:run` và **`queue:work`** nên **cùng user với PHP-FPM** (vd. `www-data`). Import Client (chunk) ghi **file cache** (`import_metrics_*` qua `StoresImportBatchMetrics`) — nếu worker khác user FPM hoặc `cache:clear` chạy bằng **root**, dễ _Permission denied_ dưới `storage/framework/cache/data/`. Không cần chmod từng file mới nếu đã `chown` + **default ACL** đúng — xem `docs/SERVER_RUNBOOK_VI.md` (mục **4**). |
| **Timeout reverse proxy**                | Nếu vẫn bật worker trong poll, cân nhắc tăng `fastcgi_read_timeout` (nginx) cho route poll hoặc giảm `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` / `IMPORT_PROGRESS_WORKER_MAX_SECONDS`.                                                                                                                                                                                                                                            |

---

## 4. Liên quan tài liệu khác

- Sales History vận hành: mục **§7** file này (trước: `09_ORDER_HISTORY_IMPROVE_PLAN.md`)
- PHP-FPM, quyền thư mục: `docs/SERVER_RUNBOOK_VI.md`
- Mapping cột Client (nghiệp vụ): `FUNC_IMPORT/IMPORT_SPECS_VI.md` § 2. Client

---

## 5. Bảng tra nhanh (theo dõi khi sửa code)

| Thành phần                              | Vai trò                                                                                                                                                          |
| --------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `App\Traits\ImportExcel`                | `importFileProcess`, `importJobProcess`, `importJobProcessChunked` (+ `allowBatchFailures`), light map, `loadFirstSheetDataRowsByRowRange`, `normalizeExcelRows` |
| `App\Http\Controllers\ImportController` | `getImportProgress`, `getQueueException`, whitelist queue, `batchRecordNameMatchesQueue` (chấp nhận tên batch `QueueName` hoặc `QueueName-chunked`)              |
| `config/app.php`                        | `import_progress_*` (worker trong poll, max job, max time)                                                                                                       |

**Queue được phép poll / exception (whitelist):** xem `ImportController::ALLOWED_IMPORT_QUEUE_NAMES` — gồm `ClientImport`, `ProductImport`, `InventoryImport`, `SalesOrderImport`, `WarehouseImport`, `SalesHistoryImport`, …

---

## 6. Tóm tắt một dòng

**Client + Product + Inventory (Purchase) + Sales Order + Warehouse + Sales History** dùng **`importJobProcessChunked`** (batch `*-chunked`, queue = short name class import). **Sales History** thêm **`allowBatchFailures` = true** trên batch, **`sales_history_id`** trong `options`, và **chỉ sheet đầu**; **`ImportSalesHistoryChunkJob`** ghi lỗi từng dòng vào **`import_row_errors_*`**, cộng **`invalid_status`**, **không throw** để **`pending_jobs`** giảm đúng; luồng stream đa sheet (**`ImportSalesHistoryStreamJob`**) đã **gỡ**.
Các module khác (Employee, Deal, Lead, …) vẫn dùng **`importJobProcess`** (1 job / dòng) khi chưa migrate.
**Nhiều CF:** map động qua `mergeDynamicColumns` (Client / Product / Inventory); Product dùng slug `name`, Inventory map `field_{id}` (mục **1quater**). Sales History **không** dùng CF động kiểu đó.
**Poll UI** dùng chung; rủi ro timeout khi **bật worker trong poll** + **file lớn / job nặng** — xử lý bằng **`--max-time`**, giảm job mỗi poll, hoặc tắt worker trong poll và dựa **cron/worker nền** (`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false` + Supervisor trên staging).

---

## 7. Sales History (Order History) — vận hành

**Spec map cột:** [`IMPORT_SPECS_VI.md`](./IMPORT_SPECS_VI.md) · **Chunk/bulk pattern:** [`../FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`](../FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md) · **Lịch sử plan dài:** `git log -- FUNC_IMPROVE/09_ORDER_HISTORY_IMPROVE_PLAN.md`

### 7.1 Trạng thái (2026-04-30)

- Đã triển khai: chunk 500 dòng/job, prefetch client/SKU/hash, bulk insert, `import_row_errors_*` + badge/CSV, poll `--max-time`.
- **Backlog:** parse-once toàn workbook (tránh mở lại file mỗi job); PhpSpreadsheet disk cache — tùy `config/excel.php`.

### 7.2 Quy chuẩn lỗi theo dòng (áp dụng import khác)

| Thành phần | Quy ước                                                                             |
| ---------- | ----------------------------------------------------------------------------------- |
| Cache      | `import_row_errors_{batchId}`, TTL 12h                                              |
| Ghi        | `StoresImportBatchMetrics::mergeImportBatchRowErrors` — lock, tối đa 500 dòng/batch |
| Nội dung   | `Sheet {i} Row {n}: …` — không stack trace                                          |
| UI         | `ImportController@getQueueException` → badge + CSV                                  |

### 7.3 Return (INSERT dòng mới)

- **Yes:** `net sales volume < 0` hoặc `net sales amount < 0` (khi có amount).
- **No:** qty ≥ 0 và (không amount hoặc amount ≥ 0).

### 7.4 Code chính

| Hạng mục             | File                                                              |
| -------------------- | ----------------------------------------------------------------- |
| Dispatch chunk       | `SalesHistoryController.php`                                      |
| Chunk job            | `ImportSalesHistoryChunkJob.php`                                  |
| Rows/job config      | `config/craveva_import.php` / `SALES_HISTORY_IMPORT_ROWS_PER_JOB` |
| Poll / worker inline | `ImportController.php`, `config/app.php` `import_progress_*`      |

### 7.5 Staging: poll 0% (`pendingJobs: 1`)

**Nghĩa:** Job chưa được worker lấy — không phải lỗi map cột.

1. `QUEUE_CONNECTION=database`; migrate `jobs` / `job_batches`.
2. Worker **phải** có queue `SalesHistoryImport` (xem `app/Console/Kernel.php`).
3. Cron `schedule:run` nên chạy **cùng user PHP-FPM** (`www-data`) — tránh `Permission denied` trên `storage/framework/cache`.
4. Production: `IMPORT_PROGRESS_RUN_QUEUE_WORKER=false` + Supervisor; tạm staging có thể bật `true` (rủi ro timeout proxy).

| Triệu chứng                                          | Xử lý nhanh                                                                                                      |
| ---------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| % đứng 0%, poll 200                                  | Kiểm tra worker + queue name; `queue:failed`                                                                     |
| Client import poll timeout sau bật worker trong poll | Giảm `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL` / `IMPORT_PROGRESS_WORKER_MAX_SECONDS` hoặc tắt worker trong poll |
| Cache permission                                     | `chown www-data storage bootstrap/cache` — `docs/SERVER_RUNBOOK_VI.md`                                           |

**Local vs staging:** Local `.test` thường chạy worker trong poll → nhanh; staging domain thật cần daemon. DB staging phải có client_code/SKU khớp file.

---

## Phụ lục A — SO/PO Inventory (tracker staging)

## 1) Muc tieu

- Tach ro hai man hinh:
    - Inventory Balance: hien ton hien tai theo SKU x Warehouse (hoac SKU x Warehouse x Batch).
    - Inventory Transactions: hien lich su phieu (GRN, Adjust, SO Delivery, ...).
- Dam bao quy trinh:
    - PO -> GRN lam ton kho tang (+).
    - SO -> Delivery (shipped) lam ton kho giam (-).

## 2) Scope ky thuat

### Phase A - UX clarification (nhanh)

- [ ] Doi ten man hien tai thanh `Inventory Transactions`.
- [ ] Hien thi cot `Items count` (khong chi 1 SKU dai dien).
- [ ] Loai bo/doi ten cot de tranh hieu nham (`Available`, `Ending`) tren man Transactions.
- [ ] Chinh style label detail de de doc hon.
- [ ] An custom field rong (`--`) o detail (co option bat lai neu can).

### Phase B - Them man Inventory Balance

- [ ] Tao route + menu `Inventory Balance`.
- [ ] Query aggregate tu `warehouse_product_batches` theo:
    - `product_id`, `warehouse_id` (batch optional).
- [ ] Cot can co:
    - SKU, Product, Warehouse, On hand, Reserved, Available, Nearest expiry, Health.
- [ ] Them filter:
    - warehouse, product, expiry status, stock status.
- [ ] Them drill-down:
    - tu Balance sang Transactions theo SKU/kho.

### Phase C - Dong bo import

- [ ] Xac dinh import hien tai dang ghi vao Transactions.
- [ ] Sau import, refresh/aggregate balance.
- [ ] Khong doi API import neu khong can.
- [ ] Bo sung test regression cho import.

## 3) Data mapping (expected)

- Balance row key:
    - Default: `product_id + warehouse_id`
    - Neu bat batch-level: `product_id + warehouse_id + batch_id`
- Transactions:
    - 1 phieu co nhieu dong item.
- Quyet dinh quan trong:
    - Balance la derived data.
    - Transactions la source of truth.

## 4) Browser test plan (SO/PO -> Inventory + / -)

### Preconditions

- [ ] Da login staging.
- [ ] Co san it nhat 1 SKU track inventory.
- [ ] Co warehouse mac dinh.

### Test T1: PO -> GRN -> Inventory tang (+)

- [ ] Ghi nhan baseline Available cua SKU tai Inventory Balance.
- [ ] Tao/chon PO co SKU do.
- [ ] Tao GRN tu PO, save thanh cong.
- [ ] Verify Available tang dung theo qty nhap.

### Test T2: SO -> Delivery (shipped) -> Inventory giam (-)

- [ ] Ghi nhan baseline sau T1.
- [ ] Tao/chon SO co SKU do.
- [ ] Tao Delivery Order, ship qty > 0, save shipped.
- [ ] Verify Available giam dung theo qty ship.

### Test T3: End-to-end consistency

- [ ] Tong bien dong = (+ inbound) - (outbound).
- [ ] Transactions co du ban ghi GRN va SO Delivery.
- [ ] Khong co am ton bat thuong (neu business rule cam am ton).

## 5) Test execution log (staging)

| Time             | Test ID | Step                          | Result | Evidence URL                                                     | Note                                                                                                                                                                                                              |
| ---------------- | ------- | ----------------------------- | ------ | ---------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-04-24 14:14 | T1      | PO -> GRN -> Inventory +      | PASS   | /account/grn, /account/sales-do/13                               | GRN `002` chuyen `Received`; tao movement inbound id `7099`, qty `+10`, product `8448`, warehouse `78`.                                                                                                           |
| 2026-04-24 14:12 | T2      | SO -> Delivery -> Inventory - | PASS   | /account/orders/13, /account/sales-do/13                         | SO `ODR#004` tao DO `SS-000013`, confirm + ship (Delivered); tao movement outbound id `7098`, qty `-1`, product `8448`, warehouse `78`.                                                                           |
| 2026-04-24 14:14 | T3      | Reconcile + / -               | PASS   | DB `stock_movements`                                             | Cung product `8448` va warehouse `78`: net bien dong moi = `+10 - 1 = +9`, dung logic PO inbound / SO outbound.                                                                                                   |
| 2026-04-24 14:50 | T4      | PO(COM123) -> GRN -> Bill     | PASS   | /account/purchase-order/8, /account/grn, /account/bills          | PO `PO#002` co SKU `COM123` qty `2`; GRN `003` doi `Received`; stock movement inbound id `7102` qty `+2` product `8449` kho `78`; Bill `BL#002` tao thanh cong (khong tao them movement kho).                     |
| 2026-04-24 16:05 | T5      | Multi-warehouse WHA (COM123)  | PASS   | /account/purchase-order/10, /account/grn/5, /account/sales-do/15 | `PO#004` + `GRN 005` tai `WAREHOUSE A (79)` tao inbound movement `id=7105`, qty `+1`; `ODR#005` + `SS-000015` ship tai cung kho tao outbound movement `id=7106`, qty `-1`; net tai kho `79` = `0` dung nghiep vu. |

## 6) Acceptance criteria

- [ ] Cung SKU cung kho chi hien 1 dong tren Balance.
- [ ] Tao them phieu khong tao dong duplicate tren Balance.
- [ ] So lieu ton tren Balance khop voi bien dong Transactions.
- [ ] User khong con hieu nham giua ton hien tai va lich su phieu.

## 7) Trigger matrix (da xac minh)

| Flow                | Document | Status trigger    | Kho thay doi | Ghi chu                                                   |
| ------------------- | -------- | ----------------- | ------------ | --------------------------------------------------------- |
| PO -> GRN -> Bill   | GRN      | `received`        | `+` inbound  | Cong kho khi GRN chuyen `Received`.                       |
| PO -> GRN -> Bill   | Bill     | `open/draft/paid` | khong doi    | Bill la chung tu tai chinh, khong post stock movement.    |
| SO -> DO -> Invoice | Sales DO | `shipped`         | `-` outbound | Tru kho tai buoc Ship DO (status `shipped`).              |
| SO -> DO -> Invoice | Invoice  | `unpaid/paid`     | khong doi    | Invoice khong truc tiep tru/cong kho trong flow hien tai. |

## 8) Multi-warehouse demo note (staging)

- Luong demo da chay tren `WAREHOUSE A (id=79)` voi SKU `COM123`:
    - Inbound: `GRN 005` (`received`) -> `stock_movements.id=7105` (`inbound`, `warehouse_to_id=79`, qty `+1`).
    - Outbound: `SS-000015` (`shipped`) -> `stock_movements.id=7106` (`outbound`, `warehouse_from_id=79`, qty `-1`).
- Ket luan: movement duoc ghi dung kho nguon/dich, khong bi doi sang kho mac dinh (`78`) trong demo nay.
