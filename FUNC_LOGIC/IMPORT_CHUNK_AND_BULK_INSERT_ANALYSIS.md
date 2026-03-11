# Import: Chunk 10 vs Bulk insert – Có cải thiện không?

**Ngữ cảnh:** Client import chậm do mỗi dòng gọi `updateCustomFieldData` → nhiều query (findOrFail, select, insert/update) **cho từng custom field**. Product import không ghi custom field nên không bị phần này.

---

## 1. Chỉnh “mỗi chunk 10” (10 dòng/job) – Cải thiện hạn chế

**Ý:** Giảm chunk size từ 20 xuống 10 → mỗi job chỉ xử lý 10 dòng.

| Hiện tại (chunk 20)          | Chunk 10                     |
| ---------------------------- | ---------------------------- |
| 1000 dòng = 50 job           | 1000 dòng = 100 job          |
| Mỗi job: 20 × (queries/dòng) | Mỗi job: 10 × (queries/dòng) |

**Tác dụng:**

- **Ưu:** Mỗi job nhẹ hơn → mỗi lần poll (`queue:work --max-jobs=50`) ít bị timeout hơn (mỗi request xử lý 50 job nhưng mỗi job ngắn hơn).
- **Nhược:** Tổng số query **không đổi** (1000 dòng vẫn 1000 × N query/dòng). Số job tăng gấp đôi → overhead queue (serialize, dispatch, run) tăng → tổng thời gian có thể **không giảm**, thậm chí tăng.

**Kết luận:** Chunk 10 giúp **giảm rủi ro timeout** mỗi request, **không** giải quyết gốc (quá nhiều query/dòng). Không phải giải pháp chính để tăng tốc.

---

## 2. Bulk insert – Cải thiện rõ rệt (nên làm)

**Ý:** Trong một chunk, **không** gọi `updateCustomFieldData` từng dòng từng field (N query), mà **gom** toàn bộ dữ liệu custom field của chunk rồi **insert một lần** (hoặc vài batch) vào `custom_fields_data`.

### 2.1. Hiện tại (từng dòng, từng field)

- Mỗi dòng: 1 User + 1 ClientDetails + 1 role attach + `saveCustomFieldsFromRow` → `updateCustomFieldData($data)`.
- Trong `updateCustomFieldData`: **mỗi field** trong `$data`:
    - `CustomField::findOrFail($id)` → 1 query
    - Có thể `Company::findOrFail` → 1 query
    - `DB::table('custom_fields_data')->where(...)->first()` → 1 query
    - `insert` hoặc `update` → 1 query  
      → 20 dòng × 5 field ≈ **400 query** chỉ cho custom field trong 1 job.

### 2.2. Cơ chế bulk insert đề xuất (trong 1 chunk job)

1. **Load metadata 1 lần/chunk:** Lấy `CustomFieldGroup` + danh sách `CustomField` (id, name, type) một lần, cache trong job.
2. **Xử lý từng dòng như cũ** cho phần User + ClientDetails + role (giữ nguyên, vì có logic auth/role/universal search).
3. **Không gọi** `updateCustomFieldData` ngay mỗi dòng. Thay vào đó:
    - Với mỗi dòng đã tạo xong `ClientDetails`, build mảng `['field_id' => value, ...]` từ row (giống hiện tại).
    - Chuẩn hóa value (date, file, …) trong memory (dùng cùng logic trong trait, nhưng không ghi DB).
    - Đẩy vào mảng chung: `$bulkRows[] = ['model' => ..., 'model_id' => $clientDetails->user_id (hoặc id tùy model), 'custom_field_id' => $id, 'value' => $value]`.
4. **Sau khi xử lý hết dòng trong chunk:** Gọi **một lần** `DB::table('custom_fields_data')->insert($bulkRows)` (có thể chia batch 100–500 row/lần nếu DB giới hạn).

**Lưu ý:** Bảng `custom_fields_data` thường không có unique (model, model_id, custom_field_id) cho import mới → chỉ cần `insert`. Nếu có ràng buộc unique thì phải dùng `insertOrIgnore` hoặc `upsert` tùy schema.

### 2.3. So sánh query (ước lượng cho 1 chunk 20 dòng, 5 custom field có giá trị)

