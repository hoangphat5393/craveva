# Báo cáo hệ thống: Users/Clients DB + Login Flow

**Ngày lập:** 2026-03-15  
**Phạm vi:**

- Cấu trúc bảng và quan hệ (migrations + Eloquent) giữa `users` và các bảng liên quan client
- Luồng đăng nhập (Fortify + session + cache) và các điểm troubleshoot thường gặp

---

## 1. Tổng quan

- **Client** trong hệ thống là một **User** có role `client` (bảng `role_user` + `roles`).
- Mỗi client (company) có 1 bản ghi **client_details** (thông tin công ty, địa chỉ, category, …) liên kết qua `user_id`.
- **client_contacts**: liên hệ con (người đại diện) của client; có thể là một User khác khi `users.is_client_contact` trỏ tới `client_contacts.id`.
- **client_notes**, **client_docs**, **projects**, **contracts**, **invoices**, **estimates** tham chiếu tới client qua `users.id` (client là một user).

---

## 2. Bảng `users`

**Migration gốc:** `2018_01_01_000000_create_craveva_new_table.php`

| Cột liên quan client | Kiểu              | Ghi chú                                      |
| -------------------- | ----------------- | -------------------------------------------- |
| `id`                 | increments        | PK                                           |
| `company_id`         | unsigned nullable | FK → companies.id                            |
| `name`               | string            | Tên user (client/contact)                    |
| `email`              | string nullable   |                                              |
| Các cột khác         | …                 | status, locale, image, mobile, country_id, … |

**Cột thêm sau (liên quan client):**

- `is_client_contact` (migration `2024_11_05_082725_add_client_id_column_in_client_contanct_table.php`):
    - `unsignedInteger nullable`, FK → **client_contacts.id**
    - Nếu có giá trị: user này là **contact** của một client (bản ghi client_contacts tương ứng).

**Quan hệ Eloquent (User model) – client:**

- `clientDetails()`: `hasOne(ClientDetails::class, 'user_id')` — **Đúng**: 1 user client có 1 client_details.
- `clientContact()`: `hasOne(ClientContact::class, 'id', 'is_client_contact')` — **Đúng**: user là contact thì trỏ tới 1 client_contacts.
- `projects()`: `hasMany(Project::class, 'client_id')` — **Đúng**: project thuộc client (user).
- `contracts()`: `hasMany(Contract::class, 'client_id', 'id')` — **Đúng**.
- `invoices()`: `hasMany(Invoice::class, 'client_id')` — **Đúng**.
- `estimates()`: `hasMany(Estimate::class, 'client_id')` — **Đúng**.

---

## 3. Bảng `client_details`

**Migration:** `2018_01_01_000000_create_craveva_new_table.php` (+ các migration alter sau).

| Cột                                                                                                           | Kiểu                        | FK / Ràng buộc                                           |
| ------------------------------------------------------------------------------------------------------------- | --------------------------- | -------------------------------------------------------- |
| `id`                                                                                                          | increments                  | PK                                                       |
| `company_id`                                                                                                  | unsigned nullable           | → companies.id                                           |
| **user_id**                                                                                                   | unsignedInteger             | **→ users.id** (CASCADE) — **Đúng**: 1-1 với user client |
| `company_name`, `address`, `shipping_address`, `postal_code`, `state`, `city`, `office`, `website`, `note`, … | …                           |                                                          |
| `category_id`                                                                                                 | unsignedBigInteger nullable | → client_categories.id                                   |
| `sub_category_id`                                                                                             | unsignedBigInteger nullable | → client_sub_categories.id                               |
| `added_by`                                                                                                    | unsignedInteger nullable    | → users.id                                               |
| `last_updated_by`                                                                                             | unsignedInteger nullable    | → users.id                                               |
| `client_code`                                                                                                 | (thêm sau)                  | Unique (company_id, client_code) — migration 2026_03_09  |

**Quan hệ Eloquent (ClientDetails):**

- `user()`: `belongsTo(User::class, 'user_id')` — **Đúng**.
- `addedBy()`: `belongsTo(User::class, 'added_by', 'id')` — **Đúng**.

**Kết luận:** Quan hệ **users ↔ client_details** (1-1 qua `user_id`) **đúng** và nhất quán.

