# Thư viện & Tên module (Craveva)

Tài liệu lưu thư viện (Composer) và tên module dùng trong logic Package / Module.

---

## 1. Thư viện chính (Composer) liên quan Package & Module

| Thư viện                           | Mục đích                                                                |
| ---------------------------------- | ----------------------------------------------------------------------- |
| `laravel/framework`                | Core framework.                                                         |
| `nwidart/laravel-modules`          | Quản lý module (Modules/), load route, `Module::disabledModuleArray()`. |
| `laravel/fortify`                  | Auth (login flow).                                                      |
| `mitchbred/entrust`                | Role/permission.                                                        |
| `yajra/laravel-datatables-oracle`  | DataTable (PackageDataTable, danh sách package).                        |
| `yajra/laravel-datatables-html`    | HTML builder cho DataTable.                                             |
| `yajra/laravel-datatables-buttons` | Nút export cho DataTable.                                               |

---

## 2. Thư viện khác thường dùng trong dự án

| Thư viện                    | Ghi chú                    |
| --------------------------- | -------------------------- |
| `laravel/cashier`           | Subscription / thanh toán. |
| `laravel/sanctum`           | API token.                 |
| `laravel/socialite`         | Đăng nhập social.          |
| `barryvdh/laravel-dompdf`   | PDF.                       |
| `maatwebsite/excel`         | Excel.                     |
| `intervention/image`        | Xử lý ảnh.                 |
| `guzzlehttp/guzzle`         | HTTP client.               |
| `spatie/laravel-backup`     | Backup.                    |
| `froiden/envato`            | Envato/plugin.             |
| `froiden/laravel-installer` | Installer.                 |
| `froiden/laravel-rest-api`  | REST API.                  |

### 2.1. Stack ngôn ngữ

| Thành phần                                    | Vai trò                                                                                                      |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `Modules/LanguagePack`                        | Nguồn chuẩn của file dịch; quét key và publish ra `lang/` cùng `Modules/*/Resources/lang/`.                  |
| `barryvdh/laravel-translation-manager`        | Import, chỉnh sửa và export bản dịch qua database/UI Translation Manager.                                    |
| `tanmuhittin/laravel-google-translate`        | Cung cấp `Str::apiTranslateWithAttributes()` cho nút Auto Translate của Translation Manager; không được xóa. |
| `stichoza/google-translate-php`                | Được command `translations:translate` của ứng dụng sử dụng trực tiếp.                                        |

`laravel-lang/lang` không tham gia các luồng trên. Package này chỉ cung cấp kho bản dịch Laravel bên ngoài và đã được gỡ vì dự án dùng kho riêng trong `Modules/LanguagePack/Languages/`.

### 2.2. `pcinaglia/laraupdater`: dependency legacy đã gỡ

Package này là một bộ tự cập nhật Laravel qua web. Khi được cấu hình đầy đủ, quản trị viên có thể gọi các route `updater.check`, `updater.currentVersion` và `updater.update`; package sẽ đọc `laraupdater.json` từ máy chủ phát hành, tải file ZIP, giải nén đè vào ứng dụng và chạy `upgrade.php` nếu có.

Kết quả audit lại toàn bộ dự án ngày 2026-06-29:

