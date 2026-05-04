## Trạng thái rà soát (2026-04-30)

- Đã triển khai phần lớn tối ưu import Sales History (chunking, prefetch, bulk insert, row error UX).
- Còn backlog chính: parse-once end-to-end (không mở lại workbook nhiều lần).
- Kết luận: giữ file này để theo dõi phần tối ưu còn lại, chưa xóa.

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

4) **Tối ưu DB** — **đã làm** trong `ImportSalesHistoryChunkJob`: prefetch `client_code` / `SKU` / `source_row_hash` theo chunk, bulk insert theo lô (cùng ý tưởng stream cũ). Có thể mở rộng cache xuyên job nếu cần.

5) **PhpSpreadsheet cache disk** (tùy `config/excel.php`) — backlog, giảm RAM workbook lớn.

6) **Gợi ý nghiệp vụ (cho user)**
   File rất lớn / nhiều sheet: có thể tách theo tháng hoặc giảm dung lượng (CSV tháng) để **ổn định hơn** — đây là khuyến nghị dùng phần mềm bảng tính, **không** phải lệnh server.

---

## Đã thực hiện (cập nhật theo code)

- [x] Giữ màn mapping cột; bước upload/map **chỉ đọc sheet đầu** cho `SalesHistoryImport` (nhẹ).
- [x] Bước mapping: header + sample giới hạn (`ChunkReadFilter`, tối đa 100 dòng quét, tối đa 5 dòng mẫu hiển thị).
- [x] Process: **chunk** một sheet đầu qua `importJobProcessChunked`; job `ImportSalesHistoryChunkJob` (stream đa sheet đã gỡ).
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
| Xử lý chunk + batch DB + bulk insert  | `app/Jobs/ImportSalesHistoryChunkJob.php`                                            |
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

### Nguyên nhân gốc đã gặp trên staging (tóm tắt)

Import **không “chạy”** (0% lâu, `pendingJobs: 1`, poll vẫn 200) thường do **một hoặc kết hợp** các lý sau — **không phải** do trình duyệt không gọi API poll:

| Thứ tự | Hiện tượng | Nguyên nhân kỹ thuật |
|--------|------------|----------------------|
| 1 | Worker không bao giờ lấy job Sales History | `queue:work` phải có **`SalesHistoryImport`** trong `--queue=...`. Nếu Kernel chỉ liệt kê `default` hoặc thiếu tên queue này, job nằm trong bảng `jobs` nhưng **không được consume** → `%` đứng yên. |
| 2 | Sau khi sửa Kernel, vẫn cũ | Worker/cron cần **restart** (`queue:restart`, hoặc chờ cron khởi động lại process) để nạp cấu hình queue mới. |
| 3 | Job bị **FAIL** gần như ngay khi worker đã lấy | Log có thể ghi **`file_put_contents(... storage/framework/cache/data/...): Permission denied`** khi job gọi `Cache::put` (vd. `StoresImportBatchMetrics`). **PHP-FPM** (web) thường chạy **`www-data`**, còn **cron** chạy **`schedule:run`** dưới user SSH → worker/cache ghi file với user khác owner `storage` → **từ chối ghi** → job lỗi, UI vẫn giống “không tiến độ”. |

**Kết luận vận hành:** Cần **đúng danh sách queue trong worker** + **cron chạy worker/cache cùng “ý” user với web** (hoặc quyền thư mục thống nhất — xem mục dưới).

### Quyền thư mục nên trao để hệ thống ổn định (Laravel + queue + file cache)

Mục tiêu: **mọi** process ghi được chỗ Laravel cần: cache file, session (nếu file), log, view compiled, upload tạm — thường gom vào **`storage/`** và **`bootstrap/cache/`**.

