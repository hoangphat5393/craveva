# Client Import — Master (gộp các lỗi thường gặp + hướng xử lý)

**Registry:** [`REGISTRY.md`](REGISTRY.md) (mã `IMPORT-CLIENT`)

## 1) Mục tiêu

- Gom các lỗi import client thường gặp (staging + data) vào 1 chỗ.
- Trỏ về “source of truth” trong code/flow để tránh đọc nhiều file rời.

**Source of truth (flow):**

- `FUNC_LOGIC/FLOW_ADD_CLIENT.md`
- `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`

---

## 2) Nhóm lỗi A — CSV delimiter/encoding (“The separation symbol could not be found”)

**Triệu chứng:** rất nhiều dòng fail với message: `The separation symbol could not be found`.

**Nguyên nhân gốc (thường gặp):**

- File thực chất là **CSV** nhưng delimiter/encoding không phù hợp, PhpSpreadsheet không detect được delimiter.
- File rỗng hoặc vài dòng đầu không có dấu phân cách.

**Cách xử lý vận hành:**

- Ưu tiên dùng **.xlsx** (sample form thường có `client-sample.xlsx`).
- Nếu bắt buộc CSV: lưu **CSV UTF-8**, delimiter `,` hoặc `;`.

**Chi tiết:** `FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md` (Phần A).

---

## 3) Nhóm lỗi B — Dữ liệu thiếu “name” (SQLSTATE 1048 Column 'name' cannot be null)

**Triệu chứng:** `SQLSTATE[23000]... Column 'name' cannot be null`

**Nguyên nhân gốc:**

- Map cột sai (field “name” map nhầm).
- Dòng dữ liệu thiếu cột/thiếu value (thường do CSV parse lỗi hoặc file thiếu dữ liệu).

**Hướng xử lý:**

- Chuẩn hóa/validate “name” trước khi lưu (để trả lỗi rõ ràng cho người dùng).
- Kiểm tra mapping & data trước khi import.

**Chi tiết:** `FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md` (Phần A).

---

## 4) Nhóm lỗi C — “File does not exist …/public/user-uploads/temp/… (staging)”

**Triệu chứng:** upload/import báo không tìm thấy file ở đường dẫn `public/user-uploads/temp/<file>.xlsx`.

**Nguyên nhân gốc (staging):**

- Thư mục `public/user-uploads/temp` không tồn tại hoặc không writable cho user chạy PHP-FPM/worker.
- Deploy đổi symlink release làm `public_path()` trỏ sang release khác giữa các bước.

**Hướng xử lý vận hành:**

- Đảm bảo thư mục tồn tại và quyền đúng cho `www-data`:
    - `public/user-uploads/temp`
    - `public/user-uploads/import-files`

**Chi tiết:** `FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md` (Phần B)  
**Runbook quyền/worker:** `docs/SERVER_RUNBOOK_VI.md`

---

## 5) Gợi ý “canonical” để tránh trùng lặp tài liệu

- Lỗi / nguyên nhân / fix cụ thể: **`FUNC_BUG/CLIENT_IMPORT_DETAILS_VI.md`**.
- Flow thực tế + vị trí code: `FUNC_LOGIC/FLOW_ADD_CLIENT.md`.
- Checklist vận hành dài: đặt trong `docs/` (runbook), không nhân bản trong `FUNC_BUG/`.
