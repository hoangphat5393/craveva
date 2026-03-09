# Kích hoạt module cho Package

## Lệnh Artisan: `packages:modules`

Dùng để **xem** package nào đang có/thiếu module nào và **bật** toàn bộ hoặc từng module cho package.

### 1. Xem danh sách module và trạng thái từng package

```bash
php artisan packages:modules list
```

- In ra danh sách tất cả module dùng cho Package (từ bảng `modules`, trừ settings/dashboards/restApi và module bị disabled).
- Với mỗi package: số module đang trong gói và **danh sách module thiếu** (nếu có).

### 2. Bật toàn bộ module cho mọi package (hoặc một package)

**Tất cả package:**

```bash
php artisan packages:modules activate-all
```

**Chỉ một package (theo ID):**

```bash
php artisan packages:modules activate-all --package=1
```

- Cập nhật `module_in_package` của package thành đủ toàn bộ module (theo danh sách từ bảng `modules`).
- Đồng bộ `module_settings` (is_allowed, status) cho mọi company dùng package đó.

### 3. Bật một module cụ thể

**Cho mọi package:**

```bash
php artisan packages:modules activate --module=clients
```

**Cho một package:**

```bash
php artisan packages:modules activate --module=products --package=9
```

- Thêm module vào `module_in_package` của package (nếu chưa có).
- Đồng bộ `module_settings` cho các company thuộc package.

### Lưu ý

- Module name dùng dạng lowercase, không dấu (vd: `knowledgebase`, `bankaccount`).
- Danh sách module “đầy đủ” lấy từ `Module::...` (trùng với form sửa Package trong Super Admin). Module không có trong bảng `modules` hoặc bị disabled sẽ không được thêm khi `activate-all`.
- Sau khi chạy lệnh, cache user có thể được xóa cho các company bị ảnh hưởng (qua `CompanyObserver::updateModuleSettings`).
