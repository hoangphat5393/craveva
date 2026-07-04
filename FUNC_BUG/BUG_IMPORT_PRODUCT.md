# Product Import — mapping, custom field & hiệu năng

**Sổ lỗi:** [SO_LOI.md](SO_LOI.md) (mã `IMPORT-PRODUCT`)  
**Flow:** `FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md` · `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`

---

## 1) Mục tiêu

- Gộp các phân tích về Product import: cột unmatched/custom field và import chậm.
- Trỏ về hướng xử lý hiện tại để tránh hiểu sai “tài liệu cũ”.

**Source of truth (ưu tiên đọc):**

- `FUNC_IMPROVE/IMPORT_CHUNK_BULK_QUEUE.md`
- `FUNC_IMPROVE/IMPORT_POLL_TRACKERS.md`

---

## 2) Nhóm vấn đề A — Unmatched columns & Custom Fields

**Hiện tượng:**

- Một số cột trong file Excel hiển thị “Unmatched”, không map được vào dropdown.

**Nguyên nhân gốc:**

- Mapping cột chỉ dựa trên danh sách `fields()` của import class (ví dụ `ProductImport::fields()`).
- Header so khớp theo chuỗi chính xác; `heading_row.formatter` (vd `slug`) có thể làm header ≠ field id.
- Product có Custom Fields trong hệ thống, nhưng import không merge custom fields vào danh sách cột nên không thể map.

---

## 3) Nhóm vấn đề B — Import chậm / “gần như không chạy”

**Hiện tượng:**

- Import chạy rất lâu hoặc UI không thấy progress cập nhật.

**Nguyên nhân gốc thường gặp:**

- Poll progress chạy `queue:work --max-jobs=50` ngay trong request HTTP → dễ timeout (PHP/nginx/proxy).
- Số job quá lớn (1 dòng = 1 job) → overhead queue và poll nhiều lần.

**Hai hướng xử lý (tài liệu đã ghi nhận):**

- Chunk/batch import để giảm số job.
- Tối ưu lookup/cache trong chunk để tránh query lặp (unit/category/subcategory).

---

## 4) Trạng thái hiện tại / ghi chú

- Phần B (phụ lục) mô tả optimize chunk + cache lookup.

## Phần A — Custom field, unmatched, import chậm (phân tích)

**Yêu cầu:** Tìm hiểu lý do (không tự ý sửa code):

1. Product có custom field và tại sao có mấy cột trống (unmatched)?
2. Import quá chậm, hầu như không chạy – nguyên nhân?

---

## 1. Cột trống (Unmatched columns) và Custom field

### 1.1. Cách mapping cột hoạt động

- **Nguồn cột hệ thống:** `ImportExcel::importFileProcess()` lấy danh sách cột từ `$importClass::fields()` (ví dụ `ProductImport::fields()`). Chỉ có các cột được khai báo trong `fields()` mới xuất hiện trong dropdown "Select a column".
- **Nguồn cột file Excel:** Hàng đầu tiên file (header) được đọc bằng `HeadingRowImport`. Giá trị header sau khi format (xem bên dưới) được so sánh với **đúng chuỗi** `id` của từng cột trong `fields()`.
- **Khớp cột:** Trong `ImportExcel.php` (khoảng dòng 73):
    ```php
    $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
    ```
    Tức là: một cột Excel chỉ được coi là “matched” khi **giá trị header (sau formatter) trùng hệt** với một `id` trong danh sách cột (vd: `product_name`, `price`, `wholesale_price`).

### 1.2. Product import không gộp Custom field vào danh sách cột

