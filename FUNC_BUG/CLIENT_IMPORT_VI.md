# Client Import — lỗi thường gặp & chi tiết

**Registry:** [REGISTRY.md](REGISTRY.md) (mã `IMPORT-CLIENT`)  
**Flow:** `FUNC_LOGIC/FLOW_ADD_CLIENT.md` · `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`

---

## 1) Mục tiêu

- Gom các lỗi import client thường gặp (staging + data) vào 1 chỗ.
- Trỏ về “source of truth” trong code/flow để tránh đọc nhiều file rời.

**Source of truth (flow):**

- `FUNC_LOGIC/FLOW_ADD_CLIENT.md`
- `FUNC_LOGIC/IMPORT_CHUNK_AND_BULK_INSERT.md`

---

## 2) Nhóm lỗi A — CSV delimiter/encoding (“The separation symbol could not be found”)

**Triệu chứng:** rất nhiều dòng fail với message: `The separation symbol could not be found`.

**Nguyên nhân gốc (thường gặp):**

- File thực chất là **CSV** nhưng delimiter/encoding không phù hợp, PhpSpreadsheet không detect được delimiter.
- File rỗng hoặc vài dòng đầu không có dấu phân cách.

**Cách xử lý vận hành:**

- Ưu tiên dùng **.xlsx** (sample form thường có `client-sample.xlsx`).
- Nếu bắt buộc CSV: lưu **CSV UTF-8**, delimiter `,` hoặc `;`.

**Chi tiết:** xem § Phụ lục bên dưới (Phần A).

---

## 3) Nhóm lỗi B — Dữ liệu thiếu “name” (SQLSTATE 1048 Column 'name' cannot be null)

**Triệu chứng:** `SQLSTATE[23000]... Column 'name' cannot be null`

**Nguyên nhân gốc:**

- Map cột sai (field “name” map nhầm).
- Dòng dữ liệu thiếu cột/thiếu value (thường do CSV parse lỗi hoặc file thiếu dữ liệu).

**Hướng xử lý:**

- Chuẩn hóa/validate “name” trước khi lưu (để trả lỗi rõ ràng cho người dùng).
- Kiểm tra mapping & data trước khi import.

**Chi tiết:** xem § Phụ lục bên dưới (Phần A).

---

## 4) Nhóm lỗi C — “File does not exist …/public/user-uploads/temp/… (staging)”

**Triệu chứng:** upload/import báo không tìm thấy file ở đường dẫn `public/user-uploads/temp/<file>.xlsx`.

**Nguyên nhân gốc (staging):**

- Thư mục `public/user-uploads/temp` không tồn tại hoặc không writable cho user chạy PHP-FPM/worker.
- Deploy đổi symlink release làm `public_path()` trỏ sang release khác giữa các bước.

**Hướng xử lý vận hành:**

- Đảm bảo thư mục tồn tại và quyền đúng cho `www-data`:
    - `public/user-uploads/temp`
    - `public/user-uploads/import-files`

**Chi tiết:** xem § Phụ lục bên dưới (Phần B).  
**Runbook quyền/worker:** `docs/SERVER_RUNBOOK_VI.md`

---

## 5) Gợi ý “canonical” để tránh trùng lặp tài liệu

- Flow: `FUNC_LOGIC/FLOW_ADD_CLIENT.md` · runbook: `docs/SERVER_RUNBOOK_VI.md`

---

## Phần A — Lỗi delimiter, SQL name null, queue filter

**Triệu chứng:** Import client báo **14705 entries failed of 17567 entries**, với hai loại lỗi chính:

1. **"The separation symbol could not be found"** (lặp lại rất nhiều lần)
2. **SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'name' cannot be null**

**Môi trường:** Import client qua file Excel/CSV (sample: `client-sample.xlsx` hoặc file CSV tùy chỉnh).

---

## 1. Lỗi "The separation symbol could not be found"

### Nguyên nhân

- Thông báo này đến từ **PhpSpreadsheet** (thư viện đọc file trong Maatwebsite Excel) khi đọc file **CSV**.
- Khi đọc CSV, reader cố **tự nhận diện delimiter** (dấu phân cách cột): dấu phẩy `,`, chấm phẩy `;`, tab, pipe `|`, v.v. Nếu **không tìm thấy** dấu phân cách nào phù hợp trong vài dòng đầu (file rỗng, encoding sai, hoặc dùng dấu phân cách lạ), PhpSpreadsheet ném lỗi **"The separation symbol could not be found"**.

