# Developer Tools Module – Kiểm tra toàn bộ

## 1. Tổng quan

Module **DeveloperTools** (`Modules/DeveloperTools`) cung cấp:

- **Credentials**: Tạo DB user + database gateway (views) theo company, multi-tenant read-only.
- **CodeMap**: Quét file (PHP, Blade, JS, …), lưu metadata và dependency (chỉ Super Admin).
- **Access logs**: Lịch sử tạo credential (thành công/thất bại).

---

## 2. Cấu trúc đã kiểm tra

| Thành phần                                                                    | Trạng thái                                                                             |
| ----------------------------------------------------------------------------- | -------------------------------------------------------------------------------------- |
| `module.json`                                                                 | OK – khai báo provider                                                                 |
| `Config/config.php`                                                           | OK – scan_paths, db_access modules, deny/sensitive/join_views                          |
| `Routes/web.php`                                                              | OK – auth middleware                                                                   |
| `Routes/api.php`                                                              | OK – auth:sanctum, 1 route v1/developertools                                           |
| `DeveloperToolsController`                                                    | Xem mục 3                                                                              |
| Entities (Credential, DbAccessLog, DbUserMapping, FileRecord, FileDependency) | OK                                                                                     |
| Services (DbAccessPolicy, FileScanner)                                        | Xem mục 4, 5                                                                           |
| Migrations                                                                    | OK – credentials, db_user_mapping, files, dependencies, access_logs, add_access_policy |
| Views (index, codemap/index)                                                  | OK                                                                                     |
| Console `SetupDatabase`                                                       | Khác logic với controller – xem mục 6                                                  |

---

## 3. Controller – Các điểm cần lưu ý

### 3.1 Phân quyền

- **index / store / destroy**: Chỉ kiểm tra `company()`, không kiểm tra role `admin`.  
  Sidebar chỉ hiển thị link cho `admin` (`in_array('admin', user_roles())`), nhưng user đã đăng nhập (có company) có thể gọi trực tiếp URL.  
  **Đề xuất**: Thêm kiểm tra role (ví dụ chỉ cho phép `admin`) trong controller hoặc middleware cho các route credentials.

- **codeMap / scanCodeMap / exportCodeMap**: Đã kiểm tra `user()->is_superadmin` → OK.

### 3.2 Bảo mật & dữ liệu

- **store**:
    - Tạo DB user, database, views; username/DB name có giới hạn độ dài và ký tự.
    - Dùng `DbAccessPolicy` để giới hạn bảng theo module → OK.
    - Mật khẩu chỉ flash session một lần → OK.

- **destroy**: Revoke credential, DROP USER, xóa mapping và bản ghi credential. Có try/catch khi DROP USER → OK.

- **exportCodeMap**: Trả về JSON, giới hạn 2000 bản ghi. Route GET không có CSRF; vì chỉ superadmin và dữ liệu là metadata file (không nhạy cảm như mật khẩu) nên chấp nhận được. Nếu muốn chặt hơn có thể đổi sang POST.

### 3.3 Logic store

- Database gateway: `api_gateway_{company_id}` (không dùng `config('developertools.gateway_db')` trong controller).
- Views: global tables, tables có `company_id`, và join_views từ config → đúng với thiết kế.
- Log và flash message đầy đủ.

---

## 4. DbAccessPolicy

- `availableModules`, `defaultModules`, `denyTables`, `globalTables`, `sensitiveTables`, `joinViews` đều đọc từ config → OK.
- `normalizeRequestedModules`: mở rộng theo `depends_on`, mặc định nếu rỗng → OK.
- `resolveAllowedTables`: pattern match, loại deny/sensitive → OK.
- `selectColumnsForTable`: bảng sensitive chỉ cho phép cột trong `allow_columns` → OK.
- `sanitizeIdentifier`: chỉ giữ ký tự an toàn → OK.
- `matchTablesByPatterns`: `%` → `.*` regex → OK.
- Unit test (`DbAccessPolicyTest`) có sẵn → OK.

---

## 5. FileScanner