- Đã quét `app/`, `Modules/`, `routes/`, `resources/`, `database/`, `config/`, `tests/`, Composer và lịch sử Git. Không có controller, route, Blade, JavaScript, module, service hoặc test của Craveva gọi class hay route của package.
- Trước khi gỡ, `composer why pcinaglia/laraupdater` chỉ cho thấy project gốc yêu cầu trực tiếp; không package nào khác phụ thuộc vào nó.
- Laravel auto-discover `LaraUpdaterServiceProvider`. Provider này chỉ publish config/view/lang, load bản dịch và tự đăng ký ba route `updater.check`, `updater.currentVersion`, `updater.update`; nó không cung cấp service nào được mã Craveva inject hoặc gọi.
- Dự án không có `config/laraupdater.php`, nên `config('laraupdater')` hiện là `null`. Route cập nhật bị từ chối bởi `checkPermission()` và package không có URL phát hành hợp lệ để tải bản cập nhật.
- `config/froiden_envato.php` có một số key tương tự, nhưng thuộc namespace `froiden_envato.*`; `pcinaglia/laraupdater` chỉ đọc `laraupdater.*`, vì vậy hai phần không kết nối với nhau.
- Package đã có từ commit đầu tiên `776968e8` ngày 2026-02-23. Lịch sử Git không có dữ liệu trước lần nhập mã nguồn này, nên không thể khẳng định người ban đầu thêm nó vì lý do gì. Khả năng cao đây là dependency legacy được kế thừa từ bộ mã nguồn/updater cũ nhưng không được dọn sau khi hệ thống dùng luồng cập nhật riêng.
- `composer remove pcinaglia/laraupdater -W --dry-run` mô phỏng thành công: `0 installs`, `0 updates`, `1 removal`. Chỉ `pcinaglia/laraupdater 1.0.3.4` bị loại; hash của `composer.json` và `composer.lock` không đổi sau dry-run.

#### Các cơ chế update thật của Craveva

| Cơ chế | Thành phần | Trạng thái liên quan đến LaraUpdater |
| --- | --- | --- |
| Kiểm tra/tải/cài phiên bản online và hỗ trợ cập nhật module | `froiden/envato`, `config/froiden_envato.php`, các route `admin.updateVersion.*`; trait `AppBoot` và `ModuleVerify` được mã ứng dụng sử dụng | Độc lập, không phụ thuộc `pcinaglia/laraupdater` |
| Upload ZIP ứng dụng/module theo cách thủ công | `UpdateAppController`, `macellan/laravel-zip` | Độc lập |
| Cài/kích hoạt module | `CustomModuleController`, `ZipArchive`, `nwidart/laravel-modules` | Độc lập |

**Kết luận audit:** không có chức năng nghiệp vụ nào cần `pcinaglia/laraupdater`. Gỡ package chỉ loại service provider, view/lang/config nằm trong package và ba route `updater.*`; không loại `froiden/envato`, `macellan/laravel-zip` hoặc `nwidart/laravel-modules`.

#### Biên bản gỡ package ngày 2026-06-29

- Đã chạy `composer remove pcinaglia/laraupdater -W --no-interaction`.
- `pcinaglia/laraupdater` đã được loại khỏi `composer.json`, `composer.lock`, Composer installed metadata và `vendor/`.
- Không xóa migration, bảng, dữ liệu, config ứng dụng hoặc mã nghiệp vụ.
- Lần chạy Composer đầu tiên cập nhật package thành công nhưng tiến trình hậu cài đặt `package:discover` bị treo ở terminal và được dừng. Sau đó đã chạy riêng `composer dump-autoload --no-scripts --optimize` và `php artisan package:discover --ansi`; cả hai hoàn tất thành công.
- `composer validate --no-check-publish` hợp lệ và `composer install --dry-run --no-scripts` trả về `Nothing to install, update or remove`.
- `php artisan route:list --path=updater` xác nhận không còn route `updater.*`.
- Các luồng thay thế vẫn còn: 8 route `admin.updateVersion.*`, 9 route `update-settings.*` và 8 route `custom-modules.*`.
- Browser smoke test: đăng nhập và dashboard hoạt động. Tài khoản `admin@example.com` nhận `403 Forbidden` tại Update Settings vì `UpdateAppController` yêu cầu Global Super Admin; đây là phân quyền hiện hữu, không phải lỗi do gỡ package. Trang Custom Modules tải được với title `Modules`, nhưng phản hồi chậm ở luồng lấy plugin từ `froiden/envato`; không thực hiện cài ZIP hoặc thay đổi dữ liệu.

