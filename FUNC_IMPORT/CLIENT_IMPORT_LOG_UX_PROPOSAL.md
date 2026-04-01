# Đề xuất: Xem log sau khi import Client

**Mục tiêu:** Sau khi import client xong, user có thể xem log để biết **lý do lỗi** và **dòng nào** trong file bị lỗi.

---

## 1. Hiện trạng

- Khi có job fail: gọi `getQueueException(batchId)` → hiển thị bảng exception (view `import_exception.blade.php`).
- Nội dung: một cột duy nhất, hiển thị **nguyên văn** message từ `failed_jobs` (vd. nhiều dòng "Row 5: ... Row 10: ..." gộp chung).
- Khi **không** có lỗi: không có khu vực "log", chỉ thấy "Import completed" và nút Back.
- Nhược điểm: khó đọc (không tách từng dòng lỗi), không có số dòng rõ ràng; khi không lỗi thì không có chỗ "xem log" thống nhất.

---

## 2. Đề xuất thiết kế

### 2.1. Luồng sau khi import xong

1. **Luôn hiển thị một khối kết quả (summary card)** ngay khi import hoàn tất:
    - Dòng 1: "Import completed."
    - Dòng 2: "**X** rows imported successfully, **Y** failed (total **Z** rows)."
    - Nút/link: **"View import log"** (hoặc "Xem log import") mở/scroll tới phần log bên dưới.

