# Phân tích Import sản phẩm chậm – Nguyên nhân và Giải pháp

**Tham chiếu:** FUNC_LOGIC/FLOW_ADD_PRODUCT.md, FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md

---

## 1. Tổng quan flow import hiện tại

1. User upload file → `ProductController::importStore()` → `importFileProcess()` đọc file 2 lần (heading + data).
2. User map cột và Submit → `ProductController::importProcess()` → `importJobProcessChunked()`.
3. File được load **toàn bộ** vào memory qua `Excel::import()`, chunk theo `chunkSize` (mặc định **100**), mỗi chunk = 1 job.
4. Jobs dispatch vào queue `ProductImport` (database), worker xử lý từng job.
5. Frontend **poll** progress mỗi 2 giây → user phải giữ trang mở ("Do not close or refresh").

---

## 2. Nguyên nhân chậm

### 2.1. Chunk size (đã tăng lên 100)

| Số sản phẩm    | Số job (chunk 100) | Overhead                              |
| -------------- | ------------------ | ------------------------------------- |
| 500            | 5                  | 5 lần serialize/dispatch/process      |
| 2463 (Miaolin) | 25                 | 25 job, mỗi job ~100 INSERT + N query |
| 5000           | 50                 | Ít job hơn chunk 20/30                |

- Mỗi job có overhead: deserialize, load company context, xử lý, commit.
- **Đã áp dụng:** default chunk **100** (ProductController::importProcess, PurchaseProductController). Override qua request `chunk_size`.

**Tham khảo IMPORT_CHUNK_AND_BULK_INSERT.md:** Chunk nhỏ không giảm tổng query, chỉ tăng overhead; tăng chunk giảm số job.

---

### 2.2. Query nhiều mỗi dòng

| Thao tác                         | Số query/dòng | Ghi chú                                                                                             |
| -------------------------------- | ------------- | --------------------------------------------------------------------------------------------------- |
| Kiểm tra SKU trùng               | 0 (cache)     | **Đã cache:** 1 query/chunk load SKU, lookup O(1) trong memory.                                     |
| `$product->save()`               | 1 INSERT      | Bắt buộc                                                                                            |
| `resolveUnitId()`                | 0–1           | Có cache `unitTypeCache`                                                                            |
| `resolveCategoryId()`            | 0–1           | Có cache `categoryCache`                                                                            |
| `resolveSubCategoryId()`         | 0–1           | Có cache `subCategoryCache`                                                                         |
| `buildProductCustomFieldsData()` | 0             | Hiện trả về [] (không ghi custom field khi import); product_grade, product_source, brand là cột DB. |
| `updateCustomFieldData()`        | 0             | Không gọi khi import (custom field data rỗng).                                                      |
| `createEmployeeActivity()`       | 1             | INSERT employee_activity                                                                            |

**Ví dụ (hiện tại, không ghi custom field):** 1 product → ~1 INSERT + 0–1 unit + 0–1 category + 0–1 sub_category + 1 activity ≈ **4–5 query/dòng**. 100 dòng/chunk ≈ **400–500 query/job** (đã bỏ query custom field và SKU exists).

---

### 2.3. Custom field – chưa bulk insert

- `buildProductCustomFieldsData()` gọi **mỗi dòng** → query CustomFieldGroup + CustomField lặp lại.
- `updateCustomFieldData()` xử lý **từng field từng dòng** → nhiều round-trip DB.

**Tham khảo IMPORT_CHUNK_AND_BULK_INSERT.md (mục 2, 3):** Bulk insert custom field giảm mạnh số query (từ ~300/chunk xuống ~5/chunk).

---

### 2.4. Load file Excel 2 lần

- `importFileProcess()`: `Excel::import()` lần 1 (preview) + có thể `HeadingRowImport` thêm.
- `importJobProcessChunked()`: `Excel::import()` lần 2 (xử lý).
- File lớn (2000+ dòng) → PhpSpreadsheet chậm, tốn memory.

---

### 2.5. Queue worker và cấu hình

- Queue mặc định: `env('QUEUE_CONNECTION', 'sync')`.
- Nếu `sync`: job chạy **ngay trong request** → HTTP timeout, blocking.
- Nếu `database`: cần `php artisan queue:work --queue=ProductImport` chạy liên tục.
- `retry_after` = 90s; job lớn có thể timeout.

---

### 2.6. Thông báo UI

- "Do not close or refresh" – đúng vì cần giữ trang để poll progress.
- `messages.importRunningInBackground` – nếu chưa translate đúng locale có thể hiển thị key thô.

---

## 3. Giải pháp đề xuất (theo độ ưu tiên)

