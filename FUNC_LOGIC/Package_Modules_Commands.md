# Lệnh Package & Module (Craveva)

Tài liệu lưu các lệnh Artisan và cách dùng cho quản lý module trong Package.

**Hai loại "bật module" khác nhau:**

| Loại                                | Lệnh / UI                                                                                    | Lưu ở đâu                                       | Ảnh hưởng                                                                             |
| ----------------------------------- | -------------------------------------------------------------------------------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------- |
| **Module trong gói (Package)**      | `activate-all` / `activate`                                                                  | `packages.module_in_package`, `module_settings` | Quyền module theo gói/công ty (menu, tính năng theo subscription).                    |
| **Custom Modules (toggle trên UI)** | `enable-custom` hoặc bật từng cái trên trang **Settings > Module Settings > Custom Modules** | `storage/app/modules_statuses.json` (nwidart)   | Bật/tắt module Laravel (Affiliate, Asset, Payroll, …); nếu tắt thì module không load. |

→ Chạy `activate-all` **không** đổi trạng thái toggle trên trang **Custom Modules**. Để tất cả toggle ON, dùng `enable-custom` hoặc dùng một lệnh gộp `activate-all-full` (xem mục 2.5).

---

## 1. Lệnh chính: `packages:modules`

**File:** `app/Console/Commands/PackageModulesCommand.php`

| Tham số / Option    | Mô tả                                                                            |
| ------------------- | -------------------------------------------------------------------------------- |
| `action` (bắt buộc) | `list` \| `activate-all` \| `activate` \| `enable-custom` \| `activate-all-full` |
| `--package=`        | ID package (tùy chọn; mặc định: tất cả package)                                  |
| `--module=`         | Tên module (bắt buộc khi `action=activate`)                                      |

---

## 2. Các lệnh cụ thể

### 2.1. Xem danh sách module và trạng thái từng package

```bash
php artisan packages:modules list
```

- In danh sách toàn bộ module dùng cho Package (từ bảng `modules`, trừ settings/dashboards/restApi và module disabled).
- Với mỗi package: số module trong gói và **danh sách module thiếu** (nếu có).

---

### 2.2. Bật toàn bộ module

**Cho tất cả package:**

```bash
php artisan packages:modules activate-all
```

**Cho một package (theo ID):**

```bash
php artisan packages:modules activate-all --package=1
```

- Cập nhật `packages.module_in_package` = JSON đầy đủ module.
- Đồng bộ `module_settings` (is_allowed, status) cho mọi company dùng package đó.

---

### 2.3. Bật một module cụ thể

**Cho mọi package:**

```bash
php artisan packages:modules activate --module=clients
```

**Cho một package:**

```bash
php artisan packages:modules activate --module=products --package=9
```

- Thêm module vào `module_in_package` nếu chưa có.
- Đồng bộ `module_settings` cho các company thuộc package.

---

### 2.4. Bật toàn bộ Custom Modules (toggle trên trang Module Settings)

Trang **Settings > Module Settings > Custom Modules** hiển thị trạng thái bật/tắt từ **nwidart** (`storage/app/modules_statuses.json`). Lệnh `activate-all` không ghi vào đây.

Để **tất cả toggle chuyển sang ON** (bật toàn bộ module Affiliate, Asset, Payroll, …):

```bash
php artisan packages:modules enable-custom
```

- Ghi trạng thái enabled cho từng module vào `modules_statuses.json`.
- Xóa cache `craveva_plugins`, `user_modules`.
- Reload trang **Module Settings** để thấy toggle ON.

---

### 2.5. Bật triệt để: Package modules + Custom Modules (một lệnh)

Nếu muốn **vừa** bật đủ module trong mọi package **vừa** cho trang **Custom Modules** hiển thị toàn bộ toggle ON, dùng:

```bash
php artisan packages:modules activate-all-full
```

- Thực hiện lần lượt: `activate-all` (cập nhật package + đồng bộ `module_settings`) rồi `enable-custom` (ghi `modules_statuses.json` + cache).
- Sau khi chạy xong, reload trang **Settings > Module Settings > Custom Modules** để thấy tất cả toggle đã bật.

**Xử lý nhanh khi đã chạy activate-all mà trang Custom Modules vẫn nhiều toggle OFF:**