### Các trường hợp thường gặp

| Tình huống                                                                                 | Hậu quả                                                |
| ------------------------------------------------------------------------------------------ | ------------------------------------------------------ |
| File CSV dùng delimiter không chuẩn (ví dụ chỉ có khoảng trắng, hoặc ký tự đặc biệt)       | Không nhận diện được → lỗi separation symbol           |
| File CSV encoding không phải UTF-8 (ví dụ Excel lưu CSV với encoding mặc định của Windows) | Một số ký tự delimiter bị đọc sai → nhận diện thất bại |
| File gần như rỗng hoặc vài dòng đầu không có dấu phân cách                                 | Inference delimiter thất bại → lỗi separation symbol   |

### Cách xử lý

1. **Dùng file Excel (.xlsx) thay vì CSV**  
   Form import có sample `client-sample.xlsx`. Nên dùng định dạng .xlsx để tránh phụ thuộc vào delimiter CSV.

2. **Nếu bắt buộc dùng CSV:**
    - Dùng **dấu phẩy `,`** hoặc **chấm phẩy `;`** làm delimiter (đúng với cấu hình mặc định trong `config/excel.php`: `'delimiter' => ','`).
    - Lưu file CSV với **encoding UTF-8** (trong Excel: Save As → CSV UTF-8).

3. **Đã bổ sung trong code:**
    - `ImportController::getQueueException` **filter theo `queue`**: chỉ hiển thị lỗi của đúng loại import (ClientImport), không lẫn với Lead/Employee hay lần import cũ.
    - Cấu hình CSV: `config/excel.php` → `imports.csv` đã có `delimiter => ','`, `input_encoding => 'UTF-8'`. Nếu dùng CSV, nên lưu file UTF-8 và dùng dấu phẩy.

---

## 2. Lỗi "Column 'name' cannot be null"

### Nguyên nhân

- Bảng `users` có cột **`name` NOT NULL**, nhưng khi import có dòng đang gán `name = null`.
- Trong code:
    - `ClientImportProcessor::processRow()` lấy giá trị cột qua `getValue($row, $columns, 'name')`.
    - `getValue` trả về `$row[$index] ?? null` với `$index` là vị trí cột được map cho "name".
- **`name` thành null** khi:
    1. **Map cột sai:** User map cột không chứa tên (hoặc map nhầm) → giá trị lấy được rỗng/null.
    2. **Số cột trong từng dòng ít hơn mapping:** Ví dụ file bị đọc sai delimiter (mỗi dòng chỉ thành 1 cột) → `$row` chỉ có `[0 => "cả dòng"]`; nếu "name" được map vào cột index 1, 2, … thì `$row[1]`, `$row[2]` không tồn tại → `getValue(..., 'name')` = null.
    3. **Ô "name" trong file để trống** cho một số dòng.

### Cách xử lý

1. **Phía code (đã bổ sung):**
    - Trong `ClientImportProcessor::processRow()`: **validate và chuẩn hóa `name`** trước khi gán vào `User`:
        - Lấy giá trị, **trim**.
        - Nếu null hoặc chuỗi rỗng → **throw Exception** rõ ràng (ví dụ: "Client name is required and cannot be empty") thay vì để DB báo lỗi 1048.
    - Như vậy lỗi hiển thị cho user sẽ dễ hiểu hơn và đúng ngữ cảnh import.

2. **Phía dữ liệu / quy trình:**
    - Đảm bảo file có **cột tên client** và **luôn điền** cho mỗi dòng cần import.
    - Nếu dùng "Contains headings": cột header phải khớp với tên field (ví dụ "Client Name" / "name" tùy form) và **map đúng** cột đó vào field "name" trong bước chọn cột.
    - Kiểm tra **encoding và delimiter** (xem mục 1) để mỗi dòng được tách đủ cột, tránh trường hợp cả dòng thành một cột và cột "name" bị thiếu.

---

## 3. Liên quan đến danh sách lỗi hiển thị ([ClientImport] ...)

- Trang kết quả import lấy danh sách lỗi từ **`failed_jobs`** qua `ImportController::getQueueException($name)`.
- **Đã bật** filter `->where('queue', $name)`: chỉ hiển thị failed job của đúng import (ví dụ ClientImport), không lẫn với Lead/Employee hay lần import cũ.