2. **Phần "Import log"** (dưới summary, có thể thu gọn/mở rộng):
    - **Nếu có lỗi (Y > 0):**
        - Tiêu đề: "Import log – Failed rows"
        - Bảng 2 cột: **Row #** (số dòng trong file) | **Error** (lý do lỗi).
        - Dữ liệu: parse từ exception message (pattern "Row N: message") để tách từng dòng lỗi.
        - Tùy chọn: nút **"Download failed rows (CSV)"** tải về file CSV (Row #, Error) để xử lý ngoài.
    - **Nếu không lỗi (Y = 0):**
        - Tiêu đề: "Import log"
        - Nội dung: "All rows were imported successfully. No errors."

3. **Backend:** Giữ API hiện tại, bổ sung:
    - Parse nội dung exception (chuỗi "Row N: message" hoặc "Row N: message \n Row M: ...") thành mảng `[{row: N, message: "..."}, ...]`.
    - Trả về thêm cấu trúc `failed_rows` (và có thể `summary`: total, succeeded, failed) để view render bảng + summary thống nhất.

### 2.2. UX/UI

| Yếu tố            | Đề xuất                                                                                                                                                  |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Vị trí**        | Log nằm ngay dưới progress, trong cùng trang import (không chuyển trang).                                                                                |
| **Summary**       | Luôn hiển thị sau khi xong: số dòng thành công, thất bại, tổng; nổi bật khi có lỗi (màu cảnh báo).                                                       |
| **Bảng lỗi**      | 2 cột: "Row #" (số dòng file, căn phải), "Error" (message, wrap text). Có thể giới hạn hiển thị 100 dòng đầu + "and X more" với link "Show all" nếu cần. |
| **Copy/Export**   | Nút "Copy" hoặc "Download CSV" cho danh sách dòng lỗi để user sửa file Excel và import lại.                                                              |
| **Khi không lỗi** | Vẫn có block "Import log" với nội dung "All rows imported successfully" để trải nghiệm nhất quán.                                                        |
| **Ngôn ngữ**      | Dùng key dịch sẵn (app.exceptions, messages...) và thêm key mới nếu cần (vd. "View import log", "Row #", "Download failed rows").                        |

### 2.3. Parse exception message

- Format từ `ImportClientChunkJob::fail()`: `"Row 5: Duplicate email.\nRow 10: Invalid date.\n… and 3 more"`.
- Quy tắc parse: tách theo `\n`, mỗi dòng match pattern `Row (\d+): (.+)` → row = $1, message = $2. Dòng "… and X more" có thể bỏ qua hoặc ghi một dòng tổng hợp.
- Nếu không match được (exception từ phiên bản cũ hoặc job khác): fallback hiển thị nguyên văn như hiện tại.

---

## 3. Các bước triển khai (đã làm)

1. **Backend (ImportController):** ✅ Parse exception message (pattern `Row N: message`) → `failed_rows`; khi có `batch_id` lấy `job_batches` → `summary` (total_jobs, failed_jobs, processed_jobs); trả về trong response `view`, `failed_rows`, `summary`.
2. **View (import_exception):** ✅ Nhận `failed_rows` và `summary`; render tiêu đề "Import log", summary card (alert), bảng Row # | Error khi có `failed_rows`; nút "Download failed rows (CSV)" (client-side); khi không lỗi hiển thị "All rows imported successfully"; fallback hiển thị raw exceptions khi không parse được.
3. **process-form (JS):** ✅ Khi import xong vẫn gọi getQueueException(batchId); hiển thị `#exceptionTable` mỗi khi có `response.view` (kể cả khi count = 0) để luôn có khối log sau khi xong.
4. **Lang (en + vi):** ✅ Thêm key `viewImportLog`, `importLog`, `importLogFailedRows`, `rowNumber`, `downloadFailedRowsCsv`, `allRowsImportedSuccessfully`, `importSummary`.

---

## 4. Mở rộng sau (tùy chọn)

- **Lưu log theo batch:** Lưu kết quả parse (failed_rows) vào bảng `import_logs` (batch_id, type, payload JSON) để sau này xem lại log theo lịch sử import mà không phụ thuộc `failed_jobs` (vì failed_jobs có thể bị xóa).
- **Filter theo batch:** Trang "Import history" liệt kê các lần import, click vào xem log chi tiết (nếu đã lưu log).

---

## 5. Hiệu năng: vì sao load lâu (trước map cột và sau khi Submit)

### 5.1. Giai đoạn 1 — Sau khi chọn file, bấm “Upload / Next” (trước màn map cột)

**Code:** `ClientController::importStore` → `ImportExcel::importFileProcess()` (`app/Traits/ImportExcel.php`).

| Nguyên nhân               | Giải thích ngắn                                                                                                                                                                                          |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Parse cả file một lần** | `Excel::import()` + `ClientImport` (`ToArray`) đọc **toàn bộ** CSV/XLSX vào RAM. File càng nhiều dòng/cột càng lâu.                                                                                      |
| **Heading (nếu bật)**     | Thêm **2 lần** `HeadingRowImport::toArray($filePath)` để lấy tiêu đề với hai kiểu `HeadingRowFormatter` (auto-match vs hiển thị) — mỗi lần **parse lại file** (không chỉ `array_shift` trên mảng đã có). |
| **Footer**                | Chỉ `array_pop` trên mảng đã load — **không** đọc file thêm.                                                                                                                                             |
| **Mạng + ghi đĩa**        | `Files::upload` upload file lên server.                                                                                                                                                                  |
| **Môi trường**            | Staging/VM ít CPU/RAM hơn máy local → cùng file thường chậm hơn.                                                                                                                                         |

**Hướng xử lý (thiết kế):**

- **Tối ưu bắt buộc:** Bước 1 chỉ cần **header + vài dòng mẫu** để map cột → có thể đổi sang đọc **giới hạn N dòng đầu** (hoặc stream CSV) thay vì load full sheet, rồi lưu file path/session và chỉ parse full ở bước Submit hoặc trong job.
- **Giảm đọc heading:** Dùng **một lần** import + lấy `$excelData[0]` làm heading, áp formatter trong PHP thay vì 2× `HeadingRowImport` (đã có nhận xét trong code review).
- **Vận hành:** Chia nhỏ file test; tăng `max_execution_time` / memory chỉ là chữa cháy, không thay thế tối ưu trên.

---

### 5.2. Giai đoạn 2 — Sau khi map cột, bấm Submit

**Code:** `ClientController::importProcess` → `ImportExcel::importJobProcessChunked()`; UI: `process-form.blade.php` gọi `postUrlEncoded`, `$.easyBlockUI` giữ màn hình đến khi response trả về.

| Nguyên nhân                         | Giải thích ngắn                                                                                                                                                                                                                                      |
| ----------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Parse file lần 2 (trùng bước 1)** | Trong `importJobProcessChunked` gọi lại **`Excel::import($importInstance, $filePath)`** — **đọc và parse lại toàn bộ file** để lấy `$excelData`, rồi mới `array_chunk` + dispatch batch. User phải chờ **hết** bước này trước khi thấy progress bar. |
| **`queue:clear`**                   | `Artisan::call('queue:clear database --queue=ClientImport')` — thêm thời gian (thường nhỏ hơn parse file lớn).                                                                                                                                       |
| **`normalizeExcelRows`**            | Duyệt **mọi dòng** để ép cell về scalar (tránh object PhpSpreadsheet trong job).                                                                                                                                                                     |
| **Dispatch batch**                  | Tạo N job + ghi `job_batches` / queue — file lớn → N lớn → ghi DB nhiều.                                                                                                                                                                             |
| **Xóa file sau submit**             | `Files::deleteFile` sau dispatch — không làm chậm chính parse.                                                                                                                                                                                       |

**Sau khi response:** UI gọi `getProgress` (polling). Lần poll đầu có thể chờ thêm (trong code client có `delay` / poll mỗi ~2s) — đó là **chờ worker xử lý queue**, khác với “load lâu” lúc bấm Submit (chủ yếu do **request đồng bộ** ở trên).

**Hướng xử lý (thiết kế):**

- **Tránh parse 2 lần:** Lưu kết quả parse bước 1 (serialized rows hoặc file tạm đã chuẩn hóa) keyed theo `import session id`, Submit chỉ đọc cache đó hoặc chỉ đọc file **một lần** nếu bắt buộc giữ file.
- **Async submit:** Trả về **202 + batch id** ngay sau khi validate + enqueue **một** job “prepare chunks” đọc file trong queue, thay vì parse full trong request HTTP (đổi kiến trúc lớn hơn).
- **Worker:** Đảm bảo `queue:work` hoặc cơ chế poll import chạy job — nếu không, progress bar “đứng” lâu (không phải cùng nguyên nhân với parse).

---

### 5.3. Giao diện màn map cột (cảm giác “load” / spinner)

- **Nhiều cột file** → Blade render **một thẻ `.importBox` cho mỗi cột** + vài dòng preview (`$importSample`) → DOM lớn, trình duyệt vẽ lâu.
- **`$.easyBlockUI` khi Submit** → overlay/spinner cho đến khi POST xong (trùng với thời gian parse lần 2 + dispatch).

**Hướng UX nhỏ:** Thu gọn preview (ít dòng hơn 5), virtualize danh sách cột nếu số cột rất lớn (hiếm với CSV client).

---

### 5.4. Tóm tắt một dòng

| Bước             | Nguyên nhân chính                                                             |
| ---------------- | ----------------------------------------------------------------------------- |
| Upload → màn map | Parse **full file** (+ 2× heading nếu bật tiêu đề).                           |
| Submit           | Parse **full file lần nữa** trong cùng request + chuẩn hóa + tạo batch queue. |

Tối ưu bền vững: **giảm số lần parse full file** (0–1 lần cho cả luồng) và/hoặc **không parse full trong request** khi file rất lớn.
