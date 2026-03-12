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