---

## 4. Tóm tắt hành động

| Việc cần làm                                                      | Trạng thái / Ghi chú                                  |
| ----------------------------------------------------------------- | ----------------------------------------------------- |
| Ghi chú lỗi và cách xử lý vào FUNC_BUG                            | ✅ Tài liệu này                                       |
| Validate `name` không null/empty trong `ClientImportProcessor`    | ✅ Nên bổ sung: trim + throw message rõ ràng          |
| Hướng dẫn user: dùng .xlsx hoặc CSV UTF-8, delimiter `,` hoặc `;` | Trong hướng dẫn import / tooltip                      |
| Filter `failed_jobs` theo queue khi hiển thị exception            | ✅ Đã bật trong `ImportController::getQueueException` |
| CSV: dùng delimiter `,` và UTF-8 (config/excel.php)               | Đã cấu hình sẵn; file CSV nên lưu UTF-8               |
| (Tùy chọn) Cho phép chọn delimiter khi upload CSV                 | Cần chỉnh UI + config/reader                          |

---

## 5. File liên quan

- `app/Imports/ClientImport.php` – Định nghĩa cột import (ToArray).
- `app/Services/ClientImportProcessor.php` – Xử lý từng dòng, tạo User + ClientDetails.
- `app/Jobs/ImportClientJob.php`, `app/Jobs/ImportClientChunkJob.php` – Queue job import.
- `app/Http/Controllers/ClientController.php` – `importStore`, `importProcess`.
- `app/Traits/ImportExcel.php` – `importFileProcess`, `importJobProcess`, `importJobProcessChunked`.
- `app/Http/Controllers/ImportController.php` – `getQueueException` (hiển thị lỗi).
- `config/excel.php` – Cấu hình CSV (delimiter, encoding).

---

## 6. File Miaolin Customer test.xlsx (48/211 thành công, 163 lỗi)

**Triệu chứng:** Import file `Miaolin Customer test.xlsx` báo 48 entries processed, 163 failed. Lỗi hiển thị: "Client name is required and cannot be empty" và "The separation symbol could not be found".

**Phân tích:**

1. **"Client name is required and cannot be empty"**  
   Một số dòng trong file **không có tên khách hàng** (cột map vào "Client Name" trống). Hệ thống đã validate và báo rõ thay vì lỗi DB.

2. **"The separation symbol could not be found"**
    - Thường xảy ra khi đọc **CSV** (PhpSpreadsheet không nhận diện được delimiter).
    - Nếu file thật sự là **.xlsx**, lỗi này có thể đến từ **lần import trước** (CSV hoặc file lỗi); danh sách exception trước đây không filter theo queue nên hiển thị lẫn.
    - **Đã xử lý:** Filter exception theo queue `ClientImport`; danh sách lỗi chỉ còn của Client Import. CSV nên dùng delimiter `,` và UTF-8 (xem `config/excel.php`).

**Cách xử lý khi import Miaolin Customer test.xlsx:**

| Việc làm                                       | Ghi chú                                                                                           |
| ---------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| Đảm bảo file là **.xlsx** hợp lệ               | Mở bằng Excel/LibreOffice rồi Save As .xlsx nếu nghi ngờ file hỏng hoặc thực chất là CSV đổi tên. |
| Bật **Contains headings** và map đúng cột      | Cột chứa tên khách hàng phải map vào **Client Name**.                                             |
| Điền **tên** cho mọi dòng cần import           | Dòng nào để trống tên sẽ lỗi "Client name is required and cannot be empty".                       |
| Sau khi sửa code (filter queue + CSV settings) | Chạy lại import; danh sách "Exceptions while importing" chỉ còn lỗi của lần Client Import này.    |

---

## 7. Kết quả điều tra sâu (Miaolin Customer test.xlsx – vẫn còn lỗi)

**Đã kiểm tra trực tiếp file và luồng:**

### 7.1. File Miaolin Customer test.xlsx

