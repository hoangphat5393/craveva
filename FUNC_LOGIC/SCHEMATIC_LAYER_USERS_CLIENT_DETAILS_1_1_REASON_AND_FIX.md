# Schematic layer báo `users` 1–many `client_details`: Nguyên do & Giải pháp

**Ngày:** 2026-03-15  
**Kiểm tra DB lại:** 2026-03-24 (local / môi trường dev đang dùng)  
**Ngữ cảnh:** Khi kết nối AI/schematic tool vào DB, tool suy ra quan hệ `users` → `client_details` là **1–many**, trong khi nghiệp vụ mong muốn **1–1** (mỗi client user có đúng một `client_details`).

---

## 1. Kết luận nhanh

- **Dữ liệu thực tế hiện tại:** `users` ↔ `client_details` đang là **1–1** (không có `user_id` trùng trong `client_details`).
- **Schema-level (database constraints):** đã có **`UNIQUE`** trên `client_details.user_id` (tên index: `client_details_user_id_unique`), migration `database/migrations/2026_03_15_000000_add_unique_user_id_to_client_details_table.php`. **FK** `client_details_user_id_foreign` (`user_id` → `users.id`) vẫn hiện diện — quan hệ 1–1 được thể hiện đầy đủ ở DB.
- **Application (Eloquent):** `User::clientDetails()` là `hasOne(ClientDetails::class, 'user_id')`; `ClientDetails::user()` là `belongsTo(User::class, 'user_id')` — khớp 1–1.

---

## 2. Nguyên do schematic layer suy ra 1–many

Phần này mô tả **lý do lịch sử** khi schema thiếu `UNIQUE` trên `user_id` hoặc khi tool chỉ đọc dump cũ. DB đã migrate (mục 1) thì constraint đủ để khẳng định 1–1 nếu tool introspect đúng.

### 2.1 Tool suy luận quan hệ dựa trên constraint, không dựa trên dữ liệu

Phần lớn schematic/ER tools suy luận:

- Có FK `client_details.user_id` → `users.id` ⇒ `client_details` **belongsTo** `users`
- Nhưng nếu không có ràng buộc `UNIQUE` trên `client_details.user_id` ⇒ phía `users` có thể có **nhiều** `client_details` (về mặt schema), nên tool kết luận **1–many**.

### 2.2 File dump vs DB thật

File `database/schema/mysql-schema.dump` có thể **lỗi thời** (snapshot cũ). Trong dump đó `client_details` có:

- `KEY client_details_user_id_foreign (user_id)` (index thường)
- `FOREIGN KEY client_details_user_id_foreign (user_id) REFERENCES users(id)`
- **Không** phản ánh migration sau này thêm `UNIQUE`.

**Trên DB đã migrate (kiểm tra 2026-03-24):** có thêm `UNIQUE` `client_details_user_id_unique` trên `user_id` (đồng thời vẫn có FK như trên). Tool schematic nên đọc **schema live** hoặc regenerate dump sau khi migrate.

---

## 3. Kiểm tra thực tế dữ liệu (DB)

Kết quả truy vấn trực tiếp trên DB (**2026-03-24**, cùng môi trường dev/local):

| Kiểm tra                                                         | Kết quả    |
| ---------------------------------------------------------------- | ---------- |
| Số dòng `client_details`                                         | **17.565** |
| Số `user_id` khác nhau trong `client_details`                    | **17.565** |
| Nhóm `user_id` có > 1 `client_details`                           | **0**      |
| User có role `client`                                            | **17.565** |
| User role `client` nhưng không có dòng `client_details`          | **0**      |
| `client_details.user_id` trỏ tới user **không** có role `client` | **0**      |

=> **Dữ liệu đúng 1–1 và đồng bộ với role client**; **ràng buộc UNIQUE + FK** đã có ở DB (xem mục 1).

---

## 4. Giải pháp khuyến nghị: Thêm UNIQUE cho `client_details.user_id`

_(Trạng thái code: migration đã có trong repo; trên DB kiểm tra 2026-03-24 **đã** áp dụng UNIQUE.)_

### 4.1 Mục tiêu

