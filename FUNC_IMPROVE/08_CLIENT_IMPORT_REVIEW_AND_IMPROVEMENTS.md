# Kiểm tra chức năng Import Client & Cải thiện tốc độ

## Trạng thái rà soát (2026-04-30)

- File này chứa phân tích cũ; một số mục đã thay đổi sau khi triển khai optimize mới.
- Nguồn trạng thái triển khai mới hơn nằm ở `FUNC_LOGIC/FLOW_ADD_CLIENT.md`.
- Kết luận:
    - Đánh dấu file này là **Archived analysis** (tham khảo lịch sử).
    - Không dùng file này làm checklist implementation hiện tại.

**Tham chiếu:** `FUNC_LOGIC/FLOW_ADD_CLIENT.md`, `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`, `FUNC_LOGIC/FLOW_ADD_PRODUCT.md`

---

## 1. Kiểm tra luồng Import Client (so với tài liệu)

### 1.1. Luồng thực tế trong code

| Bước | Tài liệu (FLOW_ADD_CLIENT) | Code thực tế                                                                        | Khớp? |
| ---- | -------------------------- | ----------------------------------------------------------------------------------- | ----- |
| 1    | user_auths (nếu có email)  | `UserAuth::createUserAuthCredentials()` trong `ClientImportProcessor::processRow()` | ✅    |
| 2    | users                      | `User::save()`                                                                      | ✅    |
| 3    | client_details             | `ClientDetails::save()`                                                             | ✅    |
| 4    | custom_fields_data         | `saveCustomFieldsFromRow()` → `updateCustomFieldData()`                             | ✅    |
| 5    | role_user                  | `$user->attachRole($role->id)`                                                      | ✅    |
| 6    | user_permissions           | `$user->assignUserRolePermission($role->id)`                                        | ✅    |
| 7    | universal_search           | `logSearchEntry()` (name, email, company_name)                                      | ✅    |

**Kết luận:** Luồng import client trong code **khớp** với FLOW_ADD_CLIENT.md. Mỗi dòng Excel → 1 transaction → đủ 7 bước (user_auths có điều kiện).

### 1.2. Chunk job và kích thước chunk

- **Job:** `ImportClientChunkJob` — nhận một mảng `$rows` (chunk), duyệt từng dòng, gọi `ClientImportProcessor::processRow()` trong transaction.
- **Chunk size:** Mặc định **20** dòng/job (`ClientController::importProcess()` — `$chunkSize = 20`), có thể override qua request `chunk_size`.
- **Queue:** `Bus::batch($jobs)->onConnection('database')->onQueue('ClientImport')` (ImportExcel::importJobProcessChunked).

---

## 2. Vấn đề Import chậm – Nguyên nhân

### 2.1. Custom field: nhiều query cho mỗi dòng

Với **mỗi dòng** client, sau khi tạo User + ClientDetails, code gọi:

- `saveCustomFieldsFromRow()`:
    - 1 query: `CustomFieldGroup::where('name','Client')->...->first()`
    - 1 query: `CustomField::where('custom_field_group_id', $group->id)->whereIn('name', $customNames)->get()`
- Rồi với **mỗi custom field có giá trị** (vd. 4–5 field), gọi `updateCustomFieldData($data)`:
    - **Mỗi field:** `CustomField::findOrFail($id)` → 1 query
    - **Mỗi field:** `DB::table('custom_fields_data')->where(...)->first()` → 1 query
    - **Mỗi field:** `insert` hoặc `update` → 1 query
    - (Nếu date) có thể thêm xử lý qua Company → thêm query

**Ước lượng cho 1 chunk 20 dòng, 5 custom field có giá trị:**

- CustomFieldGroup + CustomField: 2 query × 20 = **40** (hoặc 2 nếu cache theo chunk)
- Per field per row: 20 × 5 × (findOrFail + select + insert/update) ≈ **300** query chỉ cho custom field trong 1 job.

→ **Phần custom field chiếm phần lớn số query và thời gian** (đúng như IMPORT_CHUNK_AND_BULK_INSERT.md §1–2).

### 2.2. So sánh với Product import

| Nội dung                        | Client import                                      | Product import                                                               |
| ------------------------------- | -------------------------------------------------- | ---------------------------------------------------------------------------- |
| Custom field khi import         | **Có** — ghi qua `updateCustomFieldData` từng dòng | **Không** — không ghi custom_fields_data (buildProductCustomFieldsData = []) |
| Chunk size mặc định             | 20                                                 | 100                                                                          |
| Cache metadata (group + fields) | Load **mỗi dòng** (trong saveCustomFieldsFromRow)  | SKU, unit, category cache **1 lần/chunk**                                    |
| Nguyên nhân chậm chính          | Nhiều query custom field/dòng                      | Đã tối ưu (cache, không custom field)                                        |

---

## 3. Các cách cải thiện (theo IMPORT_CHUNK_AND_BULK_INSERT.md)

### 3.1. Bulk insert custom_fields_data (ưu tiên cao)

**Ý tưởng:** Trong một chunk job:

1. Load **một lần**: CustomFieldGroup (Client) + danh sách CustomField (id, name, type) → map name → id.
2. Với mỗi dòng: vẫn tạo User, ClientDetails, role, permissions, universal_search như hiện tại; **không** gọi `updateCustomFieldData`.
3. Với mỗi dòng: từ row + columns build mảng `['field_id' => value, ...]`, chuẩn hóa value (date, v.v.) trong memory (dùng lại logic trong CustomFieldsTrait nhưng không ghi DB).
4. Đẩy vào mảng chung: `$bulkRows[] = ['model' => ClientDetails::CUSTOM_FIELD_MODEL, 'model_id' => $clientDetails->id, 'custom_field_id' => $id, 'value' => $value]`.
5. **Sau khi xử lý hết dòng trong chunk:** gọi **một lần** (hoặc vài batch 100–500 dòng) `DB::table('custom_fields_data')->insert($bulkRows)`.

