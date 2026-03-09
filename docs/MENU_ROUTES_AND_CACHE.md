# Vì sao nhiều route bị lỗi "not defined" và cách xử lý

## 1. Flow gây lỗi

1. User vào **/account/dashboard** → layout load **sidebar** → sidebar include **menu.blade.php**.
2. **menu.blade.php** hiển thị menu theo:
   - `user_modules()` = danh sách module user được phép (từ bảng `module_settings`, theo role).
   - Permission từng mục (vd: `view_payroll`, `view_asset`).
3. Với mỗi module (Payroll, Asset, Policy, Zoom, Webhooks, …), menu gọi **`route('tên-route.index')`** để tạo link.
4. **Route đó chỉ tồn tại** nếu module tương ứng **đã load** và **đăng ký route** (trong `Modules/TênModule/Routes/web.php` qua RouteServiceProvider của module).
5. Nếu **module chưa load** hoặc **route chưa được đăng ký** (cache cũ, module tắt, lỗi bootstrap) → Laravel ném **RouteNotFoundException** → trang dashboard trắng/lỗi.

**Tóm lại:** Menu dựa vào `module_settings` (user có quyền module X), nhưng route của module X lại do **code module** đăng ký. Hai thứ có thể lệch nhau → gọi `route()` khi route chưa có → lỗi.

## 2. Đã sửa trong menu.blade.php

Đã thêm **`Route::has('tên-route.index')`** trước mỗi chỗ gọi `route()` cho **các module** sau, để nếu route chưa có thì **không render mục menu** (tránh crash):

- `payroll.index` (và payroll-expenses, employee-salary, overtime-requests)
- `server-manager.index`
- `webhooks.index`, `webhooks-log.index`
- `zoom-meetings.index`
- `biometric-devices.index`
- `assets.index`
- `policy.index`
- `qrcode.index`
- `biolinks.index`

Các route **core** (contracts, projects, invoices, …) thường luôn có khi app chạy bình thường; nếu thiếu thì thường do thiếu đăng ký route hoặc cache.

## 3. Lệnh nên chạy (không thiếu bước)

Để **đảm bảo mọi route của module được đăng ký** và không bị cache cũ:

```bash
# Xóa cache route (quan trọng nhất)
php artisan route:clear

# Xóa toàn bộ cache (config, view, cache app)
php artisan optimize:clear
```

Sau khi chạy hai lệnh trên:

- Laravel sẽ **load lại toàn bộ route** từ `routes/` và từ **từng module** (Modules/*/Routes/web.php).
- Menu chỉ hiện mục khi **vừa** có quyền **vừa** có route (`Route::has()` = true). Nếu module bật và load đúng, route sẽ có và mục menu hiện lại.

**Không cần** chạy thêm lệnh đặc biệt nào khác cho “route menu”. Nếu đã từng chạy `php artisan route:cache` trong môi trường có module tắt, nên **không cache route** khi dev (chỉ dùng `route:cache` khi deploy production nếu cần).

## 4. Kiểm tra route và module (tùy chọn)

```bash
# Liệt kê tất cả route (xem route module có trong list không)
php artisan route:list

# Liệt kê module (nwidart)
php artisan module:list
```

Nếu một module ở trạng thái **Disabled** nhưng user vẫn có trong `module_settings` → menu vẫn tính “có quyền” nhưng route module không đăng ký → lỗi. Cách an toàn là giữ phần đã sửa: chỉ gọi `route()` khi `Route::has()` = true.
