# Luồng nghiệp vụ: Thêm dữ liệu Client

**Phạm vi:** Tạo client mới qua **form UI** (ClientController::store) hoặc **import Excel** (ClientImportProcessor::processRow). Tài liệu mô tả các bảng được ghi dữ liệu và quan hệ 1-n.

**Client module hiện có:** Form thêm/sửa client; import Excel theo chunk (mặc định 100 dòng/job), **bulk insert** custom_fields_data và **cache metadata** 1 lần/chunk; khi **client_code trùng** thì **cập nhật** client cũ (updateExistingClient); custom field gồm salesperson, department, sales_assistant_name, **customer_grade**, channel_type, business_type, last_transaction_at, payment_terms, business_closure_date; trường DB vs custom giữ như §9.

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
| 12        | customer_grade        | `C級客戶` (custom field)                  |
| 13        | payment_terms         | `月結30天` (custom field)                 |
| 14        | business_closure_date | `20251231` hoặc `31/12/2025` custom field |

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

| Bước                            | File / method                                                                       |
| ------------------------------- | ----------------------------------------------------------------------------------- |
| Form UI                         | `App\Http\Controllers\ClientController::store()`                                    |
| Import chunk                    | `App\Jobs\ImportClientChunkJob::handle()`                                           |
| Import 1 dòng                   | `App\Services\ClientImportProcessor::processRow()`                                  |
| Cập nhật trùng                  | `ClientImportProcessor::updateExistingClient()`                                     |
| Bulk custom                     | `ClientImportProcessor::getClientCustomFieldMap()`, `buildCustomFieldRowsForBulk()` |
| UserAuth                        | `App\Models\UserAuth::createUserAuthCredentials()`                                  |
| Custom field (form / từng dòng) | `App\Traits\CustomFieldsTrait::updateCustomFieldData()`                             |
| Role                            | `User::attachRole()`, `User::assignUserRolePermission()`                            |
| Search                          | `App\Traits\UniversalSearchTrait::logSearchEntry()`                                 |

---

## 7. Lưu ý Import

- Import **không** tạo `client_contacts`, `client_notes`.
- **Custom field khi import:** Khi chạy qua **ImportClientChunkJob** (mặc định): processRow gọi với `skip_custom_fields => true`, custom field được gom và ghi **một lần** cuối chunk (bulk insert). Khi gọi processRow trực tiếp không qua chunk job (vd. ImportClientJob từng dòng): vẫn gọi `saveCustomFieldsFromRow()` → `updateCustomFieldData()`.
- **Role + permissions + universal_search khi import (chunk):** Trong ImportClientChunkJob, processRow gọi với `skip_role_and_search => true` và `role_id` (load 1 lần/chunk). Role_user, user_permissions và universal_search của user **mới** được gom và ghi bulk cuối chunk; không còn gọi attachRole / assignUserRolePermission / logSearchEntry từng dòng.
- `model_id` trong `custom_fields_data` cho Client = **client_details.id** (PK của bảng client_details), không phải users.id.

---

## 8. Import Client – Cải thiện cho file lớn (vd. Miaolin Product Customer ~17k dòng)

**Đã triển khai:** Bulk insert custom_fields_data, cache metadata (getClientCustomFieldMap 1 lần/chunk), chunk size mặc định 100, queue database. Chi tiết §8.2 (cột Trạng thái) và §8.3.

**Tham chiếu:** ../FUNC_IMPROVE/08_CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md, IMPORT_CHUNK_AND_BULK_INSERT.md, kinh nghiệm Product import (chunk 100, cache metadata).

### 8.1. Vấn đề trước đây (đã xử lý)

- **Chunk size:** 20 dòng/job → file 17k dòng ≈ 850 job; overhead queue lớn. → **Đã cải thiện:** chunk mặc định 100.
- **Custom field:** Mỗi dòng gọi `updateCustomFieldData()` → ~300+ query/chunk chỉ cho custom field. → **Đã cải thiện:** bulk insert cuối chunk.
- **Metadata:** CustomFieldGroup + CustomField load mỗi dòng. → **Đã cải thiện:** load 1 lần/chunk qua `getClientCustomFieldMap()`.

### 8.2. Các bước cải thiện (ưu tiên) – trạng thái

**Theo logic mới (cập nhật khi client_code trùng – §10):** Phương án dưới đây **vẫn hữu hiệu**. Cả dòng **tạo mới** và dòng **cập nhật** (trùng client_code) đều ghi `custom_fields_data` (create gọi `saveCustomFieldsFromRow`, update cũng gọi `saveCustomFieldsFromRow` trong `updateExistingClient`). Do đó bulk insert phải gom custom field của **cả hai nhánh** trong chunk (mỗi dòng trả về một `client_details.id` – dù mới hay cũ – rồi gom theo `model_id` đó); cache metadata và tăng chunk size / queue vẫn đúng như trước.