| Mục            | Kết quả                                                                  |
| -------------- | ------------------------------------------------------------------------ | ---------------------------------------------------- | ---------- |
| Định dạng      | `.xlsx` hợp lệ, PhpSpreadsheet đọc bằng **Xlsx reader** (không dùng CSV) |
| Cấu trúc       | 212 dòng (1 header + 211 dữ liệu), 15 cột (A–O)                          |
| Header         | `客戶代號`, `客戶簡稱                                                    | Customer Short Name`, `業務員`, `部門`, …, `統一編號 | Tax ID`, … |
| Cột tên        | **客戶簡稱** (index 1) = tên khách hàng                                  |
| Dòng tên trống | Chỉ **1** trong 211 dòng                                                 |

→ File đọc bình thường, lỗi "The separation symbol could not be found" **không phát sinh** khi đọc file này (vì dùng Xlsx reader, không phải CSV reader).

### 7.2. Lỗi "Client name is required and cannot be empty"

- Chỉ ~1 dòng trong file có tên trống.
- Nếu map đúng cột **客戶簡稱** → **Client Name**, sẽ chỉ có ~1 lỗi dạng này.
- Nếu map sai (ví dụ map **客戶代號** → Client Name), logic vẫn có thể chạy nhưng dễ sinh lỗi khác.

### 7.3. Lỗi "The separation symbol could not be found"

- Là lỗi của **PhpSpreadsheet CSV reader** khi không nhận diện được delimiter.
- Với file `.xlsx`, hệ thống dùng **Xlsx reader**, nên **không** gây ra lỗi này khi đọc file.
- Trong job import, `processRow()` **không** gọi đọc file hay PhpSpreadsheet.

**Các khả năng hợp lý:**

1. **Lỗi cũ trong `failed_jobs`**: `getQueueException` lấy 50 failed job gần nhất theo `queue`, **không** lọc theo `batch_id`. Nếu trước đó từng import CSV hoặc file khác bị lỗi, các bản ghi đó vẫn nằm trong `failed_jobs` và có thể hiển thị chung với lỗi hiện tại.
2. **Lần thử trước dùng CSV**: Nếu user đã thử import file CSV (hoặc file khác dạng CSV) trước đó, các job fail với "separation symbol" sẽ được ghi vào `failed_jobs`; sau đó import file xlsx vẫn thành công, nhưng danh sách exception trộn lẫn.
3. **Luồng đọc khác**: Cần kiểm tra thêm xem `HeadingRowImport` hoặc bước đọc file khác có vô tình dùng CSV reader không.

### 7.4. 163 failed vs 48 success

- Chỉ 1 dòng tên trống → không thể có 163 lỗi "Client name required".
- 163 lỗi rất có thể gồm nhiều loại khác nhau, trong đó có cả lỗi từ **các lần import trước** vì danh sách exception không lọc theo batch.

### 7.5. Các sửa đổi đã thực hiện (code)

| Việc đã làm                                   | Mục đích                                                                                                                                                                                                                                   |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Lọc exception theo **batch_id**               | Chỉ hiển thị lỗi của đúng lần import hiện tại (qua `job_batches.failed_job_ids`).                                                                                                                                                          |
| **Chuẩn hóa row (normalizeExcelRows)**        | Maatwebsite Excel có thể trả về Cell/RichText objects thay vì scalar. Chuẩn hóa trong `ImportExcel::importJobProcessChunked` và `ImportClientChunkJob::normalizeRow` trước khi xử lý/queue – tránh lỗi serialization và các lỗi liên quan. |
| **Thêm translation `duplicateEntry`**         | Trước đây dùng `__('messages.duplicateEntry')` nhưng key chưa tồn tại → hiển thị raw "messages.duplicateEntry". Đã thêm vào `lang/en/messages.php`, `lang/vi/messages.php`.                                                                |
| Hiển thị **số dòng file** trong lỗi           | Format `Row X: <message>` để dễ tra cứu trong Excel.                                                                                                                                                                                       |
| Hiển thị **toàn bộ message** (tối đa 50 dòng) | Không chỉ dòng đầu của exception.                                                                                                                                                                                                          |

### 7.6. Nguyên nhân đã xác định: cột ngày tháng (Carbon createFromFormat)

