# Developer Tools: gói có tick nhưng không thấy trong Settings / company panel

**Cập nhật:** 2026-04-06 (bổ sung UI Module Settings + quyền impersonate 2026-04-06)  
**Liên quan:** `FUNC_BUG/DEVELOPER_TOOLS_MODULE_REVIEW.md` (tổng quan module), `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md` (gói vs nwidart)

---

## 1. Triệu chứng

- Bảng `packages.module_in_package` (JSON) **đã có** `developertools` (thường dạng object `{"54":"developertools",...}`).
- Đã chạy `php artisan packages:modules activate --module=developertools` → báo đồng bộ `module_settings`.
- Trên **Settings** (company panel) **không có** mục **Developer Tools** / **CodeMap**; tìm kiếm trong sidebar cũng không ra.
- Trang **Module Settings** trước đây **chỉ liệt kê** `module_settings` với `is_allowed = 1` → nếu gói chưa có `developertools` hoặc chưa sync, **không thấy** dòng Developer Tools (dù đã có bản ghi `is_allowed = 0`). **Đã sửa:** luôn ghép thêm dòng `developertools` (admin/employee) khi `is_allowed = 0`, kèm ghi chú “chưa trong gói”; công tắc bị khóa cho tới khi gói có module.

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

### 2.5 Impersonate / user có `manage_module_setting` nhưng không role `admin`

- `ModuleSetting::checkModule()` chọn `type` theo **role** (admin → bản ghi admin, employee → bản ghi employee).
- User chỉ có role **employee** nhưng được quyền **Module Settings** (`manage_module_setting == 'all'`) khi xem tab **Admin** vẫn thấy toggle **admin**; `checkModule('developertools')` lại đọc bản ghi **employee** → có thể **không** vào được Developer Tools dù bản ghi admin đã bật.
- **Đã sửa:** `user_can_access_developertools_module()` — nếu không có role `admin` nhưng có `manage_module_setting == 'all'` thì kiểm tra bản ghi **admin** `developertools`.

---

## 3. Cách sửa đã áp dụng trong codebase (2026-04-06)

| Thay đổi                                                                    | Mục đích                                                                                                                  |
| --------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `ModuleSetting::OTHER_MODULES` thêm `developertools`                        | Company **mới** được tạo đủ dòng khi `createModuleSettings()`.                                                            |
| Migration `2026_04_06_120000_backfill_developertools_module_settings.php`   | Backfill dòng `developertools` (admin + employee) cho **mọi company** hiện có (bật nếu gói có module).                    |
| `CompanyObserver::packageModuleNamesFromJson()`                             | Chuẩn hóa JSON gói → **mảng tên module chữ thường**.                                                                      |
| `CompanyObserver::ensureModuleSettingsRowsForPackageModules()`              | Sau `updateModuleSettings`, **tạo** bản ghi thiếu cho mọi module có trong gói (gồm `developertools`).                     |
| `createModuleSettings` / `updateModuleSettings` dùng `in_array(..., true)`  | So khớp ổn định với tên đã lower-case.                                                                                    |
| `user_can_access_developertools_module()`                                   | Có role **admin** → `checkModule`; chỉ có `manage_module_setting == 'all'` → kiểm tra bản ghi **admin** `developertools`. |
| `ModuleSetting::forTenantModuleSettingsIndex()` + `ModuleSettingController` | Tab Module Settings vẫn hiện **Developer Tools** khi `is_allowed = 0`, công tắc khóa + nhắc thêm vào gói.                 |

**Test tham chiếu:** `tests/Unit/CompanyObserverPackageModulesTest.php`, `tests/Feature/ModuleSettingDeveloperToolsConstantTest.php`, `tests/Feature/ModuleSettingDeveloperToolsVisibilityTest.php`.

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

---

## 7. `is_allowed = 0` dù “tưởng” gói đã có module — không phải lỗi code module

**Nguồn sự thật duy nhất** cho `module_settings.is_allowed` trên company panel là cột **`packages.module_in_package`** của **đúng** bản ghi gói mà company đang trỏ tới (`companies.package_id`), sau khi chạy luồng **`CompanyObserver::updateModuleSettings()`** (ví dụ `php artisan packages:modules activate --module=developertools`).