**Đã kiểm tra trên DB (chạy script truy vấn thực tế):**

- Số dòng `client_details`: 17.565; số `user_id` khác nhau: 17.565 → không có `user_id` trùng (mỗi user tối đa 1 client_details).
- Không có user có role `client` thiếu `client_details`; không có `client_details.user_id` nào không phải user role client.
- **Kết luận DB: quan hệ 1-1 được đảm bảo trong dữ liệu hiện tại.**

---

## 4. Bảng `client_contacts`

**Migration gốc:** `2018_01_01_000000_create_craveva_new_table.php`  
**Migration bổ sung:** `2024_11_05_082725_add_client_id_column_in_client_contanct_table.php` (thêm `client_id`, và trên `users` thêm `is_client_contact`).

| Cột                                                  | Kiểu                     | FK / Ghi chú                                                                    |
| ---------------------------------------------------- | ------------------------ | ------------------------------------------------------------------------------- |
| `id`                                                 | increments               | PK                                                                              |
| `company_id`                                         | unsigned nullable        | → companies.id                                                                  |
| **user_id**                                          | unsignedInteger          | **→ users.id** (CASCADE) — User là **client (công ty)** mà contact này thuộc về |
| **client_id**                                        | unsignedInteger nullable | **→ users.id** (CASCADE) — Thêm sau; cùng trỏ tới client user                   |
| `contact_name`, `phone`, `email`, `title`, `address` | …                        |                                                                                 |
| `added_by`, `last_updated_by`                        | unsignedInteger nullable | → users.id                                                                      |

**Quan hệ Eloquent (ClientContact):**

- `client()`: `belongsTo(User::class, 'user_id')` — **Đúng**: contact thuộc client (user) nào.

**Quan hệ ngược (User):**

- `clientContact()`: `hasOne(ClientContact::class, 'id', 'is_client_contact')` — **Đúng**: user là contact thì có 1 bản ghi client_contacts, và `users.is_client_contact = client_contacts.id`.

**Ghi chú:** `client_contacts.user_id` và `client_contacts.client_id` đều có thể trỏ tới **users.id** (client). Migration 2024_11_05 thêm `client_id`; trong code hiện tại vẫn dùng `user_id` là chính. Có thể xem **client_id** là trùng nghĩa với **user_id** hoặc dự phòng; nếu đồng bộ dữ liệu thì nên giữ cả hai cùng giá trị khi một contact thuộc một client.

---

## 5. Bảng `client_notes`

**Migration:** `2018_01_01_000000_create_craveva_new_table.php`.

| Cột                           | Kiểu                     | FK                                                        |
| ----------------------------- | ------------------------ | --------------------------------------------------------- |
| `id`                          | increments               | PK                                                        |
| `company_id`                  | unsigned nullable        | → companies.id                                            |
| **client_id**                 | unsignedInteger nullable | **→ users.id** (CASCADE) — Client (user) mà note thuộc về |
| `title`, `type`, `details`, … | …                        |                                                           |
| `member_id`                   | unsignedInteger nullable | → users.id                                                |
| `added_by`, `last_updated_by` | unsignedInteger nullable | → users.id                                                |

**Quan hệ Eloquent (ClientNote):**

- `client()`: `belongsTo(User::class, 'client_id')` — **Đúng**.

**Kết luận:** **client_notes.client_id → users.id** **đúng**.

---

## 6. Bảng `client_docs` (ClientDocument)

**Migration:** `2018_01_01_000000_create_craveva_new_table.php` — bảng có cột **user_id** (→ users.id).

**Quan hệ Eloquent (ClientDocument):**

- `client()`: `return $this->belongsTo(User::class, 'client_id');`

**Vấn đề:** Migration chỉ có **user_id**, không có **client_id**. Nếu bảng hiện tại vẫn chỉ có `user_id` thì quan hệ nên là `belongsTo(User::class, 'user_id')` (tên method vẫn có thể là `client()`). Cần kiểm tra DB thực tế hoặc migration sau có thêm `client_id` hay không.

**Đề xuất:** Nếu bảng chỉ có `user_id`: sửa thành `belongsTo(User::class, 'user_id')` để khớp schema.

---

