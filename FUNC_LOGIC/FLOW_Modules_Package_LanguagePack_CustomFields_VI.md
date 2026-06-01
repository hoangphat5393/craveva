# Flow: Package (nwidart), LanguagePack & Custom fields (multi-tenant)

Tài liệu gộp: **lệnh bật module / gói**, **LanguagePack**, **custom fields theo company**, và **audit**.  
Thay thế: `FLOW_LanguagePack_Module.md`, `Package_Modules_Commands.md`.

---

## Mục lục

1. [Custom fields — User, Company, dữ liệu](#1-custom-fields--user-company-dữ-liệu)
2. [Module LanguagePack — lệnh Artisan & xử lý khi không hiện dịch](#2-module-languagepack--lệnh-artisan--xử-lý-khi-không-hiện-dịch)
3. [Laravel modules (nwidart) & lệnh `packages:modules`](#3-laravel-modules-nwidart--lệnh-packagesmodules)
4. [Tóm tắt lệnh](#4-tóm-tắt-lệnh)

**Tham chiếu thêm:** CF theo module — xem bảng trong file này (audit snapshot `CF_SYSTEMWIDE_AUDIT_VI.md` đã retire pass 6).

---

## 1. Custom fields — User, Company, dữ liệu

### 1.1. Bảng và `company_id`

| Bảng                      | Vai trò                                                              | `company_id`                                            |
| ------------------------- | -------------------------------------------------------------------- | ------------------------------------------------------- |
| **`custom_field_groups`** | Nhóm theo module (`model` = FQCN)                                    | **Có** — mỗi công ty một nhóm cho từng loại entity      |
| **`custom_fields`**       | Định nghĩa field (label, type, values JSON)                          | **Có** — gắn công ty (observer khi tạo)                 |
| **`custom_fields_data`**  | Giá trị trên từng bản ghi (`model` + `model_id` + `custom_field_id`) | **Không** — cách ly qua `custom_field_id` → group/field |

Giá trị CF **không** lưu `company_id` trực tiếp; tenant được suy ra từ chuỗi **field → group → company** và khớp với **`company_id` trên entity** (Product, ClientDetails, …).

### 1.2. Sơ đồ quan hệ (ASCII)

**Luồng định nghĩa field (SuperAdmin / Settings):**

```text
                    +----------+
                    |  User    |
                    | (login)  |
                    +----+-----+
                         |
                         | thuộc
                         v
+----------+       +-------------+       +----------------------+
| Company  |<------| custom_     |       | custom_fields         |
| (tenant) |       | field_      |<------| (company_id)         |
+----------+       | groups      |       +----------+-----------+
      ^             | (company_id)|                  |
      |             +------+------+                  |
      |                    |                         |
      |                    +-------------------------+
      |                    |
      |             +------v------+
      +-------------+ Entity      |
      |  (Product,  | (company_id)|
      |   Invoice,  +------+------+
      |   …)               |
      |                    |
      +--------------------+------------------+
                           |
                    +------v-------------+
                    | custom_fields_data |
                    | model, model_id,   |
                    | custom_field_id,   |
                    | value              |
                    +--------------------+
```

**Tóm tắt:** User đăng nhập trong **Company** → chỉ thấy/sửa CF thuộc **group/field** của company đó (`CompanyScope` trên Eloquent). **`custom_fields_data`** neo `model_id` tới entity; entity có `company_id` phải **khớp** với group của field (đã có lệnh audit).

### 1.3. Code liên quan (tham chiếu)

- **`App\Traits\CustomFieldsTrait`**: `getCustomFieldGroups()`, `getCustomFieldsData()` (đã lọc `custom_field_groups.company_id` khi đọc raw SQL), `updateCustomFieldData()`.
- **`App\Models\CustomField`**: `exportCustomFields()`, `resolveSelectFieldDisplayValue()` (select không crash khi value legacy).
- **`App\Observers\CustomFieldsObserver`**: gán `company_id` khi tạo field.

### 1.4. Nhóm CF có `company_id = NULL`

Có thể là **legacy** (migration/seed cũ insert không set `company_id`) hoặc bản ghi orphan. **Không** nhất thiết là lỗi “trộn company” trong audit SQL nếu không có dòng `custom_fields_data` lệch; vẫn nên dọn tay (gán đúng company hoặc xóa nếu không dùng).

### 1.5. Lệnh audit (local / server)

```bash
php artisan custom-fields:audit
```

- Kiểm tra: `custom_fields.company_id` vs `custom_field_groups.company_id`; và một số entity (Product, Client, Invoice, …) xem `custom_fields_data` có trỏ field của **company khác** với entity hay không.
- **Không** sửa DB; exit `0` = không phát hiện lệch trong phạm vi kiểm tra.
- Khi in FQCN ra console, dùng dạng `App/Models/...` để tránh Symfony Console hiểu nhầm `\` (Windows).

---

## 2. Module LanguagePack — lệnh Artisan & xử lý khi không hiện dịch

Module **LanguagePack** cung cấp 2 lệnh console: đồng bộ key từ code vào kho ngôn ngữ, và publish bản dịch từ kho ra app/modules.

**File lệnh:** `Modules/LanguagePack/Console/`

- `PublishTranslationCommand.php` → `languagepack:publish-translation`
- `SyncKeysCommand.php` → `languagepack:sync-keys`

### 2.1. `languagepack:publish-translation`

**Công dụng:** Publish toàn bộ bản dịch từ LanguagePack ra thư mục `lang` của app và từng module.

**Cách hoạt động:**

1. Gọi `LanguagePackController::publishAll()`.
2. Lấy danh sách ngôn ngữ từ `LanguageSetting::all()`.
3. Với mỗi `language_code`:
    - **App:** Xóa thư mục `lang_path($languageCode)` (nếu có), rồi copy toàn bộ từ `Modules/LanguagePack/Languages/app/{code}` vào đó.
    - **Từng module:** Với mỗi module (qua `Module::all()`), xóa `module_path($module, 'Resources/lang/'.$languageCode)` (nếu có), rồi copy từ `Modules/LanguagePack/Languages/modules/{module}/{code}` vào đó.

**Kết quả:** File dịch trong `Languages/app/` và `Languages/modules/{ModuleName}/` được đẩy ra `lang/` và `Modules/{Name}/Resources/lang/` để app và modules dùng khi chạy.

**Ví dụ chạy:**

```bash
php artisan languagepack:publish-translation
```

Không có tham số hay option. Tương đương với việc bấm "Publish All" trên giao diện Language Settings.

### 2.2. `languagepack:sync-keys`

**Công dụng:** Quét code (PHP, Blade) tìm các key dịch (`__()`, `trans()`, `@lang`, `Lang::get`, `Lang::trans`), parse key theo chuẩn vendor::file.path, rồi **thêm key thiếu** vào các file ngôn ngữ trong `Modules/LanguagePack/Languages/` (không ghi đè key đã có).

**Cách hoạt động:**

1. **Quét thư mục** (mặc định: `app`, `resources`, `Modules`) — chỉ xử lý file `.php` và `.blade.php`.
2. **Regex** bắt chuỗi trong `__('...')`, `trans("...")`, `@lang('...')`, `Lang::get('...')`, `Lang::trans('...')`.
3. **Lọc key:** bỏ key rỗng, quá ngắn, hoặc chứa `{{`, `${`.
4. **Parse key:**
    - Có `::` → vendor (module) + path: ví dụ `purchase::modules.inventory.unit` → vendor=`purchase`, file=`modules`, path=`inventory.unit`.
    - Không `::` → app: ví dụ `app.messages.success` → file=`app`, path=`messages.success`.
5. **Ghi vào LanguagePack:**
    - Key **app:** ghi vào `Languages/app/{locale}/{file}.php` (fallback locale: eng → en).
    - Key **module:** chỉ ghi nếu đã có thư mục `Languages/modules/{ModuleDir}/` (tên thư mục so khớp không phân biệt hoa thường); ghi vào `.../modules/{ModuleDir}/{locale}/{file}.php` (fallback en, eng).
    - Nếu file chưa tồn tại thì tạo file mới; nếu key đã có trong mảng thì bỏ qua. Key mới nhận giá trị mặc định `Str::title(str_replace(['_','-'], ' ', $lastSegment))`.

**Options:**

| Option      | Mô tả                                                                        |
| ----------- | ---------------------------------------------------------------------------- |
| `--paths=`  | Đường dẫn quét, phân cách bằng dấu phẩy. Mặc định: `app,resources,Modules`.  |
| `--dry-run` | Chỉ liệt kê key tìm thấy (bảng Key, Vendor, File, Path), **không ghi** file. |
| `--locale=` | Locale mặc định khi thêm key (ví dụ `eng` hoặc `en`). Mặc định: `eng`.       |

**Ví dụ:**

```bash
# Quét mặc định (app, resources, Modules), ghi vào Languages/
php artisan languagepack:sync-keys

# Chỉ quét thư mục Modules
php artisan languagepack:sync-keys --paths=Modules

# Xem key sẽ thêm, không ghi file
php artisan languagepack:sync-keys --dry-run

# Thêm key mới vào locale en
php artisan languagepack:sync-keys --locale=en
```

**Lưu ý:**

- `@choice` nằm trong comment/docblock của command nhưng **không** nằm trong `buildRegexPatterns()`, nên key dùng `@choice` sẽ bị bỏ qua khi quét.
- Key thuộc module chỉ được thêm nếu module đã có thư mục trong `Languages/modules/` (tên thư mục trùng với vendor, không phân biệt hoa thường).
- **Ví dụ kho ngôn ngữ module:** key `warehouse::app.warehouseType` (và các key `warehouse::app.*` khác) nên sửa trong repo tại `Modules/LanguagePack/Languages/modules/Warehouse/{locale}/app.php` (vd. `zh-CN`, `zh-TW`, `vi`, `en`). File `Modules/Warehouse/Resources/lang/{locale}/app.php` là bản **publish** ra; sau khi sửa LanguagePack cần chạy `languagepack:publish-translation` để đồng bộ lên module và `lang/`.

### 2.3. Workflow khuyến nghị

1. Chạy `php artisan languagepack:sync-keys` để quét code và thêm key thiếu vào LanguagePack.
2. Chỉnh sửa/dịch giá trị trong các file tại `Modules/LanguagePack/Languages/`.
3. Chạy `php artisan languagepack:publish-translation` để publish ra app và modules.

### 2.4. Lỗi Git khi `git add` (file tên không hợp lệ)

Nếu gặp:

```text
error: unable to index file 'Modules/LanguagePack/Languages/app/en/(Added By '
fatal: adding files failed
```

**Nguyên nhân:** Trong thư mục `Modules/LanguagePack/Languages/app/eng/` có một file tên dạng **`(Added By `** (dấu ngoặc + "Added By" + dấu cách), tên không chuẩn nên Git không index được.

**Cách xử lý thủ công:**

1. Mở thư mục: `...\Modules\LanguagePack\Languages\app\en\`
2. Tìm file có tên bắt đầu bằng **`(Added By`**
3. Xóa đúng file đó (chuột phải → Delete). Không xóa file khác.
4. Chạy lại `git add .`

Script hỗ trợ (nếu có trong project): `php remove-invalid-lang-file.php`. Nếu unlink/rmdir thất bại (thường gặp trên Windows với tên file đặc biệt), làm theo bước thủ công trên.

### 2.5. Đã Publish nhưng đổi ngôn ngữ vẫn không đổi / vẫn hiện key

**Nguyên nhân thường gặp:**

1. **Locale chưa cập nhật đúng sau khi chọn ngôn ngữ**  
   Admin dùng `user()->locale` (AccountBaseController). Khi bấm chọn ngôn ngữ trên header, request gọi `settings/change-language?lang=...` và lưu vào user + session. Cần session user refresh sau đổi ngôn ngữ để request tiếp theo dùng đúng locale.

2. **Thiếu file dịch cho đúng locale**  
   Publish chỉ copy những locale **có thư mục** trong `Modules/LanguagePack/Languages/app/`. Nếu chọn ngôn ngữ có `language_code` (vd: `en-SG`, `vi`) mà trong LanguagePack không có thư mục `Languages/app/en-SG/` hoặc `Languages/app/vi/`, thì sau Publish sẽ không có `lang/en-SG/` hoặc `lang/vi/` tương ứng → Laravel dùng fallback (vd: `eng`). Cần đảm bảo từng ngôn ngữ muốn dùng có thư mục trong LanguagePack rồi chạy **Publish All** (hoặc Publish từng ngôn ngữ).

3. **Thiếu key trong file dịch**  
   Nếu file `superadmin.php` (hoặc `app.php`, `modules.php`, …) của locale đó thiếu key, Laravel sẽ hiển thị key. Đảm bảo `fallback_locale` và `lang/en/` (hoặc locale mặc định) có đủ key làm dự phòng.

**Việc nên làm sau khi Publish:**

- Chọn lại ngôn ngữ trên header rồi **F5 (reload)**.
- Kiểm tra thư mục `lang/{locale}/` đã có đủ file sau khi Publish.
- Nếu vẫn thấy key: kiểm tra `config/app.php` → `fallback_locale`.

---

## 3. Laravel modules (nwidart) & lệnh `packages:modules`

### 3.1. Kết luận nhanh (lỗi Permission denied khi load module)

Lỗi dạng:

```text
include(.../Modules/Warehouse/Entities/Warehouse.php): Failed to open stream: Permission denied
```

**Không phải** do chưa chạy `packages:modules` hay `module:enable`.

- Đây là lỗi **filesystem permission** (PHP-FPM không đọc được file/thư mục module).
- Nếu module chưa bật đúng, thường gặp lỗi route/menu/module not found, **không** phải `Failed to open stream: Permission denied`.

### 3.2. Hai loại "bật module" khác nhau

| Loại                                | Lệnh / UI                                                                                    | Lưu ở đâu                                       | Ảnh hưởng                                                                             |
| ----------------------------------- | -------------------------------------------------------------------------------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------- |
| **Module trong gói (Package)**      | `activate-all` / `activate`                                                                  | `packages.module_in_package`, `module_settings` | Quyền module theo gói/công ty (menu, tính năng theo subscription).                    |
| **Custom Modules (toggle trên UI)** | `enable-custom` hoặc bật từng cái trên trang **Settings > Module Settings > Custom Modules** | `storage/app/modules_statuses.json` (nwidart)   | Bật/tắt module Laravel (Affiliate, Asset, Payroll, …); nếu tắt thì module không load. |

→ Chạy `activate-all` **không** đổi trạng thái toggle trên trang **Custom Modules**. Để tất cả toggle ON, dùng `enable-custom` hoặc `activate-all-full` (mục 3.5).

### 3.3. Nwidart có lưu DB không?

Theo cấu hình hiện tại:

- `config/modules.php` dùng `FileActivator` và `statuses-file = storage/app/modules_statuses.json`.
- Trạng thái enable/disable của **nwidart custom modules** được lưu **file JSON**, không lưu DB.

App Craveva có lớp entitlement riêng trong DB:

- `packages.module_in_package` (JSON theo package)
- `module_settings` (status/is_allowed theo company)

**Runtime thực tế — hai lớp:**

1. **Nwidart (file):** module có được load toàn hệ thống không (`modules_statuses.json`).
2. **Business (DB):** company nào được dùng module gì (`module_in_package` + `module_settings`).

### 3.4. Lệnh chính: `packages:modules`

**File:** `app/Console/Commands/PackageModulesCommand.php`

| Tham số / Option    | Mô tả                                                                            |
| ------------------- | -------------------------------------------------------------------------------- |
| `action` (bắt buộc) | `list` \| `activate-all` \| `activate` \| `enable-custom` \| `activate-all-full` |
| `--package=`        | ID package (tùy chọn; mặc định: tất cả package)                                  |
| `--module=`         | Tên module (bắt buộc khi `action=activate`)                                      |

#### Xem danh sách module và trạng thái từng package

```bash
php artisan packages:modules list
```

#### Bật toàn bộ module (theo package)

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

#### Bật một module cụ thể

**Cho mọi package:**

```bash
php artisan packages:modules activate --module=clients
```

**Cho một package:**

```bash
php artisan packages:modules activate --module=products --package=9
```

#### Bật toàn bộ Custom Modules (toggle trên Module Settings)

```bash
php artisan packages:modules enable-custom
```

- Ghi trạng thái enabled cho từng module vào `modules_statuses.json`.
- Xóa cache `craveva_plugins`, `user_modules`.
- Reload trang **Module Settings** để thấy toggle ON.

#### Bật triệt để: Package modules + Custom Modules (một lệnh)

```bash
php artisan packages:modules activate-all-full
```

- Thực hiện: `activate-all` rồi `enable-custom`.
- Sau khi chạy, reload trang **Settings > Module Settings > Custom Modules**.

**Đã chạy `activate-all` mà Custom Modules vẫn nhiều toggle OFF:**

1. Chạy thêm: `php artisan packages:modules enable-custom`
2. Hoặc lần sau dùng: `php artisan packages:modules activate-all-full`

#### Nguyên tắc vận hành

- **Custom Modules toggle** = quyết định **global** (module có load toàn hệ thống hay không).
- **Package modules** = **company nào** được dùng module gì qua `module_settings`.
- `modules_statuses.json` là file global — không đồng bộ ngược từ package từng company sang custom toggle.

Chuẩn:

1. SuperAdmin bật/tắt module ở Custom Modules (hoặc `enable-custom` khi cần bật toàn bộ).
2. Entitlement theo company vẫn do package + `module_settings`.

### 3.5. Tên module (lowercase)

Ví dụ: `clients`, `tasks`, `timelogs`, `knowledgebase`, `bankaccount`, `invoices`, `estimates`, `products`, `orders`, `reports`, `pricing`, …

Danh sách đầy đủ lấy từ bảng `modules` (trừ settings, dashboards, restApi và module disabled).

### 3.6. Lưu ý

- Sau khi chạy activate, cache user (`user_modules_{id}`) được xóa qua `CompanyObserver::updateModuleSettings`.
- Nếu bật/tắt ngoài luồng chuẩn, cần refresh cache `craveva_plugins` / `user_modules` để UI đúng.

### 3.7. Flow dữ liệu (Package → Company → Module Settings)

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

**Nguồn module:**

- Bảng `modules` là danh sách module (trừ `settings`, `dashboards`, `restApi` và module disabled).
- `packages.module_in_package` giữ JSON module của từng gói.
- `module_settings` là trạng thái runtime theo công ty (`is_allowed`, `status`, `type`).

**Khi đổi package của company:** `CompanyObserver` → `updateModuleSettings` → đồng bộ `module_settings` theo `module_in_package` → xóa cache `user_modules_{id}`.

**Runtime:** `ModuleSetting::checkModule($moduleName)`; sidebar dùng `user_modules()` (cache) + route.

### 3.8. File chính liên quan

| File                                                    | Vai trò                                                        |
| ------------------------------------------------------- | -------------------------------------------------------------- |
| `app/Models/SuperAdmin/Package.php`                     | Model package, quan hệ company                                 |
| `app/Models/ModuleSetting.php`                          | Logic module settings, checkModule                             |
| `app/Models/Module.php`                                 | Danh sách module, disabled modules                             |
| `app/Observers/CompanyObserver.php`                     | create/update module settings, clear cache                     |
| `app/Http/Controllers/SuperAdmin/PackageController.php` | CRUD package, lưu `module_in_package`                          |
| `app/Console/Commands/PackageModulesCommand.php`        | Lệnh `packages:modules`                                        |
| `app/Http/Controllers/CustomModuleController.php`       | Bật/tắt custom module + migrate + rebuild cache                |
| `config/modules.php`                                    | Cấu hình nwidart activator (file JSON)                         |
| `app/Helper/start.php`                                  | Helper `craveva_plugins()` đọc cache từ `Module::allEnabled()` |

---

## 4. Tóm tắt lệnh

| Lệnh                                                 | Mục đích                                                                                      |
| ---------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| `php artisan languagepack:publish-translation`       | Copy dịch từ `LanguagePack/Languages/` → `lang/` và `Modules/*/Resources/lang/`.              |
| `php artisan languagepack:sync-keys`                 | Quét code, thêm key thiếu vào `LanguagePack/Languages/` (`--dry-run`, `--paths`, `--locale`). |
| `php artisan packages:modules list`                  | Liệt kê module / package.                                                                     |
| `php artisan packages:modules activate-all`          | Bật module trong gói + sync `module_settings`.                                                |
| `php artisan packages:modules activate --module=...` | Bật một module (có thể `--package=`).                                                         |
| `php artisan packages:modules enable-custom`         | Bật tất cả custom module nwidart → `modules_statuses.json`.                                   |
| `php artisan packages:modules activate-all-full`     | `activate-all` + `enable-custom`.                                                             |
| `php artisan custom-fields:audit`                    | Kiểm tra lệch company trên chuỗi CF (không sửa DB).                                           |

---

_Cập nhật: gộp LanguagePack + Package/nwidart + CF; thay thế `FLOW_LanguagePack_Module.md` và `Package_Modules_Commands.md`._