| Thư mục / nhánh | Vai trò | Gợi ý quyền / owner |
|-----------------|---------|----------------------|
| **`storage/`** (toàn bộ) | `framework/cache`, `framework/sessions`, `logs`, `app` (file tạm/import nếu có) | Owner **`www-data:www-data`** (hoặc user chạy **cả** php-fpm **và** worker). Thư mục: **`2775`** hoặc **`775`** (setgid `2775` giúp file mới cùng group `www-data`). |
| **`bootstrap/cache/`** | config/routes/view cache | Cùng nguyên tắc với `storage`: ghi được bởi user chạy web + CLI worker. |
| **`public/user-uploads/`** (nếu dùng) | File user upload | Đảm bảo web server ghi được; thường cùng group `www-data`. |

**Nguyên tắc đồng bộ user (quan trọng trên staging/production):**

- **Cron** `* * * * * ... php artisan schedule:run` nên chạy job queue **cùng user với PHP-FPM** — ví dụ: `sudo -u www-data php artisan schedule:run` — để `queue:work` trong schedule không ghi cache/log dưới user khác `www-data`.
- Nếu deploy bằng user SSH: hoặc **`chown -R www-data:www-data storage bootstrap/cache`** sau deploy và chỉ sửa file qua quy trình chuẩn; hoặc thêm user deploy vào group **`www-data`** và đảm bảo **session mới** nhận group (sau `usermod -aG www-data` cần đăng nhập lại); tránh mix owner `root`/user khác trên `storage/framework/cache`.

**Lệnh tham chiếu (chạy trên server, điều chỉnh path):**

```bash
sudo chown -R www-data:www-data /path/to/app/storage /path/to/app/bootstrap/cache
sudo chmod -R ug+rwx /path/to/app/storage /path/to/app/bootstrap/cache
# tùy chọn setgid cho thư mục con (đã nêu 2775 ở trên)
```

Sau khi sửa quyền/cron, kiểm tra: `php artisan queue:failed`, log Laravel, và thử **một job** (`queue:work ... --once -v`) dưới **`www-data`** nếu vẫn nghi ngờ permission.

---

## Tại sao **local** ít lỗi, **staging** dễ lỗi / cảm giác “chờ mãi”?

| | **Local (máy dev)** | **Staging / production** |
|---|---------------------|---------------------------|
| **Worker khi mở trang import** | Thường **có** `queue:work` trong từng request poll (khi `APP_URL` dạng `.test` / `localhost` hoặc env cho phép) → job chạy ngay, % nhích nhanh. | **Không** chạy worker trong poll (domain thật) → phải chờ **cron** `schedule:run` (mỗi phút) hoặc Supervisor. |
| **Dữ liệu** | DB local đã có **client_code**, **SKU** khớp file test. | DB staging **khác** — dễ **Client not found / Product not found** → job **FAIL** nhanh; cần xem `failed_jobs` / log từng dòng. |
| **File upload** | Cùng máy, path `public/user-uploads/...` thống nhất. | Phải đúng quyền `storage` / `user-uploads` (www-data); zip deploy sai có thể thiếu file. |

**Job được xử lý khi nào?** Khi có process **queue worker** lấy job từ bảng `jobs` (cron `queue:work ... --queue=...SalesHistoryImport...` hoặc worker nền). Không có worker = `pendingJobs` mãi = 1, % đứng 0% — **không phải lỗi logic import**, mà **cấu hình server**.

**UI:** Sau ~3 phút mà vẫn còn pending, trang import hiện thêm cảnh báo (có thể rời trang, job vẫn chạy nền) — xem `resources/views/import/process-form.blade.php` và `messages.importQueueTakingLong`.

---

## Ghi chú: nguyên nhân thường gặp và cách xử lý (cập nhật theo vận hành staging)

