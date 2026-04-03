1. Dòng dữ liệu được tạo mới (INSERT)


Return:
Yes: Số lượng (net sales volume) < 0 hoặc số tiền (net sales amount) < 0 (khi có nhập amount)
No: Cả qty ≥ 0 và (không có amount hoặc amount ≥ 0)


# Kế hoạch / trạng thái cải thiện Import Sales History (Order History)


## Quy chuẩn UX lỗi import (Sales History — dùng lại cho import khác)

**Mục tiêu:** Người dùng cuối **không** đọc stack trace PHP; chỉ thấy **một dòng rõ ràng / một badge** cho mỗi dòng file lỗi.

| Thành phần | Quy ước |
|------------|---------|
| **Cache key** | `import_row_errors_{batchId}` — mảng chuỗi, TTL **12h** (cùng nhịp với `import_metrics_{batchId}`). |
| **Khởi tạo** | Khi `dispatch` batch: `Cache::put('import_row_errors_'.$batchId, [], …)` (tránh null). |
| **Ghi (parallel-safe)** | `StoresImportBatchMetrics::mergeImportBatchRowErrors($batchId, $lines)` — lock `lock_import_row_errors_{batchId}`, tối đa **500** dòng/batch (tránh phình cache). |
| **Nội dung mỗi phần tử** | Một dòng, ví dụ: `Sheet {index} Row {n}: Product not found by SKU: {sku}` — **không** nhét stack trace. |
| **Job khi có lỗi dòng** | Gọi `mergeImportBatchRowErrors` **trước** `throw`; exception message **ngắn** (dịch `messages.*ImportJobFailedShort`) để `failed_jobs` gọn. |
| **UI** | `ImportController@getQueueException`: đọc cache, truyền `importRowErrors` vào `import.import_exception`; hiển thị **badge** + nút **CSV**; nếu có `importRowErrors` và queue là Sales History thì **rút gọn** cột exception (chỉ dòng đầu). |
| **API poll** | Có thể trả thêm `import_row_errors` trong JSON (đã thêm cho tương thích client JS). |

**Áp dụng cho import khác:** Copy pattern cache + merge + view (hoặc nhánh theo `$name` trong `getQueueException`), giữ **một chuỗi một lỗi** để badge/CSV ổn định.

---

## Nguyên tắc UX

- **Người dùng cuối (khách hàng)** chỉ thao tác trên trình duyệt: upload → map cột → giữ trang để thanh tiến độ cập nhật. **Không** được giả định họ biết chạy `php artisan` hay SSH.
- **Worker queue** (nếu cần chạy nền ổn định) là trách nhiệm **môi trường triển khai** (hosting, IT nội bộ, DevOps): Supervisor, systemd, hoặc panel quản lý process — **không** ghi vào hướng dẫn cho user nghiệp vụ.
- Trên môi trường dev (`.test` / `localhost` / cấu hình tương đương), ứng dụng có thể tự gọi `queue:work` trong luồng poll progress (xem `ImportController@getImportProgress` và `config/app.php` — `import_progress_*`). Đó là chi tiết kỹ thuật, không phải việc user phải làm.

---

## Đề xuất cải thiện thêm (ưu tiên từ hiệu quả)

1) **Bỏ parse nặng trong request upload**, chuyển dần 100% phần đọc file lớn sang background sau khi đã có `batch_id` (nếu sau này đổi UX được).

2) **Không đọc cùng một workbook nhiều lần không cần thiết**
   Luồng hiện tại: bước map nhẹ (sheet đầu); bước process vẫn **mở lại file theo từng job** (theo range dòng). Hướng tốt hơn lâu dài: một lần đọc/chia chunk trong worker (giống tinh thần `importJobProcessChunked` của Client import) — còn trong backlog.

3) **Không gom toàn bộ rows vào một mảng RAM**
   Với Sales History đang dùng stream theo range + job nhỏ hơn; vẫn cần cẩn trọng file xlsx cực lớn.

4) **Tối ưu DB** — **đã làm một phần** trong `ImportSalesHistoryStreamJob`: prefetch `client_code` / `SKU` / `source_row_hash` theo chunk, bulk insert theo lô (xem code). Có thể mở rộng cache xuyên job nếu cần.

