# Luồng nghiệp vụ: Thêm dữ liệu Client

**Phạm vi:** Tạo client mới qua **form UI** (ClientController::store) hoặc **import Excel** (ClientImportProcessor::processRow). Tài liệu mô tả các bảng được ghi dữ liệu và quan hệ 1-n.

---

## 1. Tổng quan luồng

```
[Request] --> ClientController::store() hoặc ImportClientChunkJob --> ClientImportProcessor::processRow()
                |
                v
        +-------+-------+
        | UserAuth (nếu có email)
        | INSERT user_auths
        +-------+-------+
                |
                v
        +-------+-------+
        | User
        | INSERT users
        +-------+-------+
                |
                v
        +-------+-------+
        | ClientDetails
        | INSERT client_details (user_id = users.id)
        +-------+-------+
                |
                v
        +-------+-------+
        | Custom fields (nếu có)
        | INSERT custom_fields_data (model = ClientDetails, model_id = client_details.id)
        +-------+-------+
                |
                v
        +-------+-------+
        | Role + Permissions
        | INSERT role_user
        | DELETE + INSERT user_permissions
        +-------+-------+
                |
                v
        +-------+-------+
        | Universal Search
        | INSERT universal_search (2-3 dòng: name, email, company_name)
        +-------+-------+
```

### 1.1. Tại sao có cả `role_user` rồi lại `DELETE + INSERT user_permissions`?

Hai bảng phục vụ hai mục đích khác nhau:

| Bảng                 | Mục đích                                                | Nội dung                                                                                                                                                                                                                                                      |
| -------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **role_user**        | Ghi nhận **user thuộc role nào**                        | Một dòng: (user_id, role_id) – ví dụ "user 5001 có role client". Dùng khi cần biết role của user (hiển thị, phân nhóm).                                                                                                                                       |
| **user_permissions** | Bản **sao quyền** từ role xuống từng user (denormalize) | Nhiều dòng: (user_id, permission_id, permission_type_id) – ví dụ "user 5001 có quyền view_clients, add_clients, …". Dùng khi **kiểm tra quyền** (middleware, policy): chỉ cần đọc `user_permissions` theo user_id, không cần join qua role → permission_role. |

**Luồng khi gán role client:**

1. **INSERT role_user**  
   Ghi nhận: user này có role "client".

2. **DELETE + INSERT user_permissions**
    - Lấy toàn bộ quyền của role "client" từ bảng **permission_role** (role_id → permission_id, permission_type_id).
    - **DELETE** mọi dòng `user_permissions` của user này (xóa quyền cũ nếu đổi role, hoặc để trống nếu user mới).
    - **INSERT** từng dòng (user_id, permission_id, permission_type_id) tương ứng quyền của role "client".

**Lý do dùng DELETE rồi INSERT:** Để bộ quyền của user **luôn khớp với role hiện tại**. Khi đổi role (hoặc lần đầu gán), xóa hết quyền cũ rồi ghi lại theo role mới; tránh dư/thiếu quyền so với role.

**Ghi chú UI only:** Nếu tạo từ form có thể thêm: `client_contacts`, `client_notes`, cập nhật `leads.client_id`.

---

## 2. Ví dụ thực tế: một dòng từ file Miaolin Customer test.xlsx

Giả sử file có header và **một dòng dữ liệu** (vd. dòng 2) như sau. Sau khi map cột, hệ thống nhận một **row** và **columns** tương ứng.

### 2.1. Dữ liệu một dòng (sau khi map cột)

**Row (mảng theo index cột):**

| Index cột | Field id (map)        | Giá trị trong Excel (ví dụ)               |
| --------- | --------------------- | ----------------------------------------- |
| 0         | client_code           | `L1447`                                   |
| 1         | name                  | `大江生醫股份有限公司`                    |
| 2         | email                 | `contact@dajiang.com.tw`                  |
| 3         | mobile                | `02-12345678`                             |
| 4         | company_name          | `大江生醫`                                |
| 5         | address               | `台北市信義區信義路五段7號`               |
| 6         | city                  | `台北市`                                  |
| 7         | state                 | `信義區`                                  |
| 8         | postal_code           | `110`                                     |
| 9         | company_phone         | `02-12345678`                             |
| 10        | salesperson           | `王小明` (custom field)                   |
| 11        | department            | `北區業務` (custom field)                 |
| 12        | payment_terms         | `月結30天` (custom field)                 |
| 13        | business_closure_date | `20251231` hoặc `31/12/2025` custom field |