|                                    | Hiện tại                                                                                           | Bulk insert                     |
| ---------------------------------- | -------------------------------------------------------------------------------------------------- | ------------------------------- |
| CustomFieldGroup + CustomField     | 2 query × 20 (mỗi dòng gọi saveCustomFieldsFromRow, mỗi lần 2 query) hoặc đã cache trong processor | 2 query **1 lần** (1 lần/chunk) |
| CustomField::findOrFail từng field | 5 × 20 = 100 query                                                                                 | **0** (dùng map id từ bước 1)   |
| Select/Insert custom_fields_data   | 20 × 5 × 2 = 200 query                                                                             | **1** (hoặc vài batch insert)   |
| **Tổng (phần custom field)**       | **~300+**                                                                                          | **~3–5**                        |

→ **Cải thiện rất lớn** cho phần custom field; thời gian import client (có nhiều custom field) sẽ giảm rõ.

---

## 3. Các cách giải quyết khác ngoài bulk insert

| Cách                                            | Mô tả                                                                                                                                                                                                                                      | Cải thiện                                                      | Nhược / lưu ý                                                                                                                   |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| **Cache metadata trong job**                    | Trong chunk job, load **một lần** CustomFieldGroup + toàn bộ CustomField (id, name, type) của Client; truyền map này vào logic lưu thay vì gọi `CustomField::findOrFail($id)` từng field.                                                  | Giảm ~100 query/chunk (bỏ findOrFail).                         | Vẫn còn select + insert/update **từng field từng dòng** trong `updateCustomFieldData` → số query vẫn lớn, chỉ đỡ phần metadata. |
| **Tùy chọn “Bỏ qua custom field”**              | Thêm option trên form import: “Không import custom field”. Nếu bật thì không gọi `saveCustomFieldsFromRow` / `updateCustomFieldData`.                                                                                                      | Bỏ hết query custom field → import nhanh.                      | User phải sửa/sync custom field sau (tay hoặc import riêng). Phù hợp khi chỉ cần tạo client nhanh.                              |
| **Custom field chạy sau (async)**               | Import chỉ ghi users + client_details + role (+ universal_search). Sau khi chunk xong, dispatch job phụ (1 job/chunk hoặc 1 job/client) để điền custom_fields_data.                                                                        | Cảm giác import “xong” nhanh; công việc custom field trải ra.  | Tổng query không giảm, chỉ trì hoãn; cần queue worker ổn định; UI cần thể hiện “đang đồng bộ custom field”.                     |
| **Giảm round-trip trong updateCustomFieldData** | Với **một client**: (1) một query lấy tất cả row hiện có trong custom_fields_data (where model, model_id); (2) trong PHP phân biệt insert vs update; (3) một insert nhiều dòng cho bản ghi mới; (4) một hoặc vài update cho bản ghi đã có. | Từ 4×N query/client xuống còn 2–4 query/client (N = số field). | Vẫn phải sửa trait hoặc tạo method mới; cải thiện đáng kể nhưng kém hơn bulk insert cả chunk.                                   |
| **Upsert (INSERT ... ON DUPLICATE KEY UPDATE)** | Nếu bảng `custom_fields_data` có unique (model, model_id, custom_field_id): gom toàn bộ row trong chunk, gọi một lần `upsert()` (Laravel).                                                                                                 | Tương đương bulk insert: 1–2 query/chunk cho custom field.     | Phụ thuộc schema có unique; hành vi trùng lặp cần định nghĩa rõ (update hay bỏ qua).                                            |

**Tóm tắt:** Ngoài **bulk insert** (khuyến nghị nhất), có thể kết hợp hoặc dùng riêng: **cache metadata** (giảm bớt query), **bỏ qua custom field** (đơn giản, nhanh nếu không cần), **async** (trải tải), **batch per client** (giảm round-trip), **upsert** (nếu schema hỗ trợ).

---

## 4. Chia file Excel nhiều sheet (vd. 17 000 dòng → 17 sheet × 1 000 dòng) – có cải thiện?

**Câu hỏi:** Chia dữ liệu thành nhiều sheet (mỗi sheet 1000 dòng) có giúp cải thiện chức năng import không?

### 4.1. Cách import hiện tại đọc file

- Code dùng **Maatwebsite Excel** với `ToArray` (vd. `ClientImport`). Mặc định chỉ đọc **sheet đầu tiên** (first worksheet).
- Toàn bộ dữ liệu sheet đó được load vào một mảng `$excelData`, rồi mới `array_chunk()` và dispatch job.

