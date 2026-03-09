# Flow: Package & Module (Craveva)

Luồng dữ liệu từ Package → module_in_package → Company → module_settings và cache.

---

## 1. ASCII Flow

```ascii
+------------------+     +------------------------+     +-----------------------------+
|  packages        |     |  companies             |     |  module_settings            |
|  (Super Admin)   |     |  (package_id)           |     |  (company_id, module_name)  |
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

---

## 2. Luồng chi tiết

### 2.1. Nguồn danh sách module

- **Bảng `modules`**: danh sách module (trừ `settings`, `dashboards`, `restApi` và module trong `Module::disabledModuleArray()`).
- **Package**: cột `module_in_package` = JSON array tên module (vd: `["clients","tasks",...]`).
- **ModuleSetting**: hằng số `CLIENT_MODULES`, `OTHER_MODULES` dùng khi **tạo mới** module_settings cho company (trong `CompanyObserver::createModuleSettings`).

### 2.2. Khi company được gán package (đổi package_id)

1. `Company::saasSaving()` (observer) phát hiện `package_id` thay đổi.
2. Gọi `CompanyObserver::updateModuleSettings($company)`.
3. Trong `updateModuleSettings`:
   - Lấy `module_in_package` từ package của company.
   - Với mỗi bản ghi `module_settings` của company: nếu `module_name` nằm trong package → `is_allowed=1`, `status=active`; ngược lại → `is_allowed=0`, `status=deactive`.
   - Gọi `CompanyObserver::widgetUpdate($company, $moduleInPackage)` để bật/tắt dashboard widget theo module.
   - Gọi `clearCompanyUserCache($company)` (xóa cache `user_modules_{user_id}`).

### 2.3. Khi sửa package (Super Admin UI hoặc lệnh Artisan)

- **UI:** `PackageController::update()` chỉ cập nhật `packages.module_in_package`. Các company **không** tự động được đồng bộ trừ khi đổi `package_id`.
- **Lệnh `packages:modules`:** Cập nhật `module_in_package` rồi **chủ động** gọi `CompanyObserver::updateModuleSettings($company)` cho từng company của package → đồng bộ ngay.

### 2.4. Kiểm tra quyền module (runtime)

- `ModuleSetting::checkModule($moduleName)`: kiểm tra theo role (admin/employee/client) và `status=active`.
- Menu/sidebar thường dùng `user_modules()` (cache `user_modules_{id}`) và `Route::has('...')` để ẩn mục khi route không tồn tại.

---

## 3. Bảng / Model liên quan

| Bảng / Model | Ý nghĩa |
|--------------|---------|
| `packages` | Gói gói (Default, Trial, Free, …), cột `module_in_package` (JSON). |
| `companies` | Công ty, `package_id` → package. |
| `module_settings` | Theo company: `company_id`, `module_name`, `type` (admin/employee/client), `status`, `is_allowed`. |
| `modules` | Danh sách module hệ thống (dùng cho form Package và lệnh `packages:modules`). |

---

## 4. File chính

| File | Vai trò |
|------|---------|
| `app/Models/SuperAdmin/Package.php` | Model Package, quan hệ `companies`. |
| `app/Models/ModuleSetting.php` | Model module_settings, `checkModule()`, CLIENT_MODULES, OTHER_MODULES. |
| `app/Models/Module.php` | Danh sách module, `disabledModuleArray()`. |
| `app/Observers/CompanyObserver.php` | `createModuleSettings()`, `updateModuleSettings()`, `widgetUpdate()`, `clearCompanyUserCache()`. |
| `app/Http/Controllers/SuperAdmin/PackageController.php` | CRUD package, lưu `module_in_package`. |
| `app/Console/Commands/PackageModulesCommand.php` | Lệnh `packages:modules` (list, activate-all, activate). |