**Columns (map gửi từ form):** `[0=>'client_code', 1=>'name', 2=>'email', ...]` — index là key, value là field id.

### 2.2. Luồng insert từng bước với dữ liệu trên

**Bước 1 – user_auths (vì có email):**

```text
INSERT INTO user_auths (email, password, email_verified_at, ...)
VALUES ('contact@dajiang.com.tw', '<hashed>', NOW(), ...);
-- Giả sử id = 9001
```

**Bước 2 – users:**

```text
INSERT INTO users (company_id, name, email, mobile, gender, country_id, user_auth_id, ...)
VALUES (1, '大江生醫股份有限公司', 'contact@dajiang.com.tw', '02-12345678', NULL, NULL, 9001, ...);
-- Giả sử id = 5001
```

**Bước 3 – client_details:**

```text
INSERT INTO client_details (company_id, user_id, client_code, company_name, address, city, state, postal_code, office, ...)
VALUES (1, 5001, 'L1447', '大江生醫', '台北市信義區信義路五段7號', '台北市', '信義區', '110', '02-12345678', ...);
-- Giả sử id = 3001  ← đây là PK của client_details, dùng làm model_id cho custom_fields_data
```

**Bước 4 – custom_fields_data (custom field):**

Hệ thống đọc nhóm "Client" và các custom field (salesperson, department, payment_terms, business_closure_date, ...). Mỗi field có `id` trong bảng `custom_fields` (vd. 101, 102, 103, 104). Giá trị từ row được chuẩn hóa (vd. ngày `20251231` → `2025-12-31`), rồi **mỗi cặp (field_id, value)** ghi một dòng vào `custom_fields_data` với **model = ClientDetails, model_id = client_details.id**:

```text
-- Lưu ý: model_id ở đây là client_details.id (3001), không phải users.id (5001)
INSERT INTO custom_fields_data (model, model_id, custom_field_id, value) VALUES
('App\\Models\\ClientDetails', 3001, 101, '王小明'),   -- salesperson (giả sử custom_fields.id = 101)
('App\\Models\\ClientDetails', 3001, 102, '北區業務'), -- department (id = 102)
('App\\Models\\ClientDetails', 3001, 103, '月結30天'), -- payment_terms (id = 103)
('App\\Models\\ClientDetails', 3001, 104, '2025-12-31'); -- business_closure_date đã chuẩn hóa (id = 104)
```

**Bước 5 – role_user:**

```text
INSERT INTO role_user (user_id, role_id) VALUES (5001, <id của role 'client'>);
```

**Bước 6 – user_permissions:**  
Xóa toàn bộ `user_permissions` của `user_id = 5001`, rồi insert nhiều dòng lấy từ `permission_role` của role client (mỗi permission → một dòng `user_permissions`).

**Bước 7 – universal_search (2 hoặc 3 dòng):**

```text
INSERT INTO universal_search (company_id, searchable_id, title, route_name, module_type) VALUES
(1, 5001, '大江生醫股份有限公司', 'clients.show', 'client'),
(1, 5001, 'contact@dajiang.com.tw', 'clients.show', 'client'),
(1, 5001, '大江生醫', 'clients.show', 'client');
```

### 2.3. Tóm tắt một dòng Excel → các bảng

| Bảng               | Số dòng insert (ví dụ này) | Giá trị then chốt                                                                            |
| ------------------ | -------------------------- | -------------------------------------------------------------------------------------------- |
| user_auths         | 1                          | email, password                                                                              |
| users              | 1                          | id=5001, name, email, user_auth_id                                                           |
| client_details     | 1                          | id=3001, user_id=5001, client_code=L1447, company_name, address...                           |
| custom_fields_data | 4                          | model='ClientDetails', model_id=**3001** (client_details.id), 4 cặp (custom_field_id, value) |
| role_user          | 1                          | user_id=5001, role_id                                                                        |
| user_permissions   | Nhiều                      | user_id=5001, permission_id, permission_type_id                                              |
| universal_search   | 3                          | searchable_id=5001 (users.id), title = name / email / company_name                           |

**Điểm quan trọng:** Custom field luôn gắn với **client_details.id** (3001), không phải users.id (5001). Trait `CustomFieldsTrait::updateCustomFieldData()` gọi trên đối tượng `ClientDetails` nên `$this->id` chính là `client_details.id`.