### 3.1. Tăng chunk size — ✅ Đã triển khai

**Đã áp dụng:** `$chunkSize = 100` (ProductController::importProcess, PurchaseProductController). Có thể override qua request `chunk_size`.

- 2463 products: 25 job (thay vì 124 với chunk 20, 83 với chunk 30).
- Giảm overhead queue.

---

### 3.2. Cache SKU đã tồn tại trong chunk — ✅ Đã triển khai

**Đã áp dụng:** Đầu mỗi chunk (ImportProductChunkJob::handle()), load toàn bộ SKU của company một lần; trong processRow() dùng `isset($this->existingSkus[$sku])`; sau khi tạo product mới thì gán `$this->existingSkus[$sku] = true` để tránh trùng trong cùng chunk.

**Code tham khảo (đã có trong job):**

```php
// Đầu handle(), sau khi set company:
$existingSkus = Product::where('company_id', $this->company->id)
    ->whereNotNull('sku')
    ->pluck('sku')
    ->flip()
    ->all(); // O(1) lookup

// Trong processRow, thay vì exists():
if ($skuTrimmed && isset($existingSkus[$skuTrimmed])) return false;
$existingSkus[$skuTrimmed] = true; // Sau khi tạo xong
```

- Giảm N query xuống 1 query/chunk. **Đã triển khai.**

**Ảnh hưởng khi dữ liệu SKU lên 2000+?**

| Số SKU trong DB (company) | Bộ nhớ (array PHP) | Lookup `isset($arr[$sku])` | Kết luận                                                                                |
| ------------------------- | ------------------ | -------------------------- | --------------------------------------------------------------------------------------- |
| 2 000                     | ~50–200 KB         | O(1), vài μs/lần           | Không ảnh hưởng hiệu suất.                                                              |
| 20 000–50 000             | ~1–3 MB            | O(1)                       | Vẫn ổn.                                                                                 |
| 100 000+                  | ~5–10 MB           | O(1)                       | Chấp nhận được; nếu nhiều company rất lớn có thể chỉ load SKU có trong file (xem dưới). |

- **1 query** `pluck('sku')` trả về 2000 dòng: MySQL xử lý tốt, một round-trip.
- Lookup trong array/hash PHP là **O(1)** nên 2000 hay 20 000 key cũng gần như cùng thời gian.
- So với **2000 query** `exists()` (mỗi dòng 1 query), cache 2000 SKU vẫn **lợi rất lớn** về cả thời gian lẫn tải DB.

**Nếu company có hàng trăm nghìn product:** có thể chỉ load SKU trùng với file import (lấy danh sách SKU trong chunk, query `WHERE sku IN (...)` một lần) để giảm memory; với 2000–50 000 SKU thì load hết SKU company vẫn là cách đơn giản và nhanh.

**Ghi chú thêm về cache SKU (đã triển khai):**

| Nội dung                      | Mô tả                                                                                                                                                                                                                                |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Cache nằm ở đâu?**          | Trên **server** (trong memory của process PHP chạy queue worker). Không lưu trên máy người dùng (cookie, localStorage, v.v.).                                                                                                        |
| **Khi nào cache mất?**        | Ngay **sau khi xử lý xong 1 chunk** (1 job). Hết job → object job bị giải phóng → cache mất. Không phải đợi đến khi toàn bộ import xong.                                                                                             |
| **Mỗi chunk có cache riêng?** | Có. Job 1 xử lý dòng 1–30, tạo cache, dùng xong, job kết thúc → cache mất. Job 2 xử lý dòng 31–60, tạo **cache mới** (load lại từ DB), dùng xong, cache mất.                                                                         |
| **Mỗi job load SKU thế nào?** | **1 job = 1 query.** Khi job bắt đầu, gọi 1 lần `Product::where(...)->pluck('sku')` để lấy **toàn bộ** SKU của company phục vụ cho chunk đó. Các dòng trong chunk chỉ dùng `isset($this->existingSkus[$sku])`, không gọi thêm query. |
| **Tại sao nhanh hơn trước?**  | **Trước:** mỗi dòng 1 query `exists()` → 30 dòng = 30 query/chunk. **Sau:** 1 query đầu chunk + 30 lần lookup trong memory → **1 query/chunk.** Ví dụ 2463 sản phẩm (~83 chunk): trước ~2490 query chỉ cho SKU, sau ~83 query.       |

---

### 3.3. Cache CustomField metadata và bulk insert — ❌ Chưa triển khai (không cần khi không ghi custom field)

**Hiện tại:** Product import **không ghi** custom field (buildProductCustomFieldsData trả về []); product_grade, product_source, brand là cột DB → không phát sinh query custom field.

