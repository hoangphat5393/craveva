# Chuẩn hóa locale: eng → en

**Ngày:** 2026-03-13  
**Mục tiêu:** Thống nhất dùng `en` (ISO 639-1) thay cho `eng` (ISO 639-2) trong toàn bộ hệ thống.

---

## Phạm vi ảnh hưởng

### ✅ Không ảnh hưởng

- **Flow thanh toán** (Stripe, PayFast, Paystack, Razorpay): Không dùng locale app
- **Tích hợp SMS/WhatsApp/Twilio**: Dùng `company.locale` / `user.locale`, fallback `en`
- **PayFast URL** (`/eng/process`): Là path cố định của PayFast API, không liên quan locale app

### ⚠️ Cần cập nhật

| File/Module                  | Thay đổi                                           |
| ---------------------------- | -------------------------------------------------- |
| `SyncKeysCommand`            | Default `--locale=eng` → `en`, fallback path logic |
| `LanguageSettingController`  | `createEnLocale()` – logic copy eng→en             |
| `translation-manager` view   | Cập nhật warning text                              |
| `language_settings` (DB)     | Migration: `language_code = 'eng'` → `en`          |
| LanguagePack path resolution | Ưu tiên `en` thay vì `eng`                         |

---

## Các bước đã thực hiện

1. **SyncKeysCommand**: `--locale=en` mặc định; fallback path ưu tiên `en`
2. **translation-manager** view: Cập nhật warning (en là chuẩn)
3. **Migration** `2026_03_13_100000_standardize_language_code_eng_to_en.php`:
    - Nếu chỉ có `eng`: update → `en`
    - Nếu có cả `eng` và `en`: xóa bản ghi `eng`
4. **LanguageSettingController**: Giữ nguyên `createEnLocale` (copy eng→en khi cần)

---

## Lưu ý

- **LanguagePack folders**: Giữ cả `en` và `eng` trong `Languages/` vì một số locale có thể reference. Publish dùng `LanguageSetting.language_code` từ DB.
- **Sau migration**: Record `language_code='eng'` sẽ thành `en`. Nếu trùng với record `en` đã có → cần merge hoặc xử lý duplicate.
- **Chạy sau khi áp dụng:**
    ```bash
    php artisan migrate
    php artisan languagepack:publish-translation
    php artisan cache:clear
    ```