- Quét theo `config('developertools.scan_paths')` và `allowed_extensions` → OK.
- `RecursiveDirectoryIterator`: không dùng `SKIP_DOTS`. Trên một số môi trường có thể gặp `.` / `..`. **Đề xuất**:  
  `new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)` để tránh lỗi hoặc file ẩn không mong muốn.
- `extractDependencies`: luôn trả về `[]` (chưa implement). Dependency được lưu trong DB nhưng không cập nhật → chức năng dependency chưa hoàn chỉnh.
- `inferModule` chỉ nhận dạng trong `Modules/...`; path `app/`, `database/`, `routes/` không có `module` → có thể chấp nhận nếu thiết kế chỉ module trong `Modules/`.

---

## 6. Console SetupDatabase

- Command: `developer-tools:setup-db`.
- Dùng `config('developertools.gateway_db', 'api_gateway_db')` → **một DB gateway chung**, không theo company.
- Tạo function `get_developer_tools_company_id()` trong main DB (đọc từ `db_user_mapping` theo `USER()`) và tạo VIEW với `WHERE company_id = get_developer_tools_company_id()`.
- Controller lại tạo **một DB riêng per company** (`api_gateway_{id}`) và VIEW với `WHERE company_id = {company_id}` cố định (không dùng function).

Hệ quả:

- Hai cơ chế khác nhau:
    - Command: 1 DB + function (user-based).
    - Controller: N DB (1/company) + filter cố định theo company.
- Nếu chỉ chạy controller (không chạy command), function trong main DB không được tạo → command dùng cho kiểu triển khai “single gateway DB + function”.
- **Đề xuất**: Ghi rõ trong tài liệu hoặc comment khi nào dùng command, khi nào dùng “Generate Credential” trong UI; hoặc thống nhất một mô hình (chỉ dùng command hoặc chỉ dùng controller).

---

## 7. Config

- `config.php` không có key `gateway_db`. Code dùng `config('developertools.gateway_db', 'api_gateway_db')` ở view và `SetupDatabase` → mặc định hoạt động. **Đề xuất**: Thêm `'gateway_db' => env('DEVELOPERTOOLS_GATEWAY_DB', 'api_gateway_db')` trong config để dễ cấu hình môi trường.

---

## 8. Routes

- **Web**:
    - `GET developertools` → index
    - `POST developertools/create-credential` → store
    - `DELETE developertools/revoke/{id}` → destroy
    - `GET developertools/codemap/view` → codeMap (name: developertools.codemap)
    - `POST developertools/codemap/scan` → scanCodeMap
    - `GET developertools/codemap/export` → exportCodeMap
    - `GET funcnews` → codeMap (alias)
    - `GET funcnews/export` → exportCodeMap (alias)
- Form revoke dùng `@method('DELETE')` và route là `Route::delete(...)` → khớp.

---

## 9. Migrations

- Thứ tự và nội dung: credentials, db_user_mapping, developer_tools_files, developer_tools_dependencies, add_access_policy, developer_tools_db_access_logs → đủ và nhất quán.
- Bảng `developer_tools_credentials` có foreign key `company_id`, `created_by` → OK.

---

## 10. Tóm tắt đề xuất

1. **Phân quyền**: Thêm kiểm tra role (ví dụ `admin`) cho `index`, `store`, `destroy` (hoặc middleware) để khớp với sidebar.
2. **FileScanner**: Dùng `RecursiveDirectoryIterator::SKIP_DOTS` khi quét thư mục.
3. **Config**: Thêm `gateway_db` vào `config.php` (có thể dùng env).
4. **SetupDatabase vs Controller**: Làm rõ hoặc ghi chú khi nào dùng command, khi nào dùng UI; hoặc thống nhất một mô hình gateway.
5. **extractDependencies**: Hoặc implement, hoặc bỏ/ẩn phần dependency trong UI nếu không dùng.

---

## 11. Kết luận

Module Developer Tools được implement đầy đủ cho luồng chính: tạo credential theo company, policy bảng theo module, view read-only, access log, CodeMap (scan + export). Cần bổ sung phân quyền rõ ràng cho phần credentials, điều chỉnh nhỏ FileScanner và config, và làm rõ sự khác biệt giữa console command và controller (gateway DB).