**Nếu sau này bật ghi custom field khi import:** Nên áp dụng (theo IMPORT_CHUNK_AND_BULK_INSERT.md):

1. **Load 1 lần/chunk:** CustomFieldGroup + CustomField (trừ product_grade, product_source, brand).
2. **Mỗi dòng:** Chỉ build mảng `['field_id' => value]` trong memory, **không** gọi `updateCustomFieldData`.
3. **Cuối chunk:** Gom tất cả row vào `$bulkRows`, gọi `DB::table('custom_fields_data')->insert($bulkRows)` (có thể chia batch 100–500).

- Giảm từ ~14 query/product xuống ~1–2 query/chunk cho custom field.

---

### 3.4. Tùy chọn "Bỏ qua custom field" khi import — ✅ Hiệu quả tương đương

- Product import hiện **không ghi** custom field (product_grade, product_source, brand là cột DB; custom field khác không đưa vào import).
- Không cần checkbox: không gọi `updateCustomFieldData` vì `buildProductCustomFieldsData()` trả về [].
- Nếu sau này thêm custom field cho Product và muốn import → cần mở lại logic + cân nhắc bulk insert (IMPORT_CHUNK_AND_BULK_INSERT.md §6).

---

### 3.5. Kiểm tra queue và worker

- `.env`: `QUEUE_CONNECTION=database` (không dùng `sync` cho import).
- Đảm bảo worker chạy: `php artisan queue:work --queue=ProductImport --tries=3`.
- Có thể tăng `retry_after` nếu chunk lớn (vd. 180).

---

### 3.6. Giảm load file Excel

- Chỉ đọc file **1 lần** trong `importFileProcess`, lưu data vào session/cache tạm; `importJobProcessChunked` lấy từ đó thay vì đọc lại.
- Hoặc dùng `Maatwebsite\Excel\Concerns\WithChunkReading` để đọc từng chunk thay vì load hết vào memory (cần refactor flow).

---

### 3.7. Cải thiện UX (không tăng tốc nhưng giảm cảm giác chờ)

- Giảm interval poll: 2000ms → 1000ms (nếu server chịu được).
- Hiển thị số dòng đã xử lý / tổng rõ ràng hơn.
- Thêm/bổ sung translation cho `messages.importRunningInBackground` đúng locale.

---

## 4. Kế hoạch triển khai / Trạng thái

| Bước | Hành động                                                   | Trạng thái     | Ghi chú                                                               |
| ---- | ----------------------------------------------------------- | -------------- | --------------------------------------------------------------------- |
| 1    | Tăng chunk size 20 → 100                                    | ✅ Hoàn thành  | ProductController, PurchaseProductController; override qua chunk_size |
| 2    | Cache SKU trong chunk                                       | ✅ Hoàn thành  | ImportProductChunkJob: 1 query/chunk, O(1) lookup                     |
| 2b   | Cache unit type + auto-create unit khi thiếu                | ✅ Hoàn thành  | ImportProductChunkJob: unitTypeCache, createUnitType()                |
| 2c   | Cache category / sub_category trong chunk                   | ✅ Hoàn thành  | categoryCache, subCategoryCache                                       |
| 3    | Cache CustomField metadata + bulk insert custom_fields_data | ❌ Chưa        | Hiện không ghi custom field khi import; cần khi bật lại               |
| 4    | Bỏ qua custom field khi import (hiệu quả)                   | ✅ Đạt được    | Không ghi custom field → không query custom field                     |
| 5    | Kiểm tra QUEUE_CONNECTION và worker                         | Tùy môi trường | Tránh sync/timeout                                                    |

---

## 5. Tóm tắt

**Đã triển khai (3.1, 3.2, 3.4 + bổ sung):**

1. **Chunk size 100** – ProductController::importProcess, PurchaseProductController; override qua request `chunk_size`.
2. **Cache SKU** – ImportProductChunkJob: load SKU company 1 lần/chunk, lookup O(1), cập nhật cache khi tạo product mới.
3. **Cache unit type** – unitTypeCache trong chunk; auto-create unit nếu chưa có trong DB.
4. **Cache category / sub_category** – categoryCache, subCategoryCache trong chunk.
5. **product_grade, product_source, brand = cột DB** – không ghi qua custom_fields_data.
6. **Không ghi custom field khi import** – buildProductCustomFieldsData trả về [] → hiệu quả tương đương “bỏ qua custom field”.

**Chưa làm (chỉ cần khi bật ghi custom field khi import):**

1. Custom field: bulk insert (IMPORT_CHUNK_AND_BULK_INSERT.md §2, §6).
2. Đảm bảo queue database + worker chạy đúng (môi trường).
