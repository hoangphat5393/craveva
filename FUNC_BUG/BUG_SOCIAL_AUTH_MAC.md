# Cài đặt đăng nhập mạng xã hội — lỗi `The MAC is invalid`

**Mã:** `AUTH-SOCIAL-001` · **Sổ lỗi:** [`SO_LOI.md`](SO_LOI.md)

## Triệu chứng

- `account/settings/social-auth-settings` → `DecryptException: The MAC is invalid` khi render secret (`google.blade.php`, v.v.).

## Nguyên nhân

- `SocialAuthSetting` cast `*_secret_id` = `encrypted`.
- DB copy từ môi trường khác nhưng **`APP_KEY` khác** → decrypt fail khi Blade in `value="{{ $credentials->... }}"`.

## Fix đã áp dụng (code)

- Input secret: `value=""` (không decrypt/hiện secret cũ trên UI) — các file `resources/views/social-login-settings/ajax/*.blade.php`.

## Vận hành

- Sau khi import DB từ môi trường khác: đồng bộ `APP_KEY` từ đúng môi trường nguồn hoặc nhập lại secret với `APP_KEY` hiện tại.
- Nhập lại Client ID/Secret từng provider và Save (tạo payload mới với `APP_KEY` hiện tại).

## Phạm vi

- Bảng `social_auth_settings` — cấu hình **global** (SaaS: Super Admin; self-hosted: có thể trong company settings tùy `isNonCraveva()`).
