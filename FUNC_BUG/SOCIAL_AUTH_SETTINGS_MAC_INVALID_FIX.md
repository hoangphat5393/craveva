# Social Auth Settings — Lỗi “The MAC is invalid.” (DecryptException)

## 1) Triệu chứng

- Truy cập trang: `account/settings/social-auth-settings` (Social Login Settings).
- Gặp lỗi:
    - `Illuminate\Contracts\Encryption\DecryptException`
    - Thông báo: `The MAC is invalid.`
- Stacktrace trỏ vào view:
    - `resources/views/social-login-settings/ajax/google.blade.php` (và các tab tương tự facebook/linkedin/twitter)
    - Dòng hiển thị `value="{{ $credentials->*_secret_id }}"`

## 2) Nguyên nhân gốc

- Model `App\Models\SocialAuthSetting` đang cast các cột secret theo kiểu `encrypted`:
    - `facebook_secret_id`
    - `google_secret_id`
    - `linkedin_secret_id`
    - `twitter_secret_id`
    - File: `app/Models/SocialAuthSetting.php`

- Khi Blade render `{{ $credentials->google_secret_id }}`, Laravel sẽ tự động **decrypt**.
- Lỗi `The MAC is invalid` thường xảy ra khi:
    - Database được restore/copy từ môi trường khác nhưng `APP_KEY` hiện tại khác với `APP_KEY` lúc dữ liệu được encrypt, hoặc
    - Dữ liệu secret trong DB bị hỏng/không đúng định dạng encrypted payload.

=> Kết quả: chỉ cần trang cố “hiển thị lại” secret đã lưu là bị crash ngay tại bước decrypt.

## 3) Chức năng này dùng để làm gì?

- Đây là trang cấu hình Social Login/OAuth credentials cho:
    - Google
    - Facebook
    - LinkedIn
    - Twitter
- Dữ liệu lưu ở bảng `social_auth_settings` và mang tính **global (toàn hệ thống)**, không theo company.
- Khi bật/tắt hoặc đổi Client ID/Secret ở đây sẽ ảnh hưởng tới các nút “Sign in with Google/Facebook/…” trên trang đăng nhập.

## 4) Bản chất nằm ở Super Admin panel hay Company panel?

### 4.1 Route (kỹ thuật)

- Route nằm dưới nhóm `prefix: account/settings`:
    - File: `routes/web-settings.php`
    - Controller: `app/Http/Controllers/SocialAuthSettingController.php`

=> Vì route là “account settings”, về mặt kỹ thuật có thể xuất hiện trong panel dạng account.

### 4.2 Menu/Quyền (thực tế dùng theo panel)

Chức năng này được hiển thị/cho phép sử dụng tùy “mode”:

- **Craveva (SaaS)**:
    - Social Login Settings là cấu hình **global**, nên được xem là **Super Admin feature**.
    - Menu được đưa vào sidebar Settings của Super Admin:
        - `resources/views/components/super-admin/setting-sidebar.blade.php`
    - Điều kiện quyền liên quan:
        - `manage_superadmin_social_settings`

- **Non-craveva (self-hosted)**:
    - Social Login Settings có thể xuất hiện trong sidebar company panel (phần settings) tùy quyền:
        - `resources/views/components/setting-sidebar.blade.php`
        - Có điều kiện `isNonCraveva()`
    - Điều kiện quyền liên quan:
        - `manage_social_login_setting`

=> Tóm lại:

- **SaaS (Craveva)**: dùng như **Super Admin panel setting** (global).
- **Self-hosted (Non-craveva)**: có thể dùng trong **Company panel** (vẫn là cấu hình chung của instance).

## 5) Cách fix đã áp dụng (an toàn + đúng security)

### 5.1 Mục tiêu

- Tránh crash do decrypt khi render secret hỏng/khác APP_KEY.
- Không hiển thị lại secret ra UI (password field) — đây cũng là best practice bảo mật.

### 5.2 Thay đổi đã thực hiện

Không còn bind giá trị secret vào input password.

Các file đã sửa:

- `resources/views/social-login-settings/ajax/google.blade.php`
- `resources/views/social-login-settings/ajax/facebook.blade.php`
- `resources/views/social-login-settings/ajax/linkedin.blade.php`
- `resources/views/social-login-settings/ajax/twitter.blade.php`

Thay đổi chính:

- Trước: `value="{{ $credentials->*_secret_id }}"`
- Sau: `value=""`

## 6) Hậu quả/ghi chú vận hành

- Sau fix, trang Settings sẽ load bình thường ngay cả khi secret cũ không decrypt được.
- Nếu trước đó `APP_KEY` bị đổi so với lúc encrypt secrets:
    - Các secrets cũ trong DB thực tế **không còn dùng được**.
    - Cần nhập lại secret đúng (Google/Facebook/LinkedIn/Twitter) và Save để tạo encrypted payload mới bằng `APP_KEY` hiện tại.