- Model **Product** có hỗ trợ custom field: `Product::CUSTOM_FIELD_MODEL = 'App\Models\Product'` và có nhóm trong `CustomFieldGroup` (name = 'Product').
- **Nhưng** `ProductImport::fields()` (file `app/Imports/ProductImport.php`) chỉ trả về **một mảng cố định** khoảng 15 cột (product_name, price, unit_type, sku, description, storage_condition, certification, wholesale_price, price_per_box, employee_price, track_inventory, inventory_type, status, …). **Không có đoạn code nào** gọi CustomField / CustomFieldGroup để bổ sung custom field của Product vào `$this->columns`.
- Hệ quả: Trong bước map cột, dropdown "Select a column" **chỉ có các cột chuẩn** của Product, **không có** custom field. Nếu file Excel có thêm cột (ví dụ cột 7, 8, 9) mà:
    - không trùng header với bất kỳ `id` nào trong `ProductImport::fields()`, hoặc
    - là dữ liệu dành cho custom field  
      thì những cột đó sẽ luôn hiển thị là **Unmatched** và không có lựa chọn “map vào custom field” trong dropdown.

### 1.3. Tại sao có “mấy cột trống” (7, 8, 9…)?

- **“Trống”** ở đây = **unmapped**: cột có trong file Excel nhưng không khớp với bất kỳ cột hệ thống nào.
- Nguyên nhân thường gặp:
    1. **Header không trùng `id`:**  
       Header trong Excel (ví dụ "中盤價 | Whole sale price | Giá sỉ") được đọc và có thể bị format (xem `config/excel.php` → `heading_row.formatter`). Nếu formatter là `slug` thì chuỗi có thể thành dạng slug (ví dụ `whole-sale-price`), trong khi `id` trong code là `wholesale_price` → **không bằng nhau** → cột thành unmatched.
    2. **Số cột Excel > số cột trong `ProductImport::fields()`:**  
       File có thêm cột (ví dụ 7, 8, 9) không nằm trong danh sách cột chuẩn và cũng không được thêm vào từ custom field → những cột đó luôn unmatched.
    3. **Custom field không được đưa vào import:**  
       Product có custom field trong hệ thống nhưng **import không merge** các custom field vào `fields()` → user không thể chọn “map cột Excel vào custom field” → cột dữ liệu custom field sẽ thành “trống” (unmatched) nếu không trùng tên với cột chuẩn.

**Tóm tắt (1):** Cột trống/unmatched là do (a) danh sách cột import chỉ gồm cột chuẩn, không gồm custom field; (b) so khớp header theo chuỗi chính xác (và formatter có thể làm header ≠ id). Để map được vào custom field cần bổ sung custom field của Product vào `ProductImport::fields()` (hoặc cơ chế tương đương) và xử lý lưu custom_fields_data trong job import.

---

## 2. Import quá chậm / “hầu như không chạy”

### 2.1. Flow tóm tắt

1. User gửi form map cột → `importJobProcess()` đọc lại file Excel, tạo **1 job/dòng** (ví dụ 50 dòng = 50 job), dispatch batch vào queue `ProductImport` (database).
2. Frontend nhận `batchId`, gọi `getProgress(batchId)` (poll) tới route `import/process/{name}/{id}` → `ImportController::getImportProgress()`.
3. Trong `getImportProgress()`:
    - `set_time_limit(300)`.
    - Gọi `Artisan::call('queue:work database --max-jobs=50 --queue=... --stop-when-empty')` → **trong cùng request HTTP** chạy tối đa 50 job (đồng bộ).
    - Sau đó lấy batch progress và trả về JSON (progress, processedJobs, …).
4. Frontend nhận response → cập nhật progress bar và số X/Y; nếu chưa xong thì gọi lại `getProgress(batchId)` sau một khoảng delay.

### 2.2. Nguyên nhân có thể khiến “gần như không chạy” / không thấy tiến trình