| Thứ tự | Cải thiện                                                | Mô tả ngắn                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | Hiệu quả                                                                                                                       | Trạng thái                               |
| ------ | -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------- |
| 1      | **Bulk insert custom_fields_data**                       | Trong mỗi chunk job: load group + list CustomField **1 lần**; mỗi dòng tạo mới hoặc cập nhật User + ClientDetails (và role/permissions/universal_search khi tạo mới), **không** gọi `updateCustomFieldData`/`saveCustomFieldsFromRow` trong vòng lặp. Gom toàn bộ (model, model_id, custom_field_id, value) đã chuẩn hóa vào mảng, cuối chunk gọi **một lần** `DB::table('custom_fields_data')->insert($bulkRows)` (có thể chia batch 100–500 dòng/lần). **Áp dụng cho cả dòng create và dòng update** (model_id = client_details.id tương ứng). | Giảm mạnh query/chunk (từ ~300+ xuống ~3–5 cho phần custom field).                                                             | ✅ **Đã triển khai**                     |
| 2      | **Cache metadata trong chunk**                           | Load CustomFieldGroup (Client) + CustomField (id, name, type) **1 lần** trong `ImportClientChunkJob::handle()`, truyền map (name → id, type) vào logic build custom field; bỏ mọi `CustomField::findOrFail()` từng field.                                                                                                                                                                                                                                                                                                                        | Đã nằm trong thiết kế bulk insert; nếu chưa bulk thì vẫn giảm đáng kể query.                                                   | ✅ **Đã triển khai**                     |
| 3      | **Chunk size**                                           | Sau khi có bulk insert: tăng chunk lên **50–100** dòng/job (như Product) để giảm số job; cân bằng với timeout mỗi request (poll chạy queue:work --max-jobs=50).                                                                                                                                                                                                                                                                                                                                                                                  | Giảm số job, giảm overhead queue.                                                                                              | ✅ **Đã triển khai** (mặc định 100)      |
| 4      | **Queue**                                                | Giữ `QUEUE_CONNECTION=database` cho file ~17k dòng; tránh timeout và có progress.                                                                                                                                                                                                                                                                                                                                                                                                                                                                | Ổn định, không treo request.                                                                                                   | ✅ **Đã triển khai** (cấu hình hiện tại) |
| 5      | **Bulk role_user + user_permissions + universal_search** | Trong chunk job: load Role (client) và PermissionRole **1 lần**; gọi processRow với `skip_role_and_search => true` (không gọi attachRole / assignUserRolePermission / logSearchEntry từng dòng). Với user mới (`wasRecentlyCreated`): gom (user_id, role_id), gom toàn bộ (user_id, permission_id, permission_type_id), gom (searchable_id, title, route_name, module_type, company_id). Cuối chunk: bulk insert role_user, bulk delete user_permissions theo user_id in (...), bulk insert user_permissions, bulk insert universal_search.      | Giảm mạnh query/dòng (không còn 1 Role + 1 PermissionRole + 1 DELETE + N INSERT + 2–3 logSearchEntry mỗi dòng); giảm deadlock. | ✅ **Đã triển khai**                     |

**Tùy chọn:** Checkbox "Không import custom field" trên form import → bỏ hoàn toàn query custom field để import nhanh (user cập nhật custom field sau nếu cần).

### 8.3. Luồng đã triển khai (bulk insert + chunk 100)

1. **ImportClientChunkJob::handle():**
    - Load 1 lần: `ClientImportProcessor::getClientCustomFieldMap($companyId)`; **Role** (client) và **PermissionRole::where('role_id', $roleId)->get()**.
    - Khởi tạo `$bulkCustomRows`, `$roleUserRows`, `$userPermissionRows`, `$universalSearchRows`.
2. **Với mỗi dòng trong chunk:**
    - Gọi `ClientImportProcessor::processRow(..., ['skip_custom_fields' => true, 'skip_role_and_search' => true, 'role_id' => $roleId])` → không gọi saveCustomFieldsFromRow / attachRole / assignUserRolePermission / logSearchEntry trong processRow. processRow trả về `User` (tạo mới hoặc cập nhật).
    - Nếu `$user->wasRecentlyCreated`: gom role_user (user_id, role_id), gom user_permissions (user_id, permission_id, permission_type_id) từ danh sách role permissions, gom universal_search (email, company_name).
    - `$user->load('clientDetails')`, xóa sẵn custom_fields_data theo model_id, gọi `buildCustomFieldRowsForBulk` → đẩy vào `$bulkCustomRows`.
