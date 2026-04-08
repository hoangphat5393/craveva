# Chuẩn hóa locale: bỏ `eng`, chỉ dùng `en`

**Cập nhật:** 2026-03-25  
**Mục tiêu:** Thống nhất **ISO 639-1** `en` làm tiếng Anh duy nhất trong LanguagePack và runtime; **không còn** thư mục `eng` trong repo.

---

## Git & môi trường (staging / hub / production)

| Nội dung               | Ghi chú                                                                                                                                                             |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`resources/lang/*`** | Trong `.gitignore` — **không push** bản dịch đã publish. Trên mỗi môi trường cần **publish** từ LanguagePack.                                                       |
| **Nguồn trong Git**    | `Modules/LanguagePack/Languages/app/en`, `Modules/LanguagePack/Languages/modules/<Module>/en` — **đây là nơi sửa và commit**.                                       |
| **Sau deploy / pull**  | Chạy: `php artisan migrate` (nếu có migration `eng`→`en`), rồi `php artisan languagepack:publish-translation`, `php artisan cache:clear`, `php artisan view:clear`. |

---

## Đã làm trong codebase

1. **Xóa** toàn bộ `Modules/LanguagePack/Languages/app/eng` và `Modules/LanguagePack/Languages/modules/*/eng` khỏi repo.
2. **Module LanguagePack (namespace `languagepack`)**: chuyển `Modules/LanguagePack/Resources/lang/eng/` → `.../en/` (`app.php`, `messages.php`) để `__('languagepack::...')` fallback đúng với locale `en`.
3. **`SyncKeysCommand`**: chỉ ghi key mới vào thư mục `--locale` (mặc định `en`); **bỏ** fallback sang `eng`; tự tạo thư mục locale nếu thiếu.
4. **`LanguageSettingController::createEnLocale()`**: copy từ **`languagePackPath('en')`** → `resources/lang/en` (không còn phụ thuộc `resources/lang/eng`).
5. **`translation-manager`**: cảnh báo cập nhật — nhắc publish và vị trí LanguagePack.
6. **`.gitignore`**: rule file tên xấu chuyển từ `app/eng` sang `app/en`.
7. **`scripts/merge_eng_into_en_lang.php`**: merge một lần từ backup; cần **`LANGPACK_ENG_BACKUP_ROOT`** trỏ tới bản `Languages` còn `app/eng` (từ lịch sử git hoặc zip).

---

## Database

Migration: `database/migrations/2026_03_13_100000_standardize_language_code_eng_to_en.php`

- Gộp / xóa bản ghi `language_settings.language_code = 'eng'`, chuyển FK sang `en` (landing, sign-up, front, …).

---

## Phạm vi ảnh hưởng

### Không ảnh hưởng

- **Cổng thanh toán** (Stripe, PayFast, …): locale app không dùng `eng`.
- **URL PayFast** `…/eng/process`: path **của PayFast**, không đổi.

### Cần kiểm tra sau deploy

- Ngôn ngữ mặc định công ty / user = `en`.
- Email / PDF / UI không còn trỏ tới record `eng` (sau migration).

---

## Checklist staging / hub

1. Pull branch có thay đổi này.
2. `composer install` nếu cần.
3. `php artisan migrate` (production backup trước).
4. `php artisan languagepack:publish-translation`
5. `php artisan cache:clear` && `php artisan view:clear`
6. Smoke: Settings → Language, Custom Fields, một màn hình thanh toán / hóa đơn (chỉ kiểm tra hiển thị `en`).

---

## Script merge từ backup (hiếm khi dùng)

```bash
# Windows PowerShell example: checkout old tree to a temp folder, then:
$env:LANGPACK_ENG_BACKUP_ROOT = "D:\backup\LanguagePack\Languages"
php scripts/merge_eng_into_en_lang.php --dry-run
```

---

## Tài liệu liên quan

- `FUNC_LOGIC/FLOW_Modules_Package_LanguagePack_CustomFields_VI.md` — mục LanguagePack (tránh file tên invalid trong `app/en`).