Nếu phát sinh lỗi sau này:

1. Nếu lỗi nhắc `pcinaglia`, `LaraUpdaterServiceProvider` hoặc route `updater.*`, kiểm tra mã triển khai có khác repository hiện tại hay cache Laravel còn cũ; chạy `composer install`, `composer dump-autoload`, `php artisan package:discover` và `php artisan optimize:clear`.
2. Nếu lỗi thuộc kiểm tra/tải/cài phiên bản online, kiểm tra `froiden/envato`, `config/froiden_envato.php` và route `admin.updateVersion.*`; không thêm lại LaraUpdater để xử lý lỗi của Froiden.
3. Nếu thật sự cần rollback, khôi phục `composer.json` và `composer.lock` từ commit trước khi gỡ rồi chạy `composer install`; không copy thủ công thư mục package vào `vendor/`.

### 2.3. Dependency đã gỡ

- 2026-06-28 - `spatie/laravel-model-status`: không model nào dùng trait `HasStatuses`, không có migration/bảng status của package.
- 2026-06-28 - `laravel-lang/lang`: không có provider hoặc tham chiếu runtime; LanguagePack đã tự quản lý toàn bộ file dịch.
- 2026-06-29 - `pcinaglia/laraupdater`: dependency self-updater legacy không được mã ứng dụng sử dụng; Craveva dùng các luồng update độc lập nêu tại mục 2.2.

Việc gỡ được thực hiện bằng `composer remove ... -W`; không xóa migration hay dữ liệu ứng dụng.

---

## 3. Tên module trong app (Package / module_settings)

Danh sách lấy từ bảng `modules` (trừ `settings`, `dashboards`, `restApi` và module bị disabled). Dùng trong `module_in_package` và form Package.

**Module core (ví dụ từ PackageDataTable / Module model):**

- `attendance`, `bankaccount`, `clients`, `contracts`, `employees`, `estimates`, `events`, `expenses`, `holidays`, `invoices`, `knowledgebase`, `leads`, `leaves`, `messages`, `notices`, `orders`, `payments`, `products`, `projects`, `reports`, `tasks`, `tickets`, `timelogs`

**ModuleSetting constants (dùng khi tạo module_settings mới):**

- **CLIENT_MODULES:** `projects`, `tickets`, `invoices`, `estimates`, `events`, `messages`, `tasks`, `timelogs`, `contracts`, `notices`, `payments`, `orders`, `knowledgebase`
- **OTHER_MODULES:** `clients`, `employees`, `attendance`, `expenses`, `leads`, `holidays`, `products`, `reports`, `settings`, `bankaccount`, `pricing`

---

## 4. Module có thể có thêm (trong DB / package cũ)

Một số package trong DB có thể chứa thêm tên: `asset`, `zoom`, `sms`, `recruit`, `payroll`, `purchase`, `letter`, `webhooks`, `qrcode`, `biolinks`, `biometric`, `servermanager`, `performance`, `onboarding`, `policy`, `pricing`.  
Chúng có thể tương ứng module trong thư mục `Modules/`; form Package và lệnh `packages:modules` chỉ thêm module có trong bảng `modules` và không nằm trong `Module::disabledModuleArray()`.

---

## 5. File tham chiếu

- **Danh sách module cho Package:** `app/Models/Module.php` (query + `disabledModuleArray()`), `app/DataTables/SuperAdmin/PackageDataTable.php`.
- **ModuleSetting:** `app/Models/ModuleSetting.php` (CLIENT_MODULES, OTHER_MODULES).
- **Composer:** `composer.json` (require, require-dev).
- **Cập nhật toàn bộ ứng dụng:** `app/Http/Controllers/UpdateAppController.php`.
- **Cài/cập nhật module:** `app/Http/Controllers/CustomModuleController.php`, `resources/views/custom-modules/install.blade.php`.
