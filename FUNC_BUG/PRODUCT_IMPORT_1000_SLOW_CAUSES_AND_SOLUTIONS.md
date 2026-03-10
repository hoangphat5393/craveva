# Import 1000 sản phẩm chậm – Nguyên nhân và giải pháp đã áp dụng

**File:** `import_product full.xlsx` (khoảng 1000 dòng)

---

## 1. Nguyên nhân chậm (trước khi sửa)

| Nguyên nhân                   | Mô tả                                                                                                                                                                                                                                                                                    |
| ----------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **1 job / 1 dòng**            | 1000 dòng = 1000 job. Mỗi job: lấy từ queue, unserialize, chạy, commit. Overhead rất lớn.                                                                                                                                                                                                |
| **Giới hạn job mỗi lần poll** | `getImportProgress()` chạy `queue:work --max-jobs=50`. 1000 job → ít nhất 20 lần poll. Mỗi lần poll = 1 request HTTP xử lý 50 job.                                                                                                                                                       |
| **Timeout request**           | Mỗi request xử lý 50 job; nếu mỗi job ~0,3–0,5s thì 50 job ≈ 15–25s. PHP/nginx timeout 30–60s dễ cắt request → không trả JSON → progress không cập nhật.                                                                                                                                 |
| **Không cache lookup**        | **Category/Sub-category:** chỉ query khi có map cột đó; không map thì không query. **Unit type:** sản phẩm luôn cần `unit_id`; khi không map cột Unit type, code cũ vẫn gọi “default unit” mỗi dòng → 1000 dòng = 1000 query. Khi có map unit/category, cùng tên vẫn query lặp mỗi dòng. |
| **Poll 2 giây**               | Delay 2s giữa các lần poll cộng dồn (20 × 2s = 40s chỉ chờ).                                                                                                                                                                                                                             |

**Ước lượng:** 1000 dòng với 50 job/poll → 20 poll; thời gian xử lý 1000 job + chờ poll + rủi ro timeout → **vài phút đến >10 phút**, và có thể không thấy progress nếu request bị timeout.

---

## 2. Giải pháp đã áp dụng

### 2.1. Import theo chunk (100 dòng / 1 job)

- **Trước:** 1000 dòng = 1000 job.
- **Sau:** 1000 dòng = **10 job** (mỗi job 100 dòng).

**Code:**

- `ImportProductChunkJob`: job mới xử lý một mảng nhiều dòng trong một lần chạy.
- `PurchaseProductController::importProcess()` và `ProductController::importProcess()` gọi `importJobProcessChunked(..., ImportProductChunkJob::class, 100)` thay cho `importJobProcess(..., ImportProductJob::class)`.

**Lợi ích:**

- Giảm số job từ 1000 → 10 → ít poll hơn, ít overhead queue.
- Mỗi lần poll có thể xử lý hết 10 job trong 1 request (nếu timeout đủ lớn).

### 2.2. Cache lookup trong mỗi chunk

**Khi import không có map cột Category / Sub-category:** code không query bảng `product_category` hay `product_sub_category` (chỉ gán `category_id = null`, `sub_category_id = null`). Phần “query nhiều” trong trường hợp đó chủ yếu do **unit_type**:

- **Unit type:** Sản phẩm luôn cần `unit_id`. Nếu **không** map cột Unit type (hoặc trống/không tìm thấy), dùng **unit type đầu tiên** (theo id), chỉ query 1 lần/chunk (chunk job) hoặc 1 lần/request (job cũ, cache static).

**Khi import có map Category / Unit type:** không cache thì mỗi dòng query lại theo tên (cùng "Beverage" vẫn query 1000 lần). Trong `ImportProductChunkJob`:

- **Unit type:** cache `unit_type name → id`; khi không có/trống/không tìm thấy thì dùng unit đầu tiên (theo id), chỉ query 1 lần/chunk.
- **Category:** chỉ query khi có cột `product_category`; cache `category name → id`.
- **Sub-category:** chỉ query khi có cột `product_sub_category`; cache `(category_id|name) → id`.

Cùng unit/category trong 100 dòng chỉ query DB 1 lần → giảm mạnh số query khi có map các cột này.

### 2.3. Chuẩn hóa dòng Excel (normalize)

- `ImportExcel::importJobProcessChunked()` đã gọi `normalizeExcelRows()` để chuyển cell (Cell/RichText) thành scalar trước khi đưa vào job.
- Trong chunk job có `normalizeRow()` để tránh lỗi serialization / “separation symbol” khi xử lý từng dòng.

---

## 3. File đã thay đổi / thêm mới

| File                                                              | Thay đổi                                                                                    |
| ----------------------------------------------------------------- | ------------------------------------------------------------------------------------------- |
| `app/Jobs/ImportProductChunkJob.php`                              | **Mới.** Job xử lý chunk 100 dòng, có cache unit/category/subcategory.                      |
| `Modules/Purchase/Http/Controllers/PurchaseProductController.php` | `importProcess()` dùng `importJobProcessChunked` + `ImportProductChunkJob`, chunk size 100. |
| `app/Http/Controllers/ProductController.php`                      | `importProcess()` dùng `importJobProcessChunked` + `ImportProductChunkJob`, chunk size 100. |

---

## 4. Cách dùng (file 1000 sản phẩm)

1. Vào **Operations > Products** (hoặc **Products** tùy route) → **Import**.
2. Chọn file `import_product full.xlsx` (hoặc `import_product  full.xlsx`).
3. Map cột → Submit.
4. Progress hiển thị theo **số job** (ví dụ 1/10, 2/10, … 10/10), mỗi job = 100 dòng. Import 1000 dòng sẽ nhanh hơn rõ so với trước và ít bị “đứng” do timeout.

---

## 5. Vì sao phải query unit_type? Cách dùng "unit đầu tiên"

- **Trong file Excel:** cột Unit type chỉ là **chuỗi** (vd: "Pcs", "Box", "Kg").
- **Trong DB:** bảng `products` lưu **`unit_id`** (số, khóa ngoại tới `unit_types.id`), không lưu chuỗi.
- Nên bắt buộc có bước **chuỗi → tra bảng `unit_types` → lấy `id` → gán `product.unit_id`**. Có map cột Unit type thì tra theo tên; không map (hoặc ô trống, hoặc tên không có trong bảng) thì dùng **unit type đầu tiên** (theo `id`) → chỉ cần query 1 lần rồi cache, không query mỗi dòng.
- **Đã chỉnh:** khi không có unit type (hoặc trống/không tìm thấy), lấy unit **đầu tiên** (`ORDER BY id LIMIT 1`), cache để chỉ gọi DB **một lần** mỗi chunk (chunk job) hoặc một lần mỗi request (job cũ).

---

## 6. Ghi chú

- **Progress theo job, không theo dòng:** Thanh tiến trình nhảy theo từng chunk (10% cho 10 job). Nếu cần hiển thị “X / 1000 rows” cần bổ sung logic đếm row trong batch (phức tạp hơn).
- **Job cũ `ImportProductJob`:** Vẫn tồn tại (dùng cho logic 1 dòng/job nếu cần). Hiện tại import Product đều qua `ImportProductChunkJob`.
- **Chunk size 100:** Có thể chỉnh trong controller (`$chunkSize = 100`). Chunk quá lớn (vd 500) dễ vượt timeout; chunk nhỏ (vd 50) thì số job tăng, số lần poll tăng.

---

_Đã kiểm tra và áp dụng cho file import 1000 sản phẩm._
