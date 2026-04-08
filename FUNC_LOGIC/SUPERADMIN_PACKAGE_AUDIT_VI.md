# Super Admin — Package (gói): audit & flow

Tài liệu rà soát chức năng **Packages** (`/account/packages`): dữ liệu, UI, đồng bộ company, và sửa hiển thị module trùng / sai tick.

---

## 1. Luồng dữ liệu (tóm tắt)

| Thành phần                 | Vai trò                                                                                               |
| -------------------------- | ----------------------------------------------------------------------------------------------------- |
| Bảng **`packages`**        | Gói: `name`, `module_in_package` (JSON), limit storage/users, giá, `default` (yes/no/trial/lifetime). |
| **`companies.package_id`** | Company gán vào một gói.                                                                              |
| **`module_settings`**      | Đồng bộ theo gói (`CompanyObserver::updateModuleSettings`) — company được phép module nào.            |
| **`modules`**              | Danh sách module hệ thống (bỏ `settings`, `dashboards`, `restApi`, `disabledModuleArray()`).          |

**Lưu ý:** `module_in_package` khi lưu từ form là **`json_encode($request->module_in_package)`** với cấu trúc **`module_in_package[module_id] = module_name`** → JSON dạng object `{"53":"clients",...}` (value là tên module).

---

## 2. Code chính

| File                                                                            | Vai trò                                                               |
| ------------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| `app/Http/Controllers/SuperAdmin/PackageController.php`                         | CRUD package, `modifyRequest()` encode JSON module.                   |
| `app/DataTables/SuperAdmin/PackageDataTable.php`                                | Cột module: tick/cross theo từng module trong `modules`.              |
| `resources/views/super-admin/packages/ajax/create.blade.php` / `edit.blade.php` | Checkbox `module_in_package[{{ $module->id }}]`.                      |
| `app/Observers/CompanyObserver.php`                                             | Khi đổi gói / sync package: cập nhật `module_settings` theo JSON gói. |
| `app/Console/Commands/PackageModulesCommand.php`                                | `packages:modules` — bật module trong gói + sync company.             |

---

## 3. Vấn đề đã xử lý (audit UI)

### 3.1. Cùng một module hiện hai lần / tick lệch

**Nguyên nhân có thể:**

- Bảng **`modules`** có **nhiều dòng trùng `module_name`** (dữ liệu lịch sử) → foreach hiển thị hai cột giống nhau.
- So khớp `in_array` không chuẩn hóa chữ hoa/thường so với `CompanyObserver::packageModuleNamesFromJson()` (lower-case).

**Đã chỉnh:**

- Query module cho form + DataTable: **`->orderBy('module_name')->get()->unique('module_name')->values()`** — mỗi `module_name` chỉ một dòng.
- Chuẩn hóa tên module trong package JSON: **`Package::normalizedModuleNamesFromPackageJson()`** — lấy **value** JSON, lower-case, unique; cột danh sách dùng **`in_array(..., true)`** so với tên module lower-case.

### 3.2. Triệu chứng “gói có module nhưng company không thấy menu”

Không thuộc riêng Package UI — xem **`FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md`** và **`FUNC_BUG/DEVELOPER_TOOLS_MISSING_COMPANY_SETTINGS_DESPITE_PACKAGE.md`** (mẫu: `module_settings` thiếu dòng, nwidart tắt module).

---

## 4. Quyền Super Admin

- `PackageController`: `view_packages`, `add_packages`, `edit_packages`, `delete_packages` (permission `all` cho từng action).
- Package **default** / **trial** thường **không được xóa** (xem `destroy()`).

---

## 5. Kiểm tra nhanh sau khi sửa gói

1. `packages.module_in_package` có đủ module mong muốn (JSON values = tên module chữ thường sau khi lưu từ form).
2. Company dùng gói đó: `module_settings` khớp (chạy `php artisan packages:modules activate --module=...` nếu cần đồng bộ hàng loạt).
3. Reload trang Packages: mỗi module chỉ **một** dòng tick/cross; không còn trùng nhãn do duplicate `modules`.

---

## 6. Test

- `tests/Unit/PackageNormalizedModuleNamesTest.php` — parse JSON `module_in_package`.

---

_Cập nhật: audit Package Super Admin + sửa hiển thị module (unique + normalized names)._