Nếu UI marketing / bảng giá có tick **Developer Tools** nhưng JSON gói trong DB **không** chứa giá trị (sau khi lower) `developertools`, hoặc company đang gắn **nhầm** `package_id`, thì `is_allowed` vẫn là **0** — đó là **lệch dữ liệu gói ↔ company**, không phải do “thiết lập phát triển module” sai.

**Việc nên kiểm tra:**

1. SQL: `SELECT id, package_id FROM companies WHERE id = ?` và `SELECT id, module_in_package FROM packages WHERE id = ?` — trong JSON phải có value `developertools` (object hoặc mảng đều được, code đã chuẩn hóa).
2. Sau khi sửa gói: chạy lại `packages:modules activate --module=developertools` (hoặc đổi gói company để kích hoạt observer).
3. Bản dịch chữ nhắc “chưa trong gói”: key `messages.moduleNotInPackage` nằm trong **LanguagePack** (`Modules/LanguagePack/Languages/app/{en,vi}/messages.php`). App khi chạy thường đọc từ **`lang/{locale}/`** sau khi publish — cần `php artisan languagepack:publish-translation` (hoặc tương đương “Publish All” trên Language Settings). Blade module settings có **fallback** tiếng Anh/Việt nếu key chưa được publish.

---

## 8. Lệnh `packages:modules activate --module=developertools` báo “đã có module” nhưng `is_allowed` vẫn 0

**Nguyên nhân (đã sửa trong code):** `PackageModulesCommand::runActivateOne()` trước đây **`continue`** ngay khi JSON gói đã chứa module → **không gọi** `CompanyObserver::updateModuleSettings()` → `module_settings` lệch gói thì **không bao giờ** được kéo lại.

**Sau sửa:** Dù gói đã có `developertools`, lệnh vẫn **đồng bộ `module_settings`** cho mọi company thuộc gói đó. Chạy lại:

`php artisan packages:modules activate --module=developertools`

**Tham chiếu test:** `tests/Feature/PackageModulesActivateResyncsModuleSettingsTest.php`.

### Bảng / cột DB liên quan

| Bảng              | Cột / ý nghĩa                                                                                           |
| ----------------- | ------------------------------------------------------------------------------------------------------- |
| `companies`       | `package_id` — company đang dùng gói nào                                                                |
| `packages`        | `module_in_package` — JSON danh sách module trong gói (chuẩn hóa value lowercase khi đồng bộ)           |
| `module_settings` | `company_id`, `module_name` (= `developertools`), `type` (`admin` / `employee`), `is_allowed`, `status` |

---

## 9. Route `/developertools` vs company panel; lỗi 403 “Developer Tools are not available for this account”

**Route chuẩn (tenant):** `GET /account/developertools` — cùng middleware với nhóm `account` (`auth`, `multi-company-select`, `email_verified`). Tên route `developertools.index` **không đổi**; `route()` sinh URL `/account/developertools`.

**Tương thích:** `GET /developertools` (root) **301** → `/account/developertools` (tránh bookmark cũ).

**Xung đột `account/settings/developertools`:** Trong `routes/web.php` có `Route::resource('settings', ...)` (prefix `account`). URI `account/settings/{setting}` trùng pattern với `developertools`; resource không đăng ký `show` nên **GET** vào đường dẫn đó chỉ trùng **PUT** `update` → lỗi **405**. **Đã thêm** `GET account/settings/developertools` redirect **301** → `developertools.index` (đặt **trước** dòng `Route::resource('settings', ...)`).

**403** đến từ `DeveloperToolsController::ensureDeveloperToolsAccess()` khi `user_can_access_developertools_module()` = false. Logic (trong `app/Helper/start.php`) yêu cầu đồng thời:

- User **không** phải super admin; có role **admin** **hoặc** quyền **`manage_module_setting` == `all`**.
- Có `company` + `package`; `checkCompanyPackageIsValid(company_id)` (cache `company_{id}_valid_package` — vượt `max_employees` gói → **false**).
- `CompanyObserver::packageModuleNamesFromJson` chứa **`developertools`**.
- Có dòng `module_settings`: `company_id`, `module_name = developertools`, `type = admin`, **`status = active`**, **`is_allowed = 1`** (truy vấn **không** dùng `CompanyScope` để khớp đúng company).

Nếu vẫn 403: `php artisan cache:clear` (xoá cache gói hợp lệ), đồng bộ lại `module_settings`, kiểm tra email đã verify (`email_verified` middleware).
