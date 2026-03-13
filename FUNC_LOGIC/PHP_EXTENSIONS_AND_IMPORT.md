# PHP extensions và chức năng Import (Client / Product / …)

Tài liệu liệt kê **extension PHP** cần thiết cho chức năng import Excel (Client, Product, v.v.) và cách kiểm tra.

---

## 1. Extension bắt buộc cho Import Excel

Import dùng **Maatwebsite Excel** (PhpSpreadsheet). Các extension sau **bắt buộc**:

| Extension    | Mục đích                                                     |
| ------------ | ------------------------------------------------------------ |
| **zip**      | File .xlsx thực chất là file ZIP; cần zip để đọc/ghi.        |
| **xml**      | Nội dung sheet Excel lưu dạng XML.                           |
| **libxml**   | Parse XML.                                                   |
| **dom**      | DOM XML.                                                     |
| **mbstring** | Chuỗi đa byte (tiếng Việt, Trung, Nhật, v.v.) trong ô Excel. |
| **zlib**     | Nén/giải nén trong file.                                     |
| **fileinfo** | Nhận diện loại file khi upload (MIME).                       |

Thiếu một trong các extension trên có thể gây: không đọc được file, lỗi “The separation symbol could not be found”, lỗi khi upload, hoặc lỗi encoding.

---

## 2. Extension tùy chọn

| Extension               | Mục đích                                                                             |
| ----------------------- | ------------------------------------------------------------------------------------ |
| **gd** hoặc **imagick** | Xử lý ảnh nhúng trong Excel (nếu có). Không bắt buộc cho import chỉ dữ liệu text/số. |

---

## 3. Cấu hình php.ini liên quan upload/import

| Chỉ thị                 | Ý nghĩa                            | Gợi ý cho file lớn                               |
| ----------------------- | ---------------------------------- | ------------------------------------------------ |
| **upload_max_filesize** | Giới hạn kích thước 1 file upload. | ≥ 20M–50M nếu import file Excel lớn.             |
| **post_max_size**       | Giới hạn tổng dung lượng POST.     | ≥ upload_max_filesize (vd. 50M).                 |
| **max_execution_time**  | Thời gian tối đa 1 request (giây). | 300 trở lên khi chạy queue trong request (poll). |
| **memory_limit**        | Giới hạn bộ nhớ PHP.               | 256M trở lên cho file nhiều dòng.                |

---

## 4. Cách kiểm tra trên máy

### 4.1. Dòng lệnh (CLI – thường dùng khi chạy queue:work)

```bash
php -m
```

Xem trong danh sách có đủ: zip, xml, libxml, dom, mbstring, zlib, fileinfo.

### 4.2. Trong ứng dụng (WEB – cùng môi trường khi user import)

Khi **APP_DEBUG=true**, mở trong trình duyệt:

```
https://<domain>/php-ini-check
```

Response JSON gồm:

- **directives**: max_execution_time, memory_limit, post_max_size, upload_max_filesize, …
- **extensions.all_loaded**: danh sách extension đã load.
- **extensions.required_for_import**: từng extension (zip, xml, …) kèm **loaded** (true/false) và **required** (bắt buộc hay tùy chọn).
- **extensions.missing_required**: extension bắt buộc đang thiếu.
- **extensions.import_ok**: true nếu không thiếu extension bắt buộc.

Lưu ý: PHP khi chạy qua web server (Apache/Nginx) có thể dùng **php.ini khác** với khi chạy `php -m` trong terminal. Route trên phản ánh đúng môi trường WEB (request import của user).

---

## 5. Nếu thiếu extension

- **Windows (XAMPP / PHP cài tay):** Mở `php.ini`, bỏ comment dòng `extension=zip`, `extension=mbstring`, … (bỏ dấu `;` đầu dòng), restart web server.
- **Linux:** Ví dụ `sudo apt install php-zip php-xml php-mbstring php-fileinfo` (tùy phiên bản PHP: php8.2-zip, …).
- Sau khi bật extension, kiểm tra lại bằng `/php-ini-check` (WEB) hoặc `php -m` (CLI).

---

## 6. Tóm tắt

- Import Excel **cần**: zip, xml, libxml, dom, mbstring, zlib, fileinfo.
- Thiếu extension → dễ gây lỗi đọc file, upload, hoặc encoding.
- Dùng **/php-ini-check** (khi APP_DEBUG=true) để xem extension và php.ini đang dùng cho request WEB.