1. Chạy thêm: `php artisan packages:modules enable-custom`
2. Hoặc lần sau dùng luôn: `php artisan packages:modules activate-all-full`

---

### 2.6. Nguyên tắc vận hành đúng (tách Global Custom Modules và Package Entitlement)

- **Custom Modules toggle** là quyết định **global của SuperAdmin** (module có load toàn hệ thống hay không).
- **Package modules** quyết định **company nào được dùng module nào** qua `module_settings`.
- Vì `modules_statuses.json` là file global cho toàn app, không nên đồng bộ ngược từ package từng company sang custom toggle global.

=> Vận hành chuẩn:

1. SuperAdmin bật/tắt module ở Custom Modules (hoặc `enable-custom` khi cần bật toàn bộ).
2. Company entitlement vẫn do package + `module_settings` điều khiển.

---

## 3. Tên module (lowercase, không dấu)

Ví dụ: `clients`, `tasks`, `timelogs`, `knowledgebase`, `bankaccount`, `invoices`, `estimates`, `products`, `orders`, `reports`, `pricing`, …

Danh sách đầy đủ lấy từ bảng `modules` (trừ settings, dashboards, restApi và module bị disabled).

---

## 4. Lưu ý

- Danh sách module “đầy đủ” trùng với form sửa Package trong Super Admin.
- Sau khi chạy activate, cache user (`user_modules_{id}`) được xóa qua `CompanyObserver::updateModuleSettings`.

---

## 5. Flow dữ liệu (Package -> Company -> Module Settings)

```ascii
+------------------+     +------------------------+     +-----------------------------+
|  packages        |     |  companies             |     |  module_settings            |
|  (Super Admin)   |     |  (package_id)          |     |  (company_id, module_name)  |
+------------------+     +------------------------+     +-----------------------------+
         |                            |                                |
         | module_in_package          | package_id                     | is_allowed, status
         | (JSON array module names)  |-------------------------------->|
         |                            |                                |
         |                            |  CompanyObserver               |
         |                            |  .updateModuleSettings($company)
         |                            |-------------------------------->|
         |                            |                                | active/deactive
         |                            |                                | is_allowed 1/0
         |                            |                                |
         |                            |  clearCompanyUserCache         |
         |                            |  (forget user_modules_{id})    |
         |                            |-------------------------------->|
```

### 5.1. Nguồn module

- Bảng `modules` là nguồn danh sách module (trừ `settings`, `dashboards`, `restApi` và module disabled).
- `packages.module_in_package` giữ JSON module của từng gói.
- `module_settings` là trạng thái runtime theo công ty (`is_allowed`, `status`, `type`).

### 5.2. Khi đổi package của company

1. `Company` observer phát hiện `package_id` thay đổi.
2. Gọi `CompanyObserver::updateModuleSettings($company)`.
3. Đồng bộ `module_settings` theo `module_in_package`.
4. Cập nhật widget theo module và xóa cache `user_modules_{id}`.

### 5.3. Khi sửa package từ UI vs lệnh

- **UI Super Admin Package edit**: cập nhật `packages.module_in_package`; company thường chưa sync ngay nếu không đổi `package_id`.
- **Lệnh `packages:modules`**: cập nhật package rồi chủ động sync `module_settings` cho company thuộc package.

### 5.4. Runtime check module

- `ModuleSetting::checkModule($moduleName)` kiểm tra quyền + trạng thái.
- Sidebar/menu dùng `user_modules()` (cache) + kiểm tra route để hiện/ẩn menu.

---

## 6. File chính liên quan

| File                                                    | Vai trò                                    |
| ------------------------------------------------------- | ------------------------------------------ |
| `app/Models/SuperAdmin/Package.php`                     | Model package, quan hệ company             |
| `app/Models/ModuleSetting.php`                          | Logic module settings, checkModule         |
| `app/Models/Module.php`                                 | Danh sách module, disabled modules         |
| `app/Observers/CompanyObserver.php`                     | create/update module settings, clear cache |
| `app/Http/Controllers/SuperAdmin/PackageController.php` | CRUD package, lưu `module_in_package`      |
| `app/Console/Commands/PackageModulesCommand.php`        | Lệnh `packages:modules`                    |