---

## 3. Các bảng được ghi (khi thêm 1 client) – tổng quát

| #   | Bảng                   | Số dòng thêm | Ghi chú                                                                                              |
| --- | ---------------------- | ------------ | ---------------------------------------------------------------------------------------------------- |
| 1   | **user_auths**         | 0 hoặc 1     | Chỉ khi có email; `UserAuth::createUserAuthCredentials()`                                            |
| 2   | **users**              | 1            | `User::create()` hoặc `$user->save()`; `user_auth_id` = user_auths.id nếu có email                   |
| 3   | **client_details**     | 1            | `$user->clientDetails()->create($data)` hoặc `ClientDetails::save()`; `user_id` = users.id           |
| 4   | **custom_fields_data** | N            | N = số custom field có giá trị; `model` = 'App\Models\ClientDetails', `model_id` = client_details.id |
| 5   | **role_user**          | 1            | `$user->attachRole($role->id)`; role name = 'client'                                                 |
| 6   | **user_permissions**   | Nhiều        | `assignUserRolePermission()`: xóa theo user_id rồi insert từ permission_role                         |
| 7   | **universal_search**   | 2 hoặc 3     | `logSearchEntry()`: name, email (nếu có), company_name (nếu có)                                      |

**Bảng chỉ đọc (metadata):** `roles`, `custom_field_groups`, `custom_fields`, `permission_role`, `companies`.

---

## 4. Mô hình quan hệ (ASCII) – Client và bảng liên quan

```
                    companies (1)
                         |
         +---------------+---------------+
         |               |               |
         v               v               v
      users (n)    client_details (n)   roles (n)
         |               |               |
         | user_id       | id            |
         +-------> client_details.user_id
         |               |
         |               | model_id (polymorphic)
         |               v
         |         custom_fields_data (n)
         |               |
         |         custom_field_id --> custom_fields (n) --> custom_field_groups (1)
         |
         | user_auth_id
         v
    user_auths (1)

         users (1) ----< role_user >---- (1) roles
         users (1) ----< user_permissions >---- (n) permissions (qua permission_role)

         users.id = searchable_id (với module_type = 'client')
         v
    universal_search (n)
```

---

## 5. Quan hệ 1-n (tóm tắt)

| Bảng cha                    | Quan hệ | Bảng con           | Khóa ngoại                                           |
| --------------------------- | ------- | ------------------ | ---------------------------------------------------- |
| companies                   | 1-n     | users              | users.company_id                                     |
| companies                   | 1-n     | client_details     | client_details.company_id                            |
| user_auths                  | 1-n     | users              | users.user_auth_id                                   |
| users                       | 1-1     | client_details     | client_details.user_id                               |
| roles                       | n-n     | users              | qua role_user (role_id, user_id)                     |
| users                       | 1-n     | user_permissions   | user_permissions.user_id                             |
| custom_field_groups         | 1-n     | custom_fields      | custom_fields.custom_field_group_id                  |
| client_details (model + id) | 1-n     | custom_fields_data | custom_fields_data.model, model_id                   |
| custom_fields               | 1-n     | custom_fields_data | custom_fields_data.custom_field_id                   |
| users (client)              | 1-n     | universal_search   | universal_search.searchable_id, module_type='client' |

---

## 6. Nguồn code tham chiếu

| Bước         | File / method                                            |
| ------------ | -------------------------------------------------------- |
| Form UI      | `App\Http\Controllers\ClientController::store()`         |
| Import       | `App\Services\ClientImportProcessor::processRow()`       |
| UserAuth     | `App\Models\UserAuth::createUserAuthCredentials()`       |
| Custom field | `App\Traits\CustomFieldsTrait::updateCustomFieldData()`  |
| Role         | `User::attachRole()`, `User::assignUserRolePermission()` |
| Search       | `App\Traits\UniversalSearchTrait::logSearchEntry()`      |

---

## 7. Lưu ý Import

- Import **không** tạo `client_contacts`, `client_notes`.
- Import **có** gọi `saveCustomFieldsFromRow()` → `updateCustomFieldData()` nên vẫn ghi `custom_fields_data` như form.
- `model_id` trong `custom_fields_data` cho Client = **client_details.id** (PK của bảng client_details), không phải users.id.
