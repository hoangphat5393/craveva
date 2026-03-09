# Bug: Thiếu module Tier Pricing trên sidebar (Dashboard)

**Triệu chứng:** Trên trang Dashboard (`/account/dashboard`), user (admin/employee) không thấy mục **Pricing** (Tier Pricing) trong sidebar, trong khi các mục khác như Sales, Finance, Reports vẫn có.

**Môi trường:** craveva-staging.test (local/staging), user "Yadah Wang" (không phải superadmin).

---

## 1. Nguyên nhân

Menu **Pricing** (Tier Pricing) chỉ hiển thị khi **cả hai** điều kiện thỏa:

1. **User không phải superadmin**  
   Điều kiện: `!user()->is_superadmin`  
   → Superadmin **không** thấy mục Pricing (thiết kế ẩn với superadmin).

2. **`'pricing'` nằm trong `user_modules()`**  
   Điều kiện: `in_array('pricing', user_modules())`  
   → `user_modules()` lấy từ **module_settings** (theo company + role), chỉ gồm các module có `is_allowed = 1` và `status = 'active'`.

**`user_modules()`** được tính từ:

- Bảng **`module_settings`**
- Điều kiện: `company_id` = company hiện tại, `type` = role (admin/employee/client), `is_allowed = 1`, `status = 'active'`
- Kết quả cache trong `user_modules_{user_id}`

Trạng thái **module_settings** cho từng module (trong đó có `pricing`) được đồng bộ từ **package** của company:

- **Package** có cột `module_in_package` (JSON array tên module).
- Khi company đổi gói hoặc khi chạy lệnh `packages:modules activate-all` / `activate --module=pricing`, **CompanyObserver::updateModuleSettings()** sẽ:
    - Với mỗi bản ghi `module_settings` của company: nếu `module_name` **có trong** `module_in_package` của package → `is_allowed = 1`, `status = 'active'`; ngược lại → `is_allowed = 0`, `status = 'deactive'`.

**Kết luận nguyên nhân thường gặp:**

- Gói (package) của company **không có** `pricing` trong `module_in_package`  
  → `module_settings` cho `pricing` bị set `is_allowed = 0`, `status = 'deactive'`  
  → `user_modules()` không chứa `'pricing'`  
  → Menu Pricing không hiển thị.

Ngoài ra có thể gặp:

- **Module Laravel Pricing (nwidart) bị tắt**  
  → Route pricing không load; nếu không bảo vệ bằng `Route::has()`, có thể lỗi khi render menu (đã xử lý bằng thêm `Route::has('pricing.tiers.index')` trong menu).
- **Cache cũ**  
  → `user_modules_{user_id}` vẫn giữ danh sách không có `pricing` sau khi đã bật pricing trong gói.

---

## 2. Flow hoạt động (tóm tắt)

```
Package (module_in_package)
    ↓
Company (package_id)
    ↓
CompanyObserver::updateModuleSettings($company)
    → Cập nhật module_settings: is_allowed, status theo module_in_package
    → clearCompanyUserCache($company)  // xóa cache user_modules_{id}
    ↓
user_modules()
    → Đọc ModuleSetting (company_id, type=role, is_allowed=1, status=active)
    → Cache: user_modules_{user_id}
    ↓
Menu (resources/views/sections/menu.blade.php)
    @if (!user()->is_superadmin && in_array('pricing', user_modules()) && Route::has('pricing.tiers.index'))
        → Hiển thị mục "Pricing" (Tiers, Client Tiers, Contract Pricing, ...)
```

**File liên quan:**

| Thành phần                        | File / vị trí                                                                         |
| --------------------------------- | ------------------------------------------------------------------------------------- |
| Điều kiện hiển thị menu Pricing   | `resources/views/sections/menu.blade.php` (khoảng dòng 648–663)                       |
| Hàm `user_modules()`              | `app/Helper/start.php`                                                                |
| Model module_settings             | `app/Models/ModuleSetting.php` (OTHER_MODULES có `'pricing'`)                         |
| Đồng bộ package → module_settings | `app/Observers/CompanyObserver.php` (`updateModuleSettings`, `createModuleSettings`)  |
| Route Pricing                     | `Modules/Pricing/Routes/web.php` (chỉ load khi module Pricing được bật trong nwidart) |
| Trạng thái bật/tắt module Laravel | `storage/app/modules_statuses.json` (Custom Modules trong Settings)                   |

---

## 3. Hướng xử lý

### 3.1. Đảm bảo gói có module Pricing và đồng bộ lại

**Cách 1: Bật pricing cho mọi package (và đồng bộ company)**

```bash
php artisan packages:modules activate --module=pricing
```

**Cách 2: Bật toàn bộ module trong gói cho một package cụ thể (ví dụ package id = 1)**

```bash
php artisan packages:modules activate-all --package=1
```

Lệnh sẽ:

- Thêm `pricing` vào `packages.module_in_package` (nếu chưa có).
- Gọi `CompanyObserver::updateModuleSettings($company)` cho từng company dùng package đó → cập nhật `module_settings` (is_allowed, status).
- Gọi `clearCompanyUserCache($company)` → xóa cache `user_modules_{user_id}`.

Sau đó user reload trang (hoặc đăng nhập lại) để thấy menu Pricing.

### 3.2. Bật Custom Module Pricing (nwidart) nếu đang tắt

Nếu toggle **Pricing** trên **Settings > Module Settings > Custom Modules** đang tắt:

```bash
php artisan packages:modules enable-custom
```

Hoặc bật thủ công toggle Pricing trên trang đó. Khi đó route `pricing.*` mới được load; menu đã dùng `Route::has('pricing.tiers.index')` nên sẽ chỉ hiện khi route tồn tại.

### 3.3. Xóa cache user (nếu đã sửa DB mà menu vẫn không đổi)

```bash
php artisan cache:clear
```

Hoặc xóa cache theo user (trong code/tinker):

```php
cache()->forget('user_modules_' . $userId);
```

### 3.4. Kiểm tra nhanh

- **Package của company có `pricing` không:**

    ```bash
    php artisan packages:modules list
    ```

    Xem cột "Trong gói" của package mà company đang dùng có chứa `pricing`.

- **Module Laravel Pricing có bật không:**  
  Mở **Settings > Module Settings > Custom Modules**, xem toggle **Pricing** có bật (ON) không.

---

## 4. Thay đổi code đã áp dụng (phòng lỗi)

- Trong **menu.blade.php**, điều kiện hiển thị mục Pricing đã được thêm **`Route::has('pricing.tiers.index')`**:
    - Chỉ hiện menu khi route pricing đã được đăng ký (module Pricing bật).
    - Tránh lỗi khi render `route('pricing.tiers.index')` lúc module bị tắt.

---

## 5. Tóm tắt checklist

| Bước | Việc cần làm                                                                                                                             |
| ---- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| 1    | Đảm bảo package của company có `pricing` trong `module_in_package` (dùng `activate --module=pricing` hoặc `activate-all --package=...`). |
| 2    | Đảm bảo module Pricing (nwidart) đang bật (trang Custom Modules hoặc `enable-custom`).                                                   |
| 3    | Xóa cache (`cache:clear` hoặc `user_modules_{id}`) và reload/đăng nhập lại.                                                              |
| 4    | Menu dùng `Route::has('pricing.tiers.index')` để tránh lỗi khi module tắt.                                                               |
