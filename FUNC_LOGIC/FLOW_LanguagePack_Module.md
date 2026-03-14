# Hai lệnh Artisan của module LanguagePack

Module **LanguagePack** cung cấp 2 lệnh console để quản lý bản dịch: đồng bộ key từ code vào kho ngôn ngữ, và publish bản dịch từ kho ra app/modules.

**File lệnh:** `Modules/LanguagePack/Console/`

- `PublishTranslationCommand.php` → `languagepack:publish-translation`
- `SyncKeysCommand.php` → `languagepack:sync-keys`

---

## 1. `languagepack:publish-translation`

**Công dụng:** Publish toàn bộ bản dịch từ LanguagePack ra thư mục lang của app và từng module.

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

---

## 2. `languagepack:sync-keys`

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

- `@choice` nằm trong comment/docblock của command nhưng **không** nằm trong `buildRegexPatterns()`, nên key dùng `@choice` sẽ không bị quét.
- Key thuộc module chỉ được thêm nếu module đã có thư mục trong `Languages/modules/` (tên thư mục trùng với vendor, không phân biệt hoa thường).

---

## Workflow khuyến nghị (theo comment trong SyncKeysCommand)

1. Chạy `php artisan languagepack:sync-keys` để quét code và thêm key thiếu vào LanguagePack.
2. Chỉnh sửa/dịch giá trị trong các file tại `Modules/LanguagePack/Languages/`.
3. Chạy `php artisan languagepack:publish-translation` để publish ra app và modules.

---

## Lỗi Git khi `git add` (file tên không hợp lệ)

Nếu gặp:

```text
error: unable to index file 'Modules/LanguagePack/Languages/app/eng/(Added By '
fatal: adding files failed
```

**Nguyên nhân:** Trong thư mục `Modules/LanguagePack/Languages/app/eng/` có một file tên dạng **`(Added By `** (dấu ngoặc + "Added By" + dấu cách), tên không chuẩn nên Git không index được.

**Cách xử lý thủ công:**

1. Mở thư mục: `F:\web\craveva-staging\Modules\LanguagePack\Languages\app\eng\`
2. Tìm file có tên bắt đầu bằng **`(Added By`**
3. Xóa đúng file đó (chuột phải → Delete). Không xóa file khác.
4. Chạy lại `git add .`

Script hỗ trợ (chỉ thao tác trong project, chỉ nhắm file trên): chạy `php remove-invalid-lang-file.php` trong thư mục project. Nếu unlink/rmdir thất bại (thường gặp trên Windows với tên file đặc biệt), làm theo bước thủ công trên.

---

## Đã chạy Publish nhưng đổi ngôn ngữ trong admin vẫn không đổi / vẫn hiện key

**Nguyên nhân thường gặp:**

1. **Locale chưa cập nhật đúng sau khi chọn ngôn ngữ**  
   Admin dùng `user()->locale` (AccountBaseController). Khi bấm chọn ngôn ngữ trên header (dropdown), request gọi `settings/change-language?lang=...` và lưu vào user + session. Đã chỉnh: sau khi đổi ngôn ngữ, session user được refresh (`session(['user' => $setting->fresh()])`) để request tiếp theo dùng đúng locale.

2. **Thiếu file dịch cho đúng locale**  
   Publish chỉ copy những locale **có thư mục** trong `Modules/LanguagePack/Languages/app/`. Nếu chọn ngôn ngữ có `language_code` (vd: `en-SG`, `vi`) mà trong LanguagePack không có thư mục `Languages/app/en-SG/` hoặc `Languages/app/vi/`, thì sau Publish sẽ không có `lang/en-SG/` hoặc `lang/vi/` tương ứng → Laravel dùng fallback (vd: `eng`). Cần đảm bảo từng ngôn ngữ muốn dùng có thư mục trong LanguagePack rồi chạy **Publish All** (hoặc Publish từng ngôn ngữ).

3. **Thiếu key trong file dịch**  
   Nếu file `superadmin.php` (hoặc `app.php`, `modules.php`, …) của locale đó thiếu key (vd: `dashboard.totalCompany`, `dashboard.earningReports`), Laravel sẽ hiển thị key. Đã bổ sung đủ key dashboard trong `en` và `eng`; các locale khác cần có cùng cấu trúc hoặc sẽ fallback sang `fallback_locale` (config `app.fallback_locale` = `eng`).

**Việc nên làm sau khi Publish:**

- Chọn lại ngôn ngữ trên header (vd: English, Singapore, Tiếng Việt) rồi **F5 (reload)** để request mới dùng đúng locale.
- Kiểm tra thư mục `lang/{locale}/` (vd: `lang/vi/`, `lang/en-SG/`) đã có đủ file `superadmin.php`, `app.php`, `messages.php`, … sau khi Publish.
- Nếu vẫn thấy key: kiểm tra `config/app.php` → `fallback_locale` (mặc định `en`) và đảm bảo `lang/en/` có đủ key làm bản dự phòng.

**Đã chỉnh (Super Admin vẫn hiện key sau khi republish):**

- **Locale hợp lệ:** Trong `AccountBaseController::common()`, nếu `user()->locale` trống hoặc không có thư mục `lang/{locale}/`, app sẽ dùng `fallback_locale` rồi `app.locale` để luôn có locale có file dịch → panel dùng bản dịch thay vì hiện key.
- **Fallback mặc định:** `config/app.php` dùng `fallback_locale` = `env('APP_FALLBACK_LOCALE', 'en')` để Laravel fallback về `lang/en/` khi thiếu key.

---

## Tóm tắt

| Lệnh                                           | Công dụng chính                                                                                                             |
| ---------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `php artisan languagepack:publish-translation` | Copy bản dịch từ `LanguagePack/Languages/` → `lang/` và `Modules/*/Resources/lang/` cho mọi ngôn ngữ trong LanguageSetting. |
| `php artisan languagepack:sync-keys`           | Quét code, tìm key dịch, thêm key thiếu vào `LanguagePack/Languages/` (có thể dùng `--dry-run`, `--paths`, `--locale`).     |
