# Đề xuất: Lưu log import Client để kiểm tra kết quả sau này

**Mục tiêu:** Có cách **lưu trữ lâu dài** kết quả import client (thành công/thất bại, dòng lỗi, summary) để user hoặc admin có thể **xem lại, đối chiếu, audit** mà không phụ thuộc vào `failed_jobs` hoặc session hiện tại.

**Phạm vi:** Chỉ đề xuất phương án, không triển khai code.

**Tham chiếu:** CLIENT_IMPORT_LOG_UX_PROPOSAL.md (log hiển thị ngay sau import đã có); FLOW_ADD_CLIENT.md.

---

## 1. Hiện trạng

- Sau khi import xong: log hiển thị trên màn hình (summary + bảng dòng lỗi) từ **`job_batches`** + **`failed_jobs`** (parse exception message).
- **Hạn chế:**
    - `failed_jobs` thường bị xóa hoặc rotate (queue:flush, retry, cleanup).
    - Không có "lịch sử import" theo thời gian; user đóng trang là mất chi tiết.
    - Không tra cứu được "lần import ngày X có bao nhiêu dòng lỗi, nội dung gì" nếu không lưu riêng.

---

## 2. Các phương án lưu log (đề xuất)

### Phương án A: Bảng `import_logs` (dedicated) – lưu theo batch

**Ý tưởng:** Mỗi lần import client (mỗi batch) tạo **một bản ghi** trong bảng `import_logs`. Lưu metadata + payload chi tiết (summary, danh sách dòng lỗi đã parse).

**Nội dung lưu (gợi ý):**

| Trường (gợi ý) | Kiểu / Ý nghĩa                                                                   |
| -------------- | -------------------------------------------------------------------------------- |
| `id`           | PK                                                                               |
| `company_id`   | Tenant                                                                           |
| `batch_id`     | UUID từ `job_batches.id` (để đối chiếu nếu cần)                                  |
| `type`         | enum: `client`, `product`, … (mở rộng cho import khác)                           |
| `user_id`      | Người thực hiện import                                                           |
| `file_name`    | Tên file gốc (nếu có)                                                            |
| `total_rows`   | Tổng số dòng xử lý (hoặc ước lượng từ total_jobs × chunk_size)                   |
| `succeeded`    | Số dòng thành công                                                               |
| `failed`       | Số dòng thất bại                                                                 |
| `payload`      | JSON: `failed_rows` (array of `{row, message}`), hoặc thêm raw_exception nếu cần |
| `started_at`   | Thời điểm bắt đầu (có thể lấy từ batch)                                          |
| `completed_at` | Thời điểm xong (khi batch finished)                                              |
| `created_at`   | Khi tạo bản ghi log                                                              |

**Ưu:**

- Log tồn tại độc lập với queue; xóa `failed_jobs` không mất lịch sử.
- Có thể làm trang "Lịch sử import" / "Import history" filter theo company, user, ngày, type.

**Nhược:**

- Cần **khi nào ghi:** khi batch **finished** (success hoặc có failed). Có thể: listener `BatchCompleted` / polling khi frontend báo "import xong" gọi API "save import log" với batch_id, backend parse lại từ batch + failed_jobs rồi insert.
- Nếu ghi ngay khi dispatch batch thì chưa có `succeeded`/`failed` chi tiết → cần cập nhật khi batch hoàn tất.

---

### Phương án B: Ghi log khi batch hoàn tất (observer/listener)

**Ý tưởng:** Khi Laravel batch **finished** (event hoặc callback), tự động gọi service ghi một bản ghi vào `import_logs` (cấu trúc như phương án A).

**Cách triển khai gợi ý:**

- Đăng ký listener cho `Illuminate\Bus\Events\BatchFinished` (hoặc tương đương).
- Trong listener: kiểm tra batch `name` (vd. `ClientImport-chunked`) → nếu là client import thì lấy `batch_id`, đọc `job_batches` + `failed_jobs` (theo failed_job_ids), parse exception → insert/update `import_logs`.
- **Lưu ý:** Batch finished chạy trong context queue/worker; cần có `company_id`, `user_id` – có thể lưu trong batch metadata khi dispatch (CustomBatch với payload) hoặc lấy từ job đầu tiên.

**Ưu:**

- Không cần frontend gọi thêm API "lưu log"; log luôn được ghi khi import xong.
- Nhất quán với trạng thái thực tế của batch.

**Nhược:**

- Cần đảm bảo batch dispatch时 truyền được company_id, user_id (vd. qua batch options/metadata).
- Phụ thuộc event Bus; cần test kỹ khi batch fail một phần.

---

### Phương án C: Frontend gọi API "Lưu log import" sau khi xong

**Ý tưởng:** Khi UI đã có kết quả (sau khi poll xong, có summary + failed_rows), frontend gọi **một API** kiểu `POST /import-logs` với payload: `batch_id`, `type: client`, `summary`, `failed_rows` (đã parse). Backend validate (batch thuộc company, đã finished) rồi insert vào `import_logs`.

**Ưu:**

- Đơn giản: backend chỉ cần 1 endpoint nhận payload và ghi DB.
- Có sẵn `failed_rows` từ getQueueException → không cần parse lại.

**Nhược:**

