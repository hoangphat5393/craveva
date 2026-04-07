# Developer Tools: gói có tick nhưng không thấy trong Settings / company panel

**Cập nhật:** 2026-04-06  
**Liên quan:** `FUNC_BUG/DEVELOPER_TOOLS_MODULE_REVIEW.md` (tổng quan module), `FUNC_LOGIC/Package_Modules_Commands.md` (gói vs nwidart)

---

## 1. Triệu chứng

- Bảng `packages.module_in_package` (JSON) **đã có** `developertools` (thường dạng object `{"54":"developertools",...}`).
- Đã chạy `php artisan packages:modules activate --module=developertools` → báo đồng bộ `module_settings`.
- Trên **Settings** (company panel) **không có** mục **Developer Tools** / **CodeMap**; tìm kiếm trong sidebar cũng không ra.
- Trang **Module Settings** có thể **không có** toggle cho Developer Tools (chỉ liệt kê `module_settings` với `is_allowed = 1`).

---

## 2. Nguyên nhân gốc

### 2.1 `updateModuleSettings()` không insert dòng mới

- File: `app/Observers/CompanyObserver.php` — method `updateModuleSettings()`.
- Khi đổi gói hoặc chạy `packages:modules activate`, code **chỉ UPDATE** các bản ghi `module_settings` **đã tồn tại** theo `company_id`.
- Nếu company **chưa bao giờ** có dòng `module_name = developertools` (admin/employee), thì **không có ID** để cập nhật → module vẫn “vô hình” dù JSON gói đúng.

`createModuleSettings()` lúc tạo company chỉ lặp qua `ModuleSetting::CLIENT_MODULES` + `ModuleSetting::OTHER_MODULES`. Trước khi bổ sung, **`developertools` không nằm trong `OTHER_MODULES`** → company cũ không có dòng tương ứng.

### 2.2 So khớp tên module với JSON gói

- `module_in_package` thường là **object** (key số + value là tên module), không phải mảng đơn giản.
- Dùng `collect(json_decode($json))` **không** `assoc` + `Collection::contains($module_name)` dễ **không khớp** (chữ hoa/thường, kiểu dữ liệu) so với `in_array` trên danh sách đã chuẩn hóa.

### 2.3 Điều kiện hiển thị menu (đã mở rộng sau 2026-04-06)

- File: `resources/views/components/setting-sidebar.blade.php` — chỉ vẽ link khi `user_can_access_developertools_module()` **và** `Route::has('developertools.index')`.
- Helper: `app/Helper/start.php` — `user_can_access_developertools_module()`.
- Trước đây gần như **bắt buộc** role tên `admin`; user có quyền cài đặt khác nhưng **không** có role `admin` thì **không** thấy menu (dù gói + DB đúng).
- **Super admin** cố ý **không** dùng entry này (thiết kế tenant).

### 2.4 Điều kiện `ModuleSetting::checkModule('developertools')`

- Cần dòng `module_settings` đúng `type` (admin/employee), `status = active`, và đúng `company_id` (scope).

---

## 3. Cách sửa đã áp dụng trong codebase (2026-04-06)

| Thay đổi                                                                   | Mục đích                                                                                                            |
| -------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| `ModuleSetting::OTHER_MODULES` thêm `developertools`                       | Company **mới** được tạo đủ dòng khi `createModuleSettings()`.                                                      |
| Migration `2026_04_06_120000_backfill_developertools_module_settings.php`  | Backfill dòng `developertools` (admin + employee) cho **mọi company** hiện có (bật nếu gói có module).              |
| `CompanyObserver::packageModuleNamesFromJson()`                            | Chuẩn hóa JSON gói → **mảng tên module chữ thường**.                                                                |
| `CompanyObserver::ensureModuleSettingsRowsForPackageModules()`             | Sau `updateModuleSettings`, **tạo** bản ghi thiếu cho mọi module có trong gói (gồm `developertools`).               |
| `createModuleSettings` / `updateModuleSettings` dùng `in_array(..., true)` | So khớp ổn định với tên đã lower-case.                                                                              |
| `user_can_access_developertools_module()`                                  | Cho phép **admin** **hoặc** `manage_module_setting == 'all'`, vẫn kiểm tra gói + `checkModule` + không super admin. |

**Test tham chiếu:** `tests/Unit/CompanyObserverPackageModulesTest.php`, `tests/Feature/ModuleSettingDeveloperToolsConstantTest.php`.

---

## 4. Việc vận hành sau khi deploy

1. `php artisan migrate` (chạy migration backfill nếu môi trường chưa có).
2. Chạy lại sync gói (để chạy `updateModuleSettings` + ensure):  
   `php artisan packages:modules activate --module=developertools`
3. **Đăng xuất / đăng nhập lại** (session `user_roles` / cache user modules).
4. Xác nhận Nwidart: `storage/app/modules_statuses.json` có `"DeveloperTools": true` (để route đăng ký).
5. Nếu vẫn không thấy: thử URL trực tiếp `.../account/developertools` — 403 xem message; 404 kiểm tra module/route.

---

## 5. Kiểm tra nhanh trên DB

- `packages`: cột `module_in_package` có chuỗi chứa `developertools` (value, không chỉ key).
- `module_settings`: có 2 dòng (hoặc ít nhất dòng `type = admin`) với `module_name = developertools`, `company_id` đúng, `status = active`, `is_allowed = 1`.

---

## 6. Ghi chú

- **Pricing / marketing UI** “gói có Developer Tools” phải khớp **JSON thật** trên bảng `packages`; nếu chỉ sửa UI mà không cập nhật `module_in_package`, app vẫn coi như không có module.
- Module **DeveloperTools** vẫn phụ thuộc **Nwidart** load route; nếu tắt module trong `modules_statuses.json`, `Route::has('developertools.index')` = false → không vẽ menu dù `module_settings` đúng.