5) **PhpSpreadsheet cache disk** (tùy `config/excel.php`) — backlog, giảm RAM workbook lớn.

6) **Gợi ý nghiệp vụ (cho user)**
   File rất lớn / nhiều sheet: có thể tách theo tháng hoặc giảm dung lượng (CSV tháng) để **ổn định hơn** — đây là khuyến nghị dùng phần mềm bảng tính, **không** phải lệnh server.

---

## Đã thực hiện (cập nhật theo code)

- [x] Giữ màn mapping cột; bước upload/map **chỉ đọc sheet đầu** cho `SalesHistoryImport` (nhẹ).
- [x] Bước mapping: header + sample giới hạn (`ChunkReadFilter`, tối đa 100 dòng quét, tối đa 5 dòng mẫu hiển thị).
- [x] Process: **nhiều job queue** theo sheet + khoảng dòng; job `ImportSalesHistoryStreamJob`.
- [x] **Chunk mặc định 500 dòng/job** (trước đây 2000 — quá nặng mỗi job). Cấu hình: `config/craveva_import.php` / env `SALES_HISTORY_IMPORT_ROWS_PER_JOB` (clamp 100–2000 trong controller).
- [x] Trong mỗi job: **prefetch** `client_details` + `products` bằng `whereIn`, prefetch `source_row_hash` đã tồn tại; **insert theo lô** (fallback từng dòng nếu lô lỗi); timeout job tăng phù hợp.
- [x] Poll import: giảm nguy cơ request poll bị treo lâu — điều chỉnh số job xử lý mỗi lần poll trong `ImportController` + `import_progress_execution_jobs_per_poll` (`config/app.php`).
- [x] **Lỗi theo dòng (UX):** gom vào `import_row_errors_{batchId}`; job ném exception **ngắn**; Import Log hiện **badge** + tải CSV; không hiển thị stack trace dài cho user (xem mục quy chuẩn UX lỗi import).
- [x] Loại bỏ key nội bộ `__sheet` khỏi sample để tránh lỗi Blade/import form.
- [x] **CSV ở bước map:** Reader CSV của PhpSpreadsheet **không có** `listWorksheetNames()` (chỉ Xlsx/Xls có) → trước đây `$sheetNames === []` → `abort` “no valid data”. Đã thêm `listWorksheetNamesForReader()` dùng `listWorksheetInfo` hoặc fallback `Worksheet` (`ImportExcel`).
- [ ] Chưa làm full mục (1)+(2) “một lần parse toàn luồng” vì vẫn giữ UX map cột và kiến trúc stream hiện tại.

**Số liệu tham chiếu (SalesHistoryImport, bước map cột):**

| Hạng mục                          | Giá trị               | Ghi chú                                |
|-----------------------------------|-----------------------|----------------------------------------|
| Sheet nạp nội dung ở bước map     | 1                     | Sheet đầu.                             |
| Sample quét                       | tối đa 100 dòng       | `readFirstSheetSampleRows`.            |
| Sample hiển thị UI                | tối đa 5 dòng         |                                         |
| Dòng mỗi job process              | **500** (mặc định)    | `SalesHistoryController` + `config/craveva_import.php`. |


**Vị trí code chính:**

| Hạng mục                              | File                                                              |
|---------------------------------------|---------------------------------------------------------------------------------------|
| Chunk rows/job + dispatch batch       | `app/Http/Controllers/SalesHistoryController.php`                                     |
| Đọc nhẹ sheet đầu / sample            | `app/Traits/ImportExcel.php`                                                          |
| Xử lý chunk + batch DB + bulk insert  | `app/Jobs/ImportSalesHistoryStreamJob.php`                                            |
| Cấu hình số dòng/job                  | `config/craveva_import.php`                                                           |
| Poll progress / worker inline (chỉ theo cấu hình môi trường) | `app/Http/Controllers/ImportController.php`, `config/app.php`  |
| Merge row errors (cache)              | `app/Traits/StoresImportBatchMetrics.php` (`mergeImportBatchRowErrors`)               |
| Import log UI (badge / CSV)           | `resources/views/import/import_exception.blade.php`                                   |
| Page length datatable                 | `app/DataTables/SalesHistoryDataTable.php` (`pageLength` 50)                          |