- Nếu user đóng trang trước khi gọi API thì lần import đó không có log.
- Cần quy ước chỉ gọi một lần (idempotent) để tránh trùng bản ghi (vd. unique batch_id + type).

---

### Phương án D: Chỉ mở rộng `job_batches` / bảng hiện có

**Ý tưởng:** Không tạo bảng mới; lưu thêm vào cột có sẵn (vd. `job_batches.options` hoặc cột JSON) các field: `total_rows`, `succeeded`, `failed`, `failed_rows` (JSON). Cập nhật khi batch finished (listener hoặc khi getQueueException lần đầu sau khi batch xong).

**Ưu:**

- Không thêm bảng; tận dụng dữ liệu batch sẵn có.

**Nhược:**

- `job_batches` có thể bị dọn dẹp (cleanup); không phải thiết kế cho lưu lịch sử lâu dài.
- Khó mở rộng cho "trang Lịch sử import" đẹp (filter, phân quyền) nếu chỉ dựa vào bảng queue.

---

### Phương án E: Ghi file log (storage) mỗi lần import ✅ Đã triển khai

**Ý tưởng:** Mỗi khi import client xong (qua listener hoặc API), ghi một file (vd. `storage/logs/imports/clients/{batch_id}.json` hoặc `.csv`) chứa summary + danh sách dòng lỗi. Có thể kèm tên file gốc, user, timestamp.

**Triển khai:** File `storage/app/import-logs/clients/{company_id}/{batch_id}.json` khi gọi getQueueException (ClientImport + batch_id). Trang Clients → Import log (sidebar + nút): danh sách + xem chi tiết (UX giống Webhook log: Request body, Copy, JSON).

**Ưu:**

- Không đụng DB; dễ backup, dễ đọc ngoài (JSON/CSV).
- Phù hợp môi trường ít dùng DB cho audit.

**Nhược:**

- Khó query theo company/user/ngày (phải quét file, đọc metadata).
- Cần quản lý retention (xóa file cũ) và quyền đọc (storage path).

---

## 3. So sánh nhanh

| Tiêu chí            | A (bảng import_logs) | B (listener)       | C (API từ FE) | D (job_batches) | E (file)   |
| ------------------- | -------------------- | ------------------ | ------------- | --------------- | ---------- |
| Lưu lâu dài         | ✅                   | ✅ (nếu ghi vào A) | ✅ (vào A)    | ⚠️ Tùy cleanup  | ✅         |
| Không phụ thuộc FE  | ❌ (cần trigger)     | ✅                 | ❌            | ✅              | ❌         |
| Trang "Lịch sử"     | ✅ Dễ                | ✅                 | ✅            | ⚠️ Khó          | ⚠️ Khó     |
| Độ phức tạp         | Trung bình           | Cao hơn            | Thấp          | Thấp            | Trung bình |
| Mở rộng loại import | ✅ (type)            | ✅                 | ✅            | ⚠️              | ✅         |

---

## 4. Đề xuất ưu tiên

1. **Chuẩn:** **Phương án A + B**
    - Bảng `import_logs` (A) làm nguồn chính cho "Lịch sử import" và tra cứu.
    - Listener khi batch finished (B) tự ghi vào `import_logs` để không phụ thuộc user có mở trang hay không.

2. **Đơn giản hơn (MVP):** **Phương án A + C**
    - Bảng `import_logs`; khi frontend báo "import xong" và gọi API với batch_id + summary + failed_rows, backend ghi một bản ghi. Dễ làm trước, sau có thể bổ sung listener (B) để ghi cả khi user đóng trang.

3. **Không thêm bảng (tạm thời):** **Phương án D**
    - Chỉ lưu thêm vào metadata của batch; phù hợp nếu không cần trang lịch sử đầy đủ và chấp nhận log có thể mất khi cleanup queue.

4. **Chỉ cần file backup:** **Phương án E**
    - Hợp nếu mục đích chủ yếu là "có bản lưu để kiểm tra" theo từng batch, không cần query/filter trong app.

---

## 5. Trang "Lịch sử import" (nếu dùng A/B/C)

- **Vị trí gợi ý:** Trong module Clients (vd. Clients → Import → "Lịch sử import") hoặc Settings/Admin.
- **Nội dung:** Bảng danh sách các lần import (company, user, ngày, file name, total/succeeded/failed); click vào một dòng → xem chi tiết (summary + bảng Row # / Error, nút Download CSV như hiện tại).
- **Quyền:** Chỉ user có quyền import client (hoặc admin) mới xem được; filter theo company_id.

---

## 6. Tóm tắt

- **Đề xuất lưu log:** Dùng bảng riêng `import_logs` (phương án A) để lưu theo batch, không phụ thuộc `failed_jobs`.
- **Cách ghi log:** Ưu tiên tự động khi batch finished (B), hoặc MVP bằng API do frontend gọi khi import xong (C).
- **Mở rộng:** Trang "Lịch sử import" filter theo company/user/ngày, xem chi tiết và tải CSV dòng lỗi; có thể áp dụng chung cho các loại import (client, product, …) qua cột `type`.

_Tài liệu chỉ đề xuất phương án; việc triển khai thực tế (migration, listener, API, UI) thực hiện riêng._