3. **Hết chunk:** Bulk insert role_user (batch 100); DELETE user_permissions WHERE user_id IN (...); bulk insert user_permissions (batch 500); bulk insert universal_search (batch 200); bulk insert custom_fields_data (batch 500).
4. **Chunk size mặc định:** 100 dòng/job (`ClientController::importProcess()`).

### 8.4. Deadlock khi import trên staging (đã xử lý)

Trước đây: nhiều chunk job chạy song song, mỗi dòng gọi `assignUserRolePermission` (DELETE + INSERT từng user) → nhiều transaction cùng ghi `user_permissions` → **deadlock (1213)** hoặc **serialization failure (40001)**.

**Đã triển khai:** (1) **Bulk user_permissions** (§8.2 mục 5): không còn DELETE/INSERT từng dòng trong transaction; cuối chunk mới ghi role_user + user_permissions + universal_search → giảm tranh chấp lock. (2) **Retry khi deadlock**: mỗi dòng vẫn trong transaction có retry tối đa 3 lần (100ms/200ms/300ms) nếu bắt được exception deadlock/serialization (1213, 40001).

**Tại sao import Product không bị?** Product không tạo user, không ghi `user_permissions`; chỉ ghi products, unit_types, universal_search.

**Gợi ý thêm trên staging:** Nếu vẫn nhiều failed do deadlock, giảm số job chạy đồng thời (vd. `--max-jobs=10` hoặc 20) khi poll import.

### 8.5. File và tài liệu tham chiếu

| Nội dung                                     | File / tài liệu                                                                          |
| -------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Phân tích chi tiết import chậm + bulk insert | FUNC_IMPROVE/08_CLIENT_IMPORT_REVIEW_AND_IMPROVEMENTS.md                                    |
| Chunk vs bulk insert, queue sync/database    | FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md                                      |
| Cột file Miaolin vs DB/Custom                | (đã gộp) xem `FUNC_LOGIC/MAOLIN_MASTER_GUIDE.md` + `FUNC_LOGIC/MAOLIN_IMPORT_MAPPING.md` |

---

## 9. Trường import: DB vs Custom (khuyến nghị giữ hiện trạng)

**Áp dụng cho file:** Miaolin Customer / Miaolin Product Customer (cấu trúc cột tương tự). **Tham chiếu:** `MAOLIN_MASTER_GUIDE.md` và `MAOLIN_IMPORT_MAPPING.md`.

### 9.1. Trường nên ở DB (đã có – không đổi)

Các trường dùng cho nghiệp vụ cốt lõi: định danh, tìm kiếm, trùng lặp, địa chỉ, liên hệ, thuế. **Không** cần thêm cột DB mới.

| Trường hệ thống          | Bảng           | Cột file tương ứng (vd. Miaolin)  | Bắt buộc import?             |
| ------------------------ | -------------- | --------------------------------- | ---------------------------- |
| name                     | users          | 客戶簡稱 \| Customer Short Name   | **Có** (required)            |
| client_code              | client_details | 客戶代號 \| Customer Code         | Nên có (unique/company)      |
| company_name             | client_details | Có thể = 客戶簡稱 hoặc trống      | Không                        |
| email                    | users          | (File có thể không có)            | Không                        |
| mobile                   | users          | TEL_NO(一) hoặc (二)              | Không                        |
| office                   | client_details | TEL_NO(二) — điện thoại văn phòng | Không                        |
| address                  | client_details | 送貨地址 \| Delivery Address      | Không (quan trọng giao hàng) |
| city, state, postal_code | client_details | (Nếu file có)                     | Không                        |
| gst_number               | client_details | 統一編號 \| Tax ID                | Không                        |

### 9.2. Trường chỉ cần Custom Field (giữ như hiện tại)

Các trường nghiệp vụ đặc thù công ty, **không** đưa vào bảng users/client_details; giữ trong **custom_fields_data** (Custom Field Group "Client"). ClientImport và ClientImportProcessor đã map đủ.