- **A. Request poll bị timeout (không nhận được response):**
    - Một lần poll = một request HTTP chạy `queue:work` cho tối đa 50 job. Thời gian xử lý = tổng thời gian chạy 50 × `ImportProductJob` (mỗi job: vài query DB, save product, activity…). Nếu **PHP `max_execution_time` (FPM/Apache) nhỏ** (vd 30–60s) hoặc **proxy/nginx timeout** (vd 60s), request có thể **bị cắt** trước khi chạy xong 50 job và trả về. Khi đó frontend **không nhận response** → progress bar không cập nhật → cảm giác “không chạy”, dù job vẫn có thể đang chạy trong request đó cho đến lúc bị timeout.
- **B. Delay trước lần poll đầu:**
    - Trong `process-form.blade.php`, có biến `delay = isFirstPoll ? 0 : 2000` nhưng **setTimeout vẫn dùng hằng số 2000** (ví dụ `setTimeout(..., 2000)`). Nghĩa là **lần poll đầu vẫn chờ 2 giây** mới gửi request. Không phải nguyên nhân “không chạy” nhưng làm chậm cảm nhận tiến trình.
- **C. Queue / connection:**
    - Job được dispatch vào `database` queue, queue name = `ProductImport`. Nếu môi trường có `QUEUE_CONNECTION=sync` thì job chạy ngay trong request submit, không qua worker; khi đó poll không “thấy” job trong batch theo cách thông thường. Hoặc queue name không khớp (ví dụ worker chạy queue khác) → job không được xử lý khi poll gọi `queue:work --queue=ProductImport`.
- **D. Lỗi trong job (failed):**
    - Nếu phần lớn job fail (validation, DB, …), batch vẫn “xong” (processed + failed = total) và UI sẽ cập nhật khi poll trả về. Trường hợp “gần như không chạy” thường là **poll không trả về** (timeout) hoặc **poll không được gọi đúng** (JS/route).
- **E. Frontend không gọi poll hoặc gọi sai:**
    - Route progress cần `name` = `ProductImport` và `id` = batchId. Nếu `$importClassName` trong view sai hoặc batchId không được truyền đúng, URL poll sẽ sai → 404/500 → progress không cập nhật.

### 2.3. Tóm tắt nguyên nhân “import quá chậm / hầu như không chạy”

| Nguyên nhân               | Mô tả ngắn                                                                                                          |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Timeout request poll**  | PHP/proxy timeout cắt request trước khi `queue:work` chạy đủ 50 job → không trả JSON → thanh tiến trình không nhảy. |
| **Delay 2s cố định**      | `setTimeout(..., 2000)` bỏ qua biến `delay` → lần poll đầu vẫn chờ 2 giây.                                          |
| **Queue connection/name** | Sync hoặc queue name không khớp → job không chạy đúng lúc poll hoặc không vào batch.                                |
| **Sai route/param**       | `importClassName` hoặc batchId sai → request progress lỗi → UI không cập nhật.                                      |

---

## 3. File / vị trí code tham chiếu

| Nội dung                                             | File / vị trí                                                                              |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| Danh sách cột Product import (không có custom field) | `app/Imports/ProductImport.php` → `fields()`                                               |
| So khớp header với cột                               | `app/Traits/ImportExcel.php` → `importFileProcess()` (heading, matchedColumns)             |
| Product có custom field                              | `app/Models/Product.php` → `CUSTOM_FIELD_MODEL`; `app/Models/CustomFieldGroup.php`         |
| Poll progress + queue:work                           | `app/Http/Controllers/ImportController.php` → `getImportProgress()`                        |
| Delay poll (bug dùng 2000 thay vì delay)             | `resources/views/import/process-form.blade.php` → `getProgress()`, `setTimeout(..., 2000)` |
| Heading row formatter                                | `config/excel.php` → `imports.heading_row.formatter` (vd. `slug`)                          |
| Job xử lý từng dòng                                  | `app/Jobs/ImportProductJob.php` (không ghi custom_fields_data)                             |

---

_Tài liệu chỉ phân tích, không thay đổi code. Khi cần sửa sẽ thực hiện theo yêu cầu riêng._

---

## Phần B — 1000 dòng: nguyên nhân & chunk (implementation note)

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