---

## Ghi chú quyết định

- Mục tiêu đạt được: **map nhanh**, **process chia job nhỏ hơn**, **ít query DB hơn trong từng job**, **poll phản hồi ổn định hơn** — phù hợp user chỉ dùng UI.
- Việc có cần **process queue nền 24/7** (Supervisor, v.v.) là **cấu hình server** khi triển khai production/staging; không đưa vào hướng dẫn cho khách hàng nghiệp vụ.

---

## Staging / production: import đứng 0% (`pendingJobs: 1`, `processedJobs: 0`)

**Ý nghĩa JSON poll:** Job vẫn nằm trong bảng `jobs` — **chưa có worker** (hoặc worker không lắng nghe đúng queue) xử lý. Đây **không** phải lỗi PHP memory trước khi job chạy; memory chỉ liên quan khi job **đã** bắt đầu và bị OOM.

**Trên staging với `APP_URL` public (vd. `staging.craveva.com`):** mặc định **không** chạy `queue:work` trong từng request poll (xem `shouldRunQueueWorkerDuringImportProgressPoll()` trong `ImportController`, `config/app.php`). Cần **daemon** xử lý queue.

**Checklist trên server:**

1. **`.env`:** `QUEUE_CONNECTION=database` (hoặc driver đã cấu hình đúng); migrate đã chạy (`jobs`, `failed_jobs`, `job_batches`).
2. **Worker lắng nghe queue import:** batch Sales history dùng queue tên **`SalesHistoryImport`**. Ví dụ Supervisor:
   - `php artisan queue:work database --queue=SalesHistoryImport --sleep=3 --tries=1 --timeout=3600`
   - Hoặc đưa `SalesHistoryImport` vào đầu danh sách `--queue=SalesHistoryImport,default,...`.
3. **Sau deploy:** `php artisan queue:restart` để worker nạp code mới.
4. **Nếu vẫn muốn thử chạy worker qua poll (không khuyến nghị production):** đặt `IMPORT_PROGRESS_RUN_QUEUE_WORKER=true` — chỉ dùng khi hiểu rủi ro timeout proxy/FPM.

**PHP memory (khi job đã chạy nhưng fail / kill):** tăng `memory_limit` cho **CLI** (worker) và nếu cần **php-fpm** (vd. 512M–1024M tùy file). Kiểm tra `php artisan queue:failed` và log worker.

---

## Tại sao **local** ít lỗi, **staging** dễ lỗi / cảm giác “chờ mãi”?

| | **Local (máy dev)** | **Staging / production** |
|---|---------------------|---------------------------|
| **Worker khi mở trang import** | Thường **có** `queue:work` trong từng request poll (khi `APP_URL` dạng `.test` / `localhost` hoặc env cho phép) → job chạy ngay, % nhích nhanh. | **Không** chạy worker trong poll (domain thật) → phải chờ **cron** `schedule:run` (mỗi phút) hoặc Supervisor. |
| **Dữ liệu** | DB local đã có **client_code**, **SKU** khớp file test. | DB staging **khác** — dễ **Client not found / Product not found** → job **FAIL** nhanh; cần xem `failed_jobs` / log từng dòng. |
| **File upload** | Cùng máy, path `public/user-uploads/...` thống nhất. | Phải đúng quyền `storage` / `user-uploads` (www-data); zip deploy sai có thể thiếu file. |

**Job được xử lý khi nào?** Khi có process **queue worker** lấy job từ bảng `jobs` (cron `queue:work ... --queue=...SalesHistoryImport...` hoặc worker nền). Không có worker = `pendingJobs` mãi = 1, % đứng 0% — **không phải lỗi logic import**, mà **cấu hình server**.

**UI:** Sau ~3 phút mà vẫn còn pending, trang import hiện thêm cảnh báo (có thể rời trang, job vẫn chạy nền) — xem `resources/views/import/process-form.blade.php` và `messages.importQueueTakingLong`.
