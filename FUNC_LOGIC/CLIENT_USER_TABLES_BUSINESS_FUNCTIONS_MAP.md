# Bảng tra cứu: Bảng dữ liệu ↔ chức năng nghiệp vụ (User client & liên quan)

**Mục đích:** Một bảng duy nhất để xem **bảng nào phục vụ chức năng gì** trong app (CRM/B2B), trước khi sửa FK/schema.  
**Phạm vi:** Các bảng trực tiếp tên `client_*`, cột `users` gắn client/contact, `employee_details`, và **`client_product_pricing`** (Pricing module).

---

## Bảng tổng hợp

| Bảng / thực thể                                  | Vai trò nghiệp vụ (ngắn gọn)                                                                                    | Chức năng / màn hình chính trong hệ thống                                                                                                                                                                                       | Ghi chú vận hành                                                                                                                                                     |
| ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`users`** (user có role `client`)              | Tài khoản đăng nhập của **khách hàng (client)** trong tenant (`company_id`).                                    | Menu **Clients** (`ClientController`): danh sách, tạo/sửa client, import client, hồ sơ client (`clients.show`), quyền `view_clients` / `add_clients` / …                                                                        | Client = một dòng `users` + role `client`; mọi thứ “theo công ty khách” thường gắn `users.id` này.                                                                   |
| **`client_details`**                             | **Hồ sơ doanh nghiệp / địa chỉ / mã khách / warehouse mặc định / tier giá** của client (1–1 với user client).   | Cùng flow **Clients**: tab thông tin công ty, địa chỉ giao hàng, `client_code`, `pricing_tier_id`, `default_warehouse_id`; **Pricing** (`ClientTierController`, `PricingService`) đọc tier từ đây; đồng bộ kho (Maolin) nếu có. | Một client user **một** bản ghi; đây là “mặt B2B” của client, không phải tài khoản login.                                                                            |
| **`client_categories`**                          | Danh mục phân loại client (master theo company).                                                                | Cấu hình / chọn khi tạo–sửa **client** (trường category trên `client_details`).                                                                                                                                                 | Không gắn trực tiếp `users`; dùng qua `client_details.category_id`.                                                                                                  |
| **`client_sub_categories`**                      | Danh mục con dưới **client_categories**.                                                                        | Tương tự, chọn sub-category trên hồ sơ client.                                                                                                                                                                                  | FK từ `client_details.sub_category_id`.                                                                                                                              |
| **`client_contacts`**                            | **Người liên hệ** (tên, SĐT, email, chức danh…) thuộc một client (công ty).                                     | Menu **Clients** → tab **Contacts** (`ClientContactController`): thêm/sửa/xóa contact; có thể liên kết user login riêng qua `users.is_client_contact`.                                                                          | Quan hệ **1 client : nhiều contact** (`user_id` = client công ty). Khác với `client_details` (1–1).                                                                  |
| **`users.is_client_contact`** (cột trên `users`) | Đánh dấu user login là **một contact** đã “nâng” thành tài khoản (trỏ tới `client_contacts.id`).                | Đăng nhập portal / quyền theo contact; join với `client_contacts` khi cần biết contact thuộc client nào.                                                                                                                        | **0..1** contact user; có FK tới `client_contacts.id` (đã có trong DB kiểm tra trước).                                                                               |
| **`client_notes`**                               | **Ghi chú nội bộ** về client (tiêu đề, nội dung, loại, hiển thị cho client hay không, v.v.).                    | **Clients** → ghi chú client (`ClientNoteController`, `ClientNotesDataTable`, trong `ClientController`).                                                                                                                        | `client_id` → `users.id` (client). Có thể gắn thêm `member_id` (user liên quan).                                                                                     |
| **`client_user_notes`**                          | Bảng **pivot**: user nào đã “gắn” / đọc / liên kết với **client_note** nào (theo thiết kế dữ liệu).             | Luồng ghi chú client khi cần theo dõi theo user (tùy màn hình).                                                                                                                                                                 | `user_id` + `client_note_id`; không phải 1–1.                                                                                                                        |
| **`client_docs`** (`ClientDocument`)             | **Tài liệu đính kèm** file cho client (hợp đồng, PDF…) lưu trên storage.                                        | **Clients** / cài đặt hồ sơ khi có quyền `view_client_document` (ví dụ `ProfileSettingController`); đường dẫn file theo `user_id` client.                                                                                       | **1 client : nhiều file**. Model có `belongsTo(User, 'client_id')` nhưng bảng dùng `user_id` cho file path — cần khớp schema khi sửa code (đã ghi trong báo cáo DB). |
| **`client_product_pricing`**                     | **Giá & chiết khấu riêng theo từng client + sản phẩm** trong khoảng **start_date / end_date**.                  | Module **Pricing**: tính giá bán (`PricingService::calculate`) — **ưu tiên cao nhất** so với hợp đồng doanh nghiệp và tier; import pricing (`PricingImportController`).                                                         | Nhiều dòng / client / product / khoảng thời gian; **không** phải 1–1.                                                                                                |
| **`employee_details`**                           | **Hồ sơ nhân viên** (phòng ban, chức danh, người quản lý, lịch, …) — **user role employee**, không phải client. | **Employees**, **Leave**, **Dashboard**, **Lead board**, **Deal** (filter theo employee), **Payroll** (giờ lương), **Performance** (OKR, họp), v.v.                                                                             | Eloquent `User::employeeDetail()` là `hasOne`; nghiệp vụ thường **1 user nhân viên : 1** bản ghi, nhưng schema có thể cho phép trùng nếu chưa UNIQUE.                |

---

## Bảng khác chỉ tham chiếu `client_id` → `users.id` (không nằm trong tên `client_*`)

| Bảng                                                                               | Chức năng gắn với client                            |
| ---------------------------------------------------------------------------------- | --------------------------------------------------- |
| **`projects`**                                                                     | Dự án cho khách — client là `users.id` role client. |
| **`contracts`**, **`invoices`**, **`estimates`**, **`orders`** (nếu có cột client) | Bán hàng / kế toán / báo giá theo client.           |
| **`payments`** (qua invoice/project)                                               | Thu tiền theo hóa đơn/dự án của client.             |

Các bảng này **không** thay thế `client_details`; chúng là nghiệp vụ giao dịch xây trên “client = user”.

---

## Sơ đồ luồng ý niệm (tóm tắt)

```
users (role client)
  └── 1 — 1 client_details        → hồ sơ DN, địa chỉ, tier, warehouse mặc định
  └── 1 — n client_contacts       → người liên hệ
            └── 0 — 1 users (is_client_contact) → tài khoản contact (nếu có)
  └── 1 — n client_notes          → ghi chú nội bộ
  └── 1 — n client_docs           → file đính kèm
  └── n — m client_user_notes     → pivot note ↔ user (nếu dùng)
  └── n dòng client_product_pricing → giá theo SP + kỳ hiệu lực

users (role employee)
  └── 1 — 1 employee_details (nghiệp vụ; schema có thể chưa UNIQUE)
```

---

**Nguồn tham chiếu code:** `ClientController`, `ClientContactController`, `ClientNoteController`, `Modules/Pricing/Services/PricingService.php`, `Modules/Pricing/Http/Controllers/ClientTierController.php`, `app/Models/User.php`, `FUNC_LOGIC/DATABASE_REPORT_USERS_CLIENT_TABLES_RELATIONSHIPS.md`.
