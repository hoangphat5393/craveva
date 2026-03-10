# Import Client – File does not exist (staging)

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