| Trường custom (name)  | Cột file (vd. Miaolin)               | Ghi chú                                                     |
| --------------------- | ------------------------------------ | ----------------------------------------------------------- |
| salesperson           | 業務員 \| Salesperson                | ✅ Đã có trong ClientImport                                 |
| department            | 部門 \| Department                   | ✅ Đã có                                                    |
| sales_assistant_name  | 業務助理名稱 \| Sales Assistant Name | ✅ Đã có                                                    |
| customer_grade        | 客戶(集團)分級 \| Customer Grade     | ✅ Đã có (ClientImport::fields + getClientCustomFieldNames) |
| channel_type          | 通路別 \| Channel Type               | ✅ Đã có                                                    |
| business_type         | 型態別 \| Business Type              | ✅ Đã có                                                    |
| last_transaction_at   | 最近交易 \| Last Transaction Date    | ✅ Đã có                                                    |
| payment_terms         | 交易條件 \| Payment Terms            | ✅ Đã có                                                    |
| business_closure_date | 歇業日期 \| Business Closure Date    | ✅ Đã có (có giá trị → set User.status = inactive)          |

### 9.3. Cột file khác (đã map hoặc tùy chọn)

| Cột file / trường                    | Trạng thái    | Ghi chú                                                                                                                                         |
| ------------------------------------ | ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| **客戶(集團)分級 \| Customer Grade** | ✅ **Đã map** | custom field `customer_grade` đã có trong ClientImport::fields() và getClientCustomFieldNames(); không nhầm với pricing_tier_id (Tier Pricing). |

### 9.4. Kết luận

- **Không** cần thêm cột DB mới cho các cột hiện có trong file Miaolin Product Customer / Miaolin Customer. Cấu trúc hiện tại (name, client_code, address, office, gst_number… trên DB; salesperson, department, payment_terms… trên custom field) là **hợp lý** và nên giữ.
- Cải thiện chính cho file ~17k dòng: **bulk insert custom_fields_data** + cache metadata trong chunk + tăng chunk size sau khi bulk insert; không thay đổi cách phân chia DB vs Custom.

### 9.5. Cột `client_details.mobile` và `client_details.office_phone` – không sử dụng (legacy)

**Đã kiểm tra toàn bộ dự án:** Không có code nào đọc hoặc ghi `client_details.mobile` hoặc `client_details.office_phone`.

| Cột                           | Trạng thái    | Ghi chú                                                                                                               |
| ----------------------------- | ------------- | --------------------------------------------------------------------------------------------------------------------- |
| `client_details.mobile`       | Không sử dụng | Cột dư từ migration SaaS; app dùng `users.mobile` cho SĐT di động. Model ClientDetails `$fillable` không có `mobile`. |
| `client_details.office_phone` | Không sử dụng | Cột dư; app dùng `client_details.office` cho SĐT văn phòng. `$fillable` không có `office_phone`.                      |

**Mapping thực tế:**

| Dữ liệu       | Bảng / cột              | Form, Import, DataTable                                                |
| ------------- | ----------------------- | ---------------------------------------------------------------------- |
| SĐT di động   | `users.mobile`          | ClientController store/update, ClientImportProcessor, ClientsDataTable |
| SĐT văn phòng | `client_details.office` | ClientImportProcessor (company_phone → office), form edit client       |

**Migration:** `2018_02_01_000000_create_craveva_saas_upgrade_fix_table.php` đã drop rồi add lại `mobile`, `office_phone` vào `client_details`. Các cột này tồn tại trong DB nhưng luôn NULL vì app không ghi vào. Có thể tạo migration `dropColumn(['mobile','office_phone'])` nếu muốn dọn schema.

### 9.6. Ảnh hưởng khi khách hàng xóa hoặc thêm custom field

Import client dùng **danh sách tên custom field cố định** trong code (`ClientImportProcessor::getClientCustomFieldNames()` và `ClientImport::fields()`). Khi admin xóa hoặc thêm custom field trong Settings (Client group):

| Tình huống                                                      | Ảnh hưởng                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Xóa custom field** (vd. xóa "salesperson" khỏi nhóm Client)   | **Không gây lỗi.** Trong `saveCustomFieldsFromRow()` ta load CustomField theo group + `whereIn('name', $customNames)`. Field đã xóa không còn trong DB → `$fields->get($name)` null → bỏ qua, không ghi. Cột tương ứng trong file import sẽ **không được ghi** vào `custom_fields_data` (đúng với việc field không còn dùng). Dữ liệu cũ đã lưu với custom_field_id đó có thể thành "orphan" tùy chính sách khi xóa custom field (có xóa luôn custom_fields_data hay không).                                                               |
| **Thêm custom field mới** (vd. thêm "region" trong nhóm Client) | **Field mới chưa được import.** Danh sách trong code không có tên mới → không map cột file vào field đó. Để import được: (1) thêm tên vào `getClientCustomFieldNames()`, (2) thêm cột (id + name) vào `ClientImport::fields()` để user map cột Excel. **Hoặc** sau này có thể đổi sang cách **động**: load toàn bộ CustomField của group Client từ DB (theo company), dùng danh sách đó thay cho hardcode → mọi custom field admin thêm sẽ tự có thể map và import (cần đảm bảo form import có cách hiển thị/chọn cột cho các field động). |

