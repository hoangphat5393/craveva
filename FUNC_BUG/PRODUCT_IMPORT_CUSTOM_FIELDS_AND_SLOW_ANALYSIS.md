# Phân tích: Product import – cột trống/unmatched, custom field, và import chậm / gần như không chạy

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