Ép schema-level thể hiện đúng nghiệp vụ:

- Mỗi `users.id` (client user) có tối đa **1** dòng `client_details`.

### 4.2 Tác động đến hệ thống

**Không thay đổi mô hình multi-tenant theo company.**  
`company_id` vẫn là FK/tenant scope như cũ.

**Những ảnh hưởng chính:**

- **Integrity tốt hơn:** ngăn phát sinh dữ liệu sai (lỡ create thêm `client_details` lần 2 cho cùng `user_id`).
- **Có thể lộ bug ẩn:** nếu có luồng code nào đang “create mới” thay vì “update” `client_details`, sau khi thêm UNIQUE sẽ bị lỗi insert (đúng mong đợi).
- **Hiệu năng:** thường **không xấu đi**, vì lookup theo `user_id` được tối ưu tốt hơn với unique index.

### 4.3 Rủi ro khi chạy migration

- Nếu môi trường nào đó đã có duplicate `client_details.user_id` (khác với DB hiện tại), migration thêm UNIQUE sẽ **fail**.
    - Cần chạy kiểm tra duplicates trước khi apply ở staging/production.

---

## 5. Vì sao “company” không bị ảnh hưởng?

Hệ thống hiện tại là multi-tenant theo `company_id`:

- `users.company_id` gắn user vào **một** company.
- `client_details.company_id` về thực tế nên khớp với `users.company_id` của user đó.

Thêm `UNIQUE(client_details.user_id)` chỉ siết quan hệ theo user, **không**:

- thay đổi FK `company_id`
- thay đổi scope theo company
- không tạo quan hệ “cross-company”

---

## 6. Nếu muốn “1 client thuộc nhiều company” thì sao?

Với thiết kế hiện tại: **không** (một user chỉ thuộc một company do `users.company_id`).

### 6.1 Làm rõ: UNIQUE `client_details.user_id` **không** quyết định email có trùng được hay không

- Thêm `UNIQUE(client_details.user_id)` chỉ đảm bảo **mỗi user có tối đa 1 client_details**.
- Việc “có thể tạo client mới cho company B với **cùng email** như company A hay không” phụ thuộc vào:
    - **`users.email` đang unique toàn cục** (trong migration `users` có `email->unique()`), và
    - Thiết kế multi-tenant hiện tại gắn user vào **một** company qua `users.company_id`.

**Vì vậy:** ngay cả khi **chưa** thêm `UNIQUE(client_details.user_id)`, hệ thống vẫn **không** cho phép dùng lại cùng email để tạo user client cho company khác (trừ khi thay đổi constraint/thiết kế).

Muốn 1 người / 1 email có thể là client của nhiều company thì cần **đổi kiến trúc**:

### Option A (khuyến nghị nếu cần multi-company membership)

- Tách “identity” và “membership”
- Tạo bảng pivot kiểu `company_users` / `company_clients` để 1 identity có nhiều company.

### Option B (nhân bản user theo company)

- Một company có một user riêng (dù cùng email)
- Phải đổi unique constraint `users.email` (vd. unique theo `(company_id,email)`).

Đây là thay đổi lớn, không nên làm chỉ để phục vụ schematic tool.

---

## 7. Checklist triển khai (khi quyết định thêm UNIQUE)

- [x] Migration `unique(client_details.user_id)` — có file `2026_03_15_000000_add_unique_user_id_to_client_details_table.php`
- [x] Dev/local (2026-03-24): 0 duplicate `user_id`; index `client_details_user_id_unique` tồn tại
- [ ] **Staging/prod:** trước khi migrate, chạy kiểm tra duplicates (nếu chưa migrate):
    - `select user_id, count(*) from client_details group by user_id having count(*)>1;`
- [ ] **Staging/prod:** chạy migrate và xác nhận index giống dev
- [ ] Regenerate `mysql-schema.dump` (hoặc dùng schema introspection live) để tool schematic không đọc file dump cũ
- [ ] Xác nhận tool schematic/AI suy luận `users` ↔ `client_details` là 1–1 khi trỏ vào DB đã có UNIQUE