- Lỗi **"The separation symbol could not be found"** đến từ **Carbon::createFromFormat()** khi parse ngày.
- Trong `CustomFieldsTrait::updateCustomFieldData`, các custom field type `date` (last_transaction_at, business_closure_date) được parse bằng `Carbon::createFromFormat(company()->date_format, $value)`.
- Company date_format thường dùng dấu phân cách (vd. `d-m-Y`, `m/d/Y`). Nếu giá trị từ Excel là **YYYYMMDD** (vd. `20240513`) không có dấu phân cách, Carbon throw exception.
- **Đã sửa:** Thêm `parseDateForCustomField()` thử nhiều format: company format, **Ymd** (YYYYMMDD), Y-m-d, d-m-Y, m/d/Y, d/m/Y, và Carbon::parse() fallback.

### 7.7. Phát hiện CSV đổi đuôi .xlsx (đã triển khai)

- **`Files::isCsvDisguisedAsXlsx($filePath)`**: Kiểm tra magic bytes – file .xlsx thật là ZIP (bắt đầu bằng PK), còn CSV là plain text.
- Gọi **trước** `Excel::import` trong `importFileProcess` và `importJobProcessChunked`.
- Nếu phát hiện CSV đổi đuôi: xóa file, throw `ValidationException` với message hướng dẫn user lưu đúng .xlsx hoặc upload với đuôi .csv.
- Message: `messages.importFileCsvDisguisedAsXlsx` (en, vi, zh-CN, zh-TW).

### 7.8. Hướng xử lý tiếp theo (nếu vẫn còn lỗi)

| Việc cần làm                                      | Mục đích                                                      |
| ------------------------------------------------- | ------------------------------------------------------------- |
| Xóa/archive `failed_jobs` cũ (thủ công hoặc cron) | Giảm nhiễu do lỗi các lần import trước.                       |
| Xác nhận mapping cột                              | Đảm bảo **客戶簡稱** được map vào **Client Name** khi import. |

---

## Phần B — File temp không tồn tại (staging)

## Lỗi

```
File does not exist at path /var/www/craveva-staging/current/craveva/public/user-uploads/temp/5625d56cb5fbaea000720234cb031b43.xlsx
```

## Đối chiếu Local vs Staging

### Luồng upload (giữ nguyên logic)

1. **ClientController::importStore()** → **importFileProcess()** → **Files::upload($request->import_file, Files::IMPORT_FOLDER)**
    - `IMPORT_FOLDER = 'import-files'`

2. **Files::upload()** (app/Helper/Files.php):
    - `config(['filesystems.default' => 'local'])`  
      → disk `local` trong `config/filesystems.php` có `'root' => public_path('user-uploads')`
    - `$uploadedFile->storeAs('temp', $newName)`  
      → ghi file vào **public/user-uploads/temp/{tên_file}**
    - `$tempPath = public_path('user-uploads/temp/'.$newName)`
    - `Storage::put($newPath, File::get($tempPath))` với `$newPath = 'import-files/'.$newName`  
      → copy từ temp sang **public/user-uploads/import-files/{tên_file}**
    - `File::delete($tempPath)`  
      → xóa file trong **temp**
    - Trả về `$newName` (chỉ tên file).

3. **ImportExcel::importFileProcess()** (sau khi upload):
    - Đọc file bằng:  
      `$filePath = public_path(Files::UPLOAD_FOLDER.'/'.Files::IMPORT_FOLDER.'/'.$this->file)`  
      → **public/user-uploads/import-files/{tên_file}**

4. Form bước 2 gửi `<input type="hidden" name="file" value="{{ $file }}">` (chỉ tên file).

5. **ClientController::importProcess()**:
    - `$filePath = public_path(Files::UPLOAD_FOLDER.'/'.Files::IMPORT_FOLDER.'/'.$request->file)`  
      → lại dùng **public/user-uploads/import-files/{tên_file}**

**Kết luận code:**

- Mọi chỗ đọc file import đều dùng **import-files**, không dùng **temp** sau khi upload xong.
- Đường dẫn **temp** chỉ dùng **trong** `Files::upload()` (bước trung gian).
- Lỗi “File does not exist at path .../temp/...” nhiều khả năng xảy ra **trong** `Files::upload()` khi gọi `File::get($tempPath)` (sau `storeAs('temp', $newName)`).

### Khác biệt Local vs Staging