**Lưu ý:** `model_id` cho Client là **client_details.id** (đúng như FLOW_ADD_CLIENT §2.2).

**Hiệu quả:** Giảm từ ~300+ query/chunk (phần custom field) xuống còn ~3–5 query/chunk → cải thiện rất lớn.

### 3.2. Cache metadata trong chunk job

- Trong `ImportClientChunkJob::handle()`: load **một lần** CustomFieldGroup + CustomField (Client) cho company.
- Truyền map (name → id, type) vào `ClientImportProcessor::processRow()` hoặc vào hàm xử lý custom field, để **không** gọi `CustomField::findOrFail($id)` từng field.

Nếu chưa làm bulk insert thì bước này vẫn giảm đáng kể query (bỏ findOrFail từng field); nếu đã làm bulk insert thì bước này đã nằm trong thiết kế (load 1 lần/chunk).

### 3.3. Tùy chọn “Bỏ qua custom field” khi import

- Thêm option trên form import (checkbox): “Không import custom field”.
- Nếu bật: không gọi `saveCustomFieldsFromRow` / không ghi custom_fields_data.
- **Ưu:** Import nhanh, ít query. **Nhược:** User phải cập nhật custom field sau (tay hoặc import riêng).

### 3.4. Chunk size

- **Giảm chunk (vd. 10):** Giảm rủi ro timeout mỗi request, **không** giảm tổng số query → không giải quyết gốc chậm.
- **Tăng chunk (vd. 50–100):** Giảm số job, giảm overhead queue; nhưng mỗi job nặng hơn → cần cân bằng với timeout (vd. 50–100 vẫn thường ổn nếu đã bulk insert custom field).

### 3.5. Queue: sync vs database

- **database** (hiện tại): Job chạy khi poll (getImportProgress) hoặc worker; tránh timeout request, có progress.
- **sync:** Toàn bộ job chạy trong request POST → dễ timeout với file lớn.

**Khuyến nghị:** Giữ `QUEUE_CONNECTION=database` cho file lớn (vd. Miaolin ~8600 dòng).

---

## 4. So sánh với Product import (FLOW_ADD_PRODUCT, IMPORT_CHUNK_AND_BULK_INSERT §6)

| Hạng mục                 | Client import (hiện tại)                 | Product import (đã tối ưu)        |
| ------------------------ | ---------------------------------------- | --------------------------------- |
| Chunk size               | 20                                       | 100                               |
| Custom field khi import  | Có, từng dòng từng field (nhiều query)   | Không ghi custom_fields_data      |
| Cache metadata           | Mỗi dòng (group + fields)                | 1 lần/chunk (SKU, unit, category) |
| Bulk insert custom field | Chưa                                     | Không áp dụng (không ghi)         |
| Cải thiện đề xuất        | Bulk insert + cache metadata trong chunk | Đã thực hiện cache + chunk 100    |

---

## 5. Kết luận và hướng làm

1. **Luồng import client** đúng với tài liệu FLOW_ADD_CLIENT; chậm chủ yếu do **custom field**: mỗi dòng gọi `updateCustomFieldData` → nhiều query (findOrFail, select, insert/update) **cho từng field**.
2. **Cải thiện hiệu quả nhất:** **Bulk insert custom_fields_data** trong chunk job (load metadata 1 lần/chunk, gom toàn bộ row custom field trong chunk rồi insert một hoặc vài batch). Có thể tách logic chuẩn hóa value (date, v.v.) từ CustomFieldsTrait để dùng trong bulk mà không gọi trait từng dòng.
3. **Bổ sung:** Cache metadata (CustomFieldGroup + CustomField) trong chunk job, truyền vào processor để không gọi findOrFail từng field (đã nằm trong thiết kế bulk insert).
4. **Tùy chọn:** “Bỏ qua custom field” khi import để tăng tốc nhanh mà không sửa nhiều code.
5. **Không ưu tiên:** Chỉ giảm chunk xuống 10 để “tăng tốc” — không giảm tổng query; chỉ nên giảm chunk nếu cần giảm timeout từng request.
6. Giữ **queue database** cho file lớn; sau khi bulk insert có thể thử tăng chunk size (vd. 50) để giảm số job.

---

## 6. Nguồn code tham chiếu

| Thành phần          | File / method                                                                                                 |
| ------------------- | ------------------------------------------------------------------------------------------------------------- |
| Gọi import chunk    | `ClientController::importProcess()` → `importJobProcessChunked(..., ImportClientChunkJob::class, $chunkSize)` |
| Chunk job           | `App\Jobs\ImportClientChunkJob::handle()`                                                                     |
| Xử lý 1 dòng        | `App\Services\ClientImportProcessor::processRow()`                                                            |
| Custom field từ row | `ClientImportProcessor::saveCustomFieldsFromRow()` → `ClientDetails::updateCustomFieldData()`                 |
| Trait custom field  | `App\Traits\CustomFieldsTrait::updateCustomFieldData()` (findOrFail, select, insert/update từng field)        |
| Chunk size mặc định | `ClientController::importProcess()` — `$chunkSize = 20`                                                       |