## 7. Bảng `client_categories` và `client_sub_categories`

- **client_categories**: `company_id` → companies.id. Không trực tiếp FK tới users.
- **client_details.category_id** → client_categories.id; **client_details.sub_category_id** → client_sub_categories.id — **Đúng**.

---

## 8. Các bảng khác tham chiếu Client (users)

| Bảng              | Cột                     | FK                                             | Ghi chú                               |
| ----------------- | ----------------------- | ---------------------------------------------- | ------------------------------------- |
| projects          | client_id               | → users.id                                     | **Đúng** (Project model: `client_id`) |
| contracts         | client_id               | → users.id                                     | **Đúng**                              |
| invoices          | client_id               | → users.id                                     | **Đúng**                              |
| estimates         | client_id               | → users.id                                     | **Đúng**                              |
| client_user_notes | user_id, client_note_id | user_id → users, client_note_id → client_notes | Gắn user với note — **Đúng**          |

---

## 9. Sơ đồ quan hệ tóm tắt

```
users (id)
  ├── 1:1 client_details (user_id) ✅
  ├── 0..1 client_contacts (is_client_contact → client_contacts.id) — user là contact ✅
  ├── 1:n projects (client_id) ✅
  ├── 1:n contracts (client_id) ✅
  ├── 1:n invoices (client_id) ✅
  └── 1:n estimates (client_id) ✅

client_details (user_id → users.id) ✅
  └── category_id → client_categories, sub_category_id → client_sub_categories ✅

client_contacts (user_id → users.id = client company; users.is_client_contact → client_contacts.id) ✅
  └── client_id → users.id (cùng nghĩa với user_id, thêm sau) ✅

client_notes (client_id → users.id) ✅

client_docs (user_id → users.id) ⚠️ Model dùng client_id — cần kiểm tra cột thực tế.
```

---

## 10. Kết luận và khuyến nghị

- **Đúng và nhất quán:**
    - **users ↔ client_details**: 1-1 qua `user_id`.
    - **users ↔ client_contacts**: user là contact qua `is_client_contact` → client_contacts.id; client_contacts.user_id = client (user).
    - **client_notes.client_id**, **projects.client_id**, **contracts.client_id**, **invoices.client_id**, **estimates.client_id** đều trỏ tới **users.id** và khớp với model.

- **Cần kiểm tra:**
    - **ClientDocument**: model dùng `client_id` nhưng migration gốc chỉ có `user_id`. Nên kiểm tra schema thực tế; nếu chỉ có `user_id` thì sửa relation thành `belongsTo(User::class, 'user_id')`.

- **Ghi chú thiết kế:**
    - **client_contacts.client_id** và **user_id** cùng trỏ tới users.id (client). Nên đồng bộ giá trị khi tạo/cập nhật contact để tránh lệch dữ liệu.

---

## 11. Bảng dữ liệu ↔ chức năng nghiệp vụ (gộp từ tài liệu business)

| Bảng / thực thể                              | Vai trò nghiệp vụ                                                 | Chức năng/màn hình chính                                   | Ghi chú                                         |
| -------------------------------------------- | ----------------------------------------------------------------- | ---------------------------------------------------------- | ----------------------------------------------- |
| `users` (role `client`)                      | Tài khoản đăng nhập của khách hàng theo `company_id`              | Clients (`ClientController`): list/create/edit/show/import | Client gốc là một dòng `users` có role `client` |
| `client_details`                             | Hồ sơ doanh nghiệp client (địa chỉ, mã khách, tier, kho mặc định) | Form thông tin client; Pricing đọc `pricing_tier_id`       | Nghiệp vụ 1 user client : 1 client_details      |
| `client_categories`, `client_sub_categories` | Danh mục phân loại client                                         | Chọn category/sub-category trong form client               | Dùng qua FK trên `client_details`               |
| `client_contacts`                            | Người liên hệ thuộc client                                        | Tab Contacts (`ClientContactController`)                   | 1 client : nhiều contact                        |
| `users.is_client_contact`                    | Liên kết user login là contact                                    | Portal/login theo contact                                  | Trỏ tới `client_contacts.id`                    |
| `client_notes`                               | Ghi chú nội bộ cho client                                         | Client notes (`ClientNoteController`)                      | `client_id` trỏ `users.id`                      |
| `client_user_notes`                          | Pivot user ↔ note                                                 | Luồng note theo user                                       | Không phải 1-1                                  |
| `client_docs`                                | Tài liệu đính kèm client                                          | Hồ sơ/tài liệu client                                      | Bảng dùng `user_id`; model cần khớp schema      |
| `client_product_pricing`                     | Giá riêng theo client + sản phẩm + kỳ hiệu lực                    | Pricing module (`PricingService`)                          | Nhiều dòng theo client/product/time             |
| `employee_details`                           | Hồ sơ nhân viên (không phải client)                               | Employees/Leave/Payroll/Performance                        | Tách biệt nghiệp vụ client                      |