|                   | Local                                              | Staging (Deployer)                                                                              |
| ----------------- | -------------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| **public_path()** | Thư mục project (vd: `D:\xampp\htdocs\...\public`) | `/var/www/craveva-staging/current/craveva/public` (symlink `current` → release)                 |
| **Ghi file**      | Trực tiếp vào thư mục project                      | Có thể khác user (deploy vs www-data), quyền thư mục                                            |
| **Nhiều server**  | Thường 1 process                                   | Có thể nhiều node; file upload ở node A, request sau ở node B                                   |
| **Symlink**       | Ít dùng                                            | `current` → release mới mỗi lần deploy; nếu deploy giữa bước 1 và 2, file có thể nằm release cũ |

### Nguyên nhân khả dĩ trên staging

1. **Thư mục không tồn tại hoặc không ghi được**
    - `public/user-uploads/temp` hoặc `public/user-uploads/import-files` chưa có hoặc không writable (www-data).
    - `storeAs('temp', $newName)` không ghi được → file không nằm ở `.../temp/...` → `File::get($tempPath)` → “File does not exist at path .../temp/...”.

2. **Config filesystem**
    - Staging cache config với `FILESYSTEM_DRIVER` khác (vd S3) → nếu có chỗ dùng disk trước khi `config(['filesystems.default' => 'local'])` có hiệu lực, file có thể ghi vào chỗ khác, không vào `public/user-uploads/temp`.

3. **Deploy giữa hai bước**
    - File được lưu vào release cũ; sau deploy `current` trỏ sang release mới → `public_path()` không còn trỏ tới file → lỗi (thường sẽ là đường dẫn **import-files** chứ không phải temp, trừ khi lỗi xảy ra ngay trong request upload).

## Cách xử lý (không đổi logic form/route)

### 1. Kiểm tra trên staging (nên làm trước)

SSH vào staging, chạy:

```bash
cd /var/www/craveva-staging/current/craveva

# Thư mục và quyền
ls -la public/user-uploads/
ls -la public/user-uploads/temp/          # phải tồn tại, writable
ls -la public/user-uploads/import-files/  # phải tồn tại, writable

# User chạy web server (vd www-data)
ps aux | grep php-fpm
# hoặc
ps aux | grep apache

# Tạo/thư mục và quyền (chạy bằng user deploy hoặc root)
sudo mkdir -p public/user-uploads/temp public/user-uploads/import-files
sudo chown -R www-data:www-data public/user-uploads
sudo chmod -R 775 public/user-uploads
```

Sau đó thử upload lại. Nếu vẫn lỗi, kiểm tra log PHP/Laravel (storage/logs) và log web server.

### 2. Đảm bảo thư mục luôn có trước khi upload (code) – đã sửa

Trong **Files::upload()** đã thêm tạo thư mục **temp** trước khi `storeAs('temp', ...)`:

- **File:** `app/Helper/Files.php`
- **Đã thêm:** `self::createDirectoryIfNotExist('temp');` trước khi gọi `storeAs('temp', $newName)` (và vẫn giữ `createDirectoryIfNotExist($folder)` cho import-files).

Logic upload/import (route, form, request, đọc file từ **import-files**) không đổi.

### 3. (Tùy chọn) Dùng storage thay vì public cho import

Nếu staging dùng shared storage (vd `storage` được deployer share giữa các release), có thể chuyển import sang `storage_path('app/import-files')` để file nằm trong storage thay vì dưới `public`. Cách này đòi hỏi sửa thêm:

- **Files::upload()** khi `$dir === Files::IMPORT_FOLDER`: ghi/copy vào `storage_path('app/import-files')`.
- **ImportExcel** (và mọi chỗ đọc file import): dùng `storage_path('app/import-files/'.$request->file)` thay vì `public_path(...)`.

Chỉ nên làm bước 3 nếu đã làm 1 và 2 mà vẫn lỗi (vd nhiều server, không share `public`).

## Tóm tắt

- **Logic hiện tại:** File upload qua **temp** rồi copy sang **import-files**; mọi bước đọc file đều dùng **import-files**. Không cần đổi flow hay form.
- **Lỗi “.../temp/... does not exist”** nhiều khả năng xảy ra trong `Files::upload()` khi đọc từ temp (thư mục temp chưa có / không ghi được trên staging).
- **Hành động:** (1) Kiểm tra và sửa quyền/thư mục `public/user-uploads/temp` và `import-files` trên staging; (2) Trong code, đảm bảo tạo thư mục **temp** trước khi ghi (ví dụ gọi `createDirectoryIfNotExist('temp')` trong `Files::upload()`).