| Triệu chứng (UI / poll) | Nguyên nhân | Cách xử lý |
|-------------------------|-------------|------------|
| **Thanh % đứng 0%, `0 / 1`, poll HTTP 200** nhưng `processedJobs` không tăng | Request poll **chỉ đọc** `job_batches` / queue; **job chưa được worker xử lý** (không có `queue:work` đúng queue, hoặc chưa tới lượt cron). | Đảm bảo `app/Console/Kernel.php` có `SalesHistoryImport` trong `queue:work`; cron `schedule:run` (ưu tiên chạy dưới `www-data`). Tạm thời staging: bật **`IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`** trong `.env` để mỗi lần poll có thể chạy vài job (giới hạn `IMPORT_PROGRESS_EXECUTION_JOBS_PER_POLL`); sau đó `php artisan config:clear` hoặc `config:cache`. |
| **Import Client (file lớn)** trước chạy ổn, sau khi bật worker trong poll lại “đứng” / poll lỗi | Cùng bật `IMPORT_PROGRESS_RUN_QUEUE_WORKER=true`: mỗi poll chạy `queue:work` **lâu** (nhiều chunk) → request vượt **timeout nginx/php-fpm** → JSON poll hỏng, UI không cập nhật (favicon “Import Client” vẫn “đang import”). Trước đó poll **chỉ đọc DB** nên luôn nhanh. | Trong code: `queue:work` trong poll có **`--max-time`** (config `IMPORT_PROGRESS_WORKER_MAX_SECONDS`, mặc định 25s). Hoặc tắt worker trong poll (`IMPORT_PROGRESS_RUN_QUEUE_WORKER=false`) và chỉ dựa **cron** + queue `ClientImport` trong `Kernel`. |
| **Cùng triệu chứng**, đã có cron | Worker/cron user **khác** user FPM → lỗi ghi **`storage/framework/cache`** khi job gọi `Cache::put` | `chown`/`chmod` `storage` + `bootstrap/cache`; cron `schedule:run` nên **`sudo -u www-data`**; user deploy trong group `www-data`. |
| **Chuỗi lỗi hiển thị nguyên khóa** `messages.importQueueTakingLong` (hoặc tương tự) | Thiếu bản dịch trong **`lang/{locale}/messages.php`** (vd. `zh-TW`, `zh-CN`) | Thêm key tương ứng trong repo, deploy, `view:clear` nếu cần. |
| Import nặng / spreadsheet lớn | **`memory_limit` PHP-FPM** thấp (vd. 128M) hoặc sai phiên bản pool | Site cần trùng **phiên bản** Nginx → FPM (vd. 8.3); đặt **`memory_limit`** đủ (vd. 1024M) trong đúng `php.ini` của pool đang dùng. |
| Extension thiếu (local có, staging không) | Package PHP chưa cài | `apt install php8.3-sqlite3` (ví dụ `sqlite3` / `pdo_sqlite`); `phpenmod` + reload FPM. |

Chi tiết quyền thư mục và PHP-FPM: `docs/SERVER_RUNBOOK_VI.md`.

Phân loại cơ chế import (Client / Product / Inventory / Sales History) và poll queue: `FUNC_IMPORT/IMPORT_MECHANISMS_POLL_AND_QUEUE_VI.md`.

---

## Git và staging: nguồn sự thật là repo (local / `main`)

- **Mã nguồn và tài liệu trong repo** (máy dev local, nhánh `main` trên GitHub/GitLab) là **bản chuẩn**: chỉnh sửa, commit, push từ đó.
- **Staging** chỉ **kéo code** (`git pull` / deploy script) để chạy thử — **không** coi staging là nơi chỉnh code rồi “đẩy ngược” lên repo. Mọi thay đổi bền vững phải **vào repo trước**, rồi staging **pull** bản mới.
- **Cấu hình chỉ trên server** (`.env`, `memory_limit`, cron, quyền `storage`) ghi lại trong tài liệu vận hành hoặc script deploy; tránh drift không theo dõi được.

Mục đích: **local ↔ git ↔ staging** luôn **đồng nhất** theo hướng **code từ repo → staging áp dụng**, không phải staging tự phát sinh thay đổi nguồn.