### 4.2. Nếu chỉ chia sheet, không sửa code

| Tình huống                         | Kết quả                                                                                      |
| ---------------------------------- | -------------------------------------------------------------------------------------------- |
| File 1 sheet 17 000 dòng           | Import đủ 17 000 dòng; một lần load 17 000 dòng vào memory → dễ chạm **memory limit** (PHP). |
| File 17 sheet mỗi sheet 1 000 dòng | Chỉ **sheet đầu tiên** được đọc → chỉ import **1 000 dòng**; 16 sheet còn lại **bị bỏ qua**. |

→ Chia nhiều sheet **không** tự động cải thiện tốc độ; nếu không đổi code thì còn **giảm** số dòng được import (chỉ 1 sheet).

### 4.3. Nếu sửa code để đọc từng sheet (sheet-by-sheet)

- Đọc **lần lượt từng sheet** (sheet 1 → xử lý → sheet 2 → …), mỗi lần chỉ giữ 1 sheet trong memory (vd. 1 000 dòng), rồi chunk và dispatch job cho sheet đó; sau đó chuyển sang sheet tiếp theo.
- **Lợi ích:** Giảm **peak memory** (tránh lỗi memory limit khi file rất lớn). Có thể import đủ 17 000 dòng từ 17 sheet.
- **Không thay đổi:** Tổng số dòng, tổng số job, tổng query DB (custom field, …) vẫn như cũ → **tốc độ xử lý (thời gian chạy)** gần như không cải thiện, chủ yếu cải thiện **ổn định** (không sập do memory).

### 4.4. Kết luận (chia nhiều sheet)

| Mục tiêu                  | Chia nhiều sheet (không sửa code) | Chia nhiều sheet + sửa đọc từng sheet |
| ------------------------- | --------------------------------- | ------------------------------------- |
| Import đủ 17 000 dòng     | Không (chỉ 1 sheet = 1 000 dòng)  | Có                                    |
| Giảm memory               | Không                             | Có (peak memory thấp hơn)             |
| Tăng tốc (giảm thời gian) | Không                             | Gần như không (cùng khối lượng xử lý) |

**Khuyến nghị:** Chia nhiều sheet **chỉ hữu ích** khi kèm **thay đổi code** đọc từng sheet để (1) import đủ tất cả sheet và (2) tránh vượt memory. Muốn **tăng tốc** thật sự vẫn cần **bulk insert** (và/hoặc bỏ qua custom field, cache metadata) như mục 2 và 3.

---

## 5. Kết luận và hướng làm

| Hướng                              | Cải thiện                        | Ghi chú                                   |
| ---------------------------------- | -------------------------------- | ----------------------------------------- |
| **Chunk 10 (10 dòng/job)**         | Ít, chủ yếu giảm timeout/request | Không giảm tổng số query.                 |
| **Bulk insert custom_fields_data** | Rất lớn                          | Giảm mạnh số query/chunk; nên triển khai. |

**Đề xuất:**

1. **Không** đổi chunk xuống 10 chỉ để “cải thiện tốc độ” – chỉ nên giảm chunk nếu muốn giảm timeout từng request.
2. **Ưu tiên:** Bulk insert custom_fields_data (mục 2) – giảm query nhiều nhất, hành vi giữ nguyên.
3. **Nếu chưa làm bulk insert**, có thể áp dụng tạm: cache metadata (mục 3) +/ hoặc tùy chọn “Bỏ qua custom field” (mục 3).
4. Khi triển khai bulk insert trong chunk job (client, và sau này product nếu có ghi custom field):
    - Trong job: load group + list CustomField **một lần**;
    - Mỗi dòng: vẫn tạo User/ClientDetails (và role) như cũ, nhưng **không** gọi `updateCustomFieldData`;
    - Gom toàn bộ cặp (model, model_id, custom_field_id, value) đã chuẩn hóa vào mảng;
    - Cuối chunk: **một (hoặc vài) lần** `DB::table('custom_fields_data')->insert($bulkRows)`.

Như vậy vừa giữ đúng hành vi (custom field vẫn gắn đúng client), vừa giảm mạnh số query và tăng tốc import khi có nhiều custom field.
