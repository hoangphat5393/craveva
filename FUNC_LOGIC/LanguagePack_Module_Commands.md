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

## Tóm tắt

| Lệnh                               | Công dụng chính                                                                                                             |
| ---------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `languagepack:publish-translation` | Copy bản dịch từ `LanguagePack/Languages/` → `lang/` và `Modules/*/Resources/lang/` cho mọi ngôn ngữ trong LanguageSetting. |
| `languagepack:sync-keys`           | Quét code, tìm key dịch, thêm key thiếu vào `LanguagePack/Languages/` (có thể dùng `--dry-run`, `--paths`, `--locale`).     |
