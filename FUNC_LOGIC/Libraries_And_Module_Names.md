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
| `doctrine/dbal`                    | DB schema/migration.                                                    |

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