**Tóm tắt:** Xóa custom field → an toàn, chỉ không ghi field đó khi import. Thêm custom field mới → mặc định không import được; cần cập nhật code (hoặc làm dynamic theo DB) thì mới map được.

---

## 10. Import: xử lý khi client_code trùng với dữ liệu cũ

Khi một dòng trong file import có **client_code** trùng với client đã tồn tại (cùng `company_id`), hệ thống cần chọn một trong các cách xử lý sau.

### 10.1. Cách xử lý hiện tại (đã triển khai – Update khi trùng)

| Hành vi               | Mô tả                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Trùng client_code** | `ClientImportProcessor::processRow()` tìm thấy `ClientDetails` cùng company + client_code → gọi `updateExistingClient()` để **cập nhật** client đó theo dữ liệu dòng file.                                                                                                                                                                                                                                                                        |
| **Cập nhật**          | Cập nhật `users` (name, email, mobile, gender, country_id), `client_details` (company_name, address, city, state, postal_code, office, website, gst_number), custom fields (gồm customer_grade — khi không dùng bulk thì qua `saveCustomFieldsFromRow`, khi dùng bulk thì qua bulk insert trong job); business_closure_date có giá trị → `User.status = 'inactive'`; xóa và tạo lại `universal_search`. Không tạo user_auth/role/permissions mới. |
| **Trong chunk job**   | `processRow()` trả về `User` (created hoặc updated) → chunk job coi dòng thành công.                                                                                                                                                                                                                                                                                                                                                              |
| **Kết quả**           | Dòng được xử lý: client mới thì tạo; client_code trùng thì **cập nhật** dữ liệu từ file.                                                                                                                                                                                                                                                                                                                                                          |

**Code tham chiếu:** `ClientImportProcessor::processRow()` — khi có `$existingDetails` theo client_code thì `return self::updateExistingClient(...)`. `ClientImportProcessor::updateExistingClient()` — cập nhật user, client_details, saveCustomFieldsFromRow, business_closure_date → inactive, UniversalSearch delete + logSearchEntry.

### 10.2. Các lựa chọn khác (Skip / Fail)

| Cách     | Mô tả                                                 | Ghi chú                                                          |
| -------- | ----------------------------------------------------- | ---------------------------------------------------------------- |
| **Skip** | Trùng client_code → bỏ qua dòng, không cập nhật.      | Có thể bật lại bằng tùy chọn trên form import (chưa triển khai). |
| **Fail** | Trùng client_code → throw Exception, dòng vào failed. | Dùng khi bắt buộc file không được chứa client_code đã tồn tại.   |

### 10.3. Khuyến nghị

- **Hiện tại:** Đã triển khai **Update** khi trùng client_code — phù hợp khi file là nguồn master (vd. export ERP rồi import lại) để vừa tạo mới vừa đồng bộ client cũ.
- Nếu sau này cần **Skip** khi trùng: thêm tùy chọn trên form import và truyền vào job/processor, trong `processRow()` khi `$duplicateByClientCode` và option = Skip thì `return null` thay vì gọi `updateExistingClient()`.

### 10.4. Chi tiết đã triển khai (updateExistingClient)

- Lấy `$user = $existingDetails->user`, `$clientDetails = $existingDetails`.
- Cập nhật `$user`: name, email, mobile, gender, country_id (không tạo user_auth mới).
- Cập nhật `$clientDetails`: company_name, address, city, state, postal_code, office, website, gst_number; **không** đổi client_code.
- Custom field: khi **bulk insert** (chunk job) thì processRow gọi với `skip_custom_fields => true`, job xóa sẵn custom_fields_data theo model_id rồi gom và insert cuối chunk; khi **không** dùng bulk thì gọi `saveCustomFieldsFromRow()` — gồm customer_grade và các custom field khác.
- Nếu có business_closure_date → set `$user->status = 'inactive'` và save.
- Xóa bản ghi `universal_search` (searchable_id = user.id, module_type = 'client', company_id); tạo lại bằng `logSearchEntry` (name, email nếu có, company_name nếu có).
- Return `$user` để chunk job coi dòng thành công.