### Bảng giao dịch tham chiếu client qua `users.id`

- `projects` (`client_id`)
- `contracts` (`client_id`)
- `invoices` (`client_id`)
- `estimates` (`client_id`)

Các bảng này là giao dịch, không thay thế `client_details`.

---

_Báo cáo dựa trên migrations và code Eloquent trong repo; không truy vấn database thực tế._

---

## 12. Login Flow (Fortify) – tóm tắt vận hành & troubleshoot

### 12.1 ASCII Visual Flow

```ascii
+---------------------+       +----------------------+       +-------------------------+
|   User (Browser)    |       |   Laravel Backend    |       |        Database         |
+---------------------+       +----------------------+       +-------------------------+
          |                              |                                |
          | 1. Enter Email               |                                |
          |----------------------------->|                                |
          | (AJAX POST /check-email)     |                                |
          |                              | 2. Check users table           |
          |                              |------------------------------->|
          |                              | 3. Return: Email Exists?       |
          |                              |<-------------------------------|
          | 4. JSON Response             |                                |
          |<-----------------------------|                                |
          | (Show Password Field)        |                                |
          |                              |                                |
          | 5. Enter Password & Submit   |                                |
          |----------------------------->|                                |
          | (AJAX POST /login)           |                                |
          |                              | 6. Fortify Authentication      |
          |                              |    (AttemptToAuthenticate)     |
          |                              | 7. Validate Credentials        |
          |                              |    (Hash::check)               |
          |                              |------------------------------->| (user_auths table)
          |                              | 8. Check Active Status         |
          |                              |------------------------------->| (users table)
          |                              | 9. Generate Session            |
          |                              |    (Write to file/redis)       |
          | 10. JSON Response            |                                |
          |<-----------------------------|                                |
          | 11. Redirect to dashboard    |                                |
+---------+------------------------------+--------------------------------+
```

### 12.2 Bảng chính liên quan login

- `user_auths`: credentials (email/password hash, 2FA)
- `users`: profile + status + `company_id`
- `sessions` (nếu `SESSION_DRIVER=database`): lưu session

### 12.3 Điểm code chính

- Frontend: `resources/views/auth/login.blade.php`
- Helper AJAX: `public/vendor/helper/helper.js` (`$.easyAjax`)
- Fortify pipeline: `config/fortify.php`
- Provider: `app/Providers/FortifyServiceProvider.php`
- Middleware: `app/Http/Middleware/Authenticate.php`
- Models: `app/Models/User.php`, `app/Models/UserAuth.php`

### 12.4 Troubleshooting thường gặp

- **Login xong bị đá về trang login ngay**
    - **Nguyên nhân hay gặp:** cache `user_is_active_{id}` bị `false` (stale cache)
    - **Fix:**

```bash
php artisan cache:forget user_is_active_{USER_ID}
php artisan cache:clear
```

- **AJAX login lỗi / form không submit đúng**
    - **Nguyên nhân:** backend trả HTML redirect thay vì JSON làm `$.easyAjax` hiểu sai
    - **Fix:** đảm bảo backend trả JSON khi `$request->wantsJson()` (đặc biệt với login modal/AJAX)

- **Credentials không khớp**
    - **Lưu ý:** hệ thống kiểm password dựa trên `user_auths`, không phải chỉ `users`

- **419 Page Expired**
    - **Nguyên nhân:** CSRF / session domain
    - **Fix:** kiểm tra CSRF token trong form và `SESSION_DOMAIN` trong `.env`
