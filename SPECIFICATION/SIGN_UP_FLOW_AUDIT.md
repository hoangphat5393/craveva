# Rà soát luồng đăng ký (Sign up) — Front & Super Admin

Tài liệu tra cứu theo codebase Laravel đa tenant; dùng cho QA, onboarding dev, và kiểm thử E2E.

---

## 1. Điều kiện load route (quan trọng)

Trong `App\Providers\RouteServiceProvider`, file `routes/SuperAdmin/web-public.php` (chứa resource `/signup`) chỉ được đăng ký khi **`isCraveva()` trả về true** (cùng nhóm với `mapSuperAdminRoutes`). Nếu build không phải Craveva, luồng front signup trong file này **có thể không tồn tại** — cần xác nhận môi trường staging/production.

---

## 2. Front — đăng ký company (tenant mới)

| Thành phần       | Vị trí                                                                                                                                                                                                   |
| ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Route**        | `routes/SuperAdmin/web-public.php`: `Route::resource('signup', CompanyRegisterController::class, ['only' => ['index', 'store']])` trong group `as => 'front.'`, middleware `['disable-frontend', 'web']` |
| **Tên route**    | `front.signup.index` (GET `/signup`), `front.signup.store` (POST `/signup`)                                                                                                                              |
| **Controller**   | `App\Http\Controllers\SuperAdmin\CompanyRegisterController`                                                                                                                                              |
| **Form request** | `App\Http\Requests\SuperAdmin\Register\StoreRequest`                                                                                                                                                     |
| **Base**         | `FrontBaseController`                                                                                                                                                                                    |

### View có thể được chọn (`CompanyRegisterController@index`)

- `super-admin.saas.register` nếu `front_design == 1`
- `super-admin.front.register` nếu không
- `super-admin.register` nếu `frontend_disable` **hoặc** `setup_homepage == 'custom'`

### Middleware `DisableFrontend`

Nếu `frontend_disable` và request **không phải** `front.signup.index` và **không** ajax → redirect `login`. Trang signup index được **ngoại lệ** (vẫn mở được).

### Luồng `store()` (tóm tắt)

1. **`registration_open`** — nếu tắt → `abort_403('Registration Disabled')`.
2. **reCAPTCHA** (khi `google_recaptcha_status == 'active'`) — `recaptchaValidate()`; v2 dùng `g-recaptcha-response`, v3 dùng `g_recaptcha` (xem `StoreRequest` + `GlobalSetting::validateGoogleRecaptcha`).
3. Tạo `Company`, gán `sub_domain` nếu `module_enabled('Subdomain')`.
4. `addUser()` → `UserAuth::createUserAuthCredentials`, gán role admin, v.v.
5. **`company_need_approval`**: nếu bật → flash `company_approval_pending`, redirect `front.signup.index`, **không** `Auth::loginUsingId` (theo nhánh hiện tại).
6. Nếu **không** cần duyệt và **không** Subdomain → auto login bằng `user_auth_id`.
7. **`email_verification`**: gửi mail verify qua `sendEmailVerificationNotification()` chỉ khi `email_verification && !company_need_approval && !module_enabled('Subdomain')`.
8. **`NewUser` notification** (có password) — lỗi gửi mail có thể ném `TransportException` → bắt riêng, rollback, message SMTP.

### Module Subdomain (`Modules/Subdomain/Routes/web.php`)

- Đăng ký thêm **GET** `signup` → cùng tên route `front.signup.index` với middleware `SubdomainCheck`.
- **POST** `front.signup.store` trong module **không** định nghĩa lại — form thường post tới route resource từ `web-public` **nếu** route đó được load; cần **kiểm tra thực tế** khi bật Subdomain (trùng tên route / thứ tự đăng ký route).

---

## 3. Super Admin — company & duyệt

| Thành phần        | Vị trí                                                                                                                    |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **Route**         | `routes/SuperAdmin/web.php`, prefix `account`, middleware `auth` + `super-admin`                                          |
| **Resource**      | `Route::resource('companies', CompanyController::class)` → `superadmin.companies.*`                                       |
| **Duyệt company** | `POST account/companies/approve_company` → `CompanyController@approveCompany`, tên `superadmin.companies.approve_company` |
| **Controller**    | `App\Http\Controllers\SuperAdmin\CompanyController`                                                                       |
| **Form requests** | `App\Http\Requests\SuperAdmin\Company\StoreRequest`, `UpdateRequest`                                                      |

`approveCompany()`: set `company.approved = 1`, cập nhật `admin_approval` cho user thuộc company, gửi notification `CompanyApproved`.

**Company chưa duyệt** khi đăng ký public: liên quan `company_need_approval` trong `CompanyObserver` (khi người tạo không phải superadmin).

---

## 4. Super Admin — cài đặt Sign up (front SaaS)

| Thành phần     | Vị trí                                                                                                                                                                 |
| -------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Route**      | Group `front-settings`: `Route::resource('sign-up-setting', SignUpController::class)->only(['index', 'update'])` + `signup_setting.lang`, `signup_setting.update_lang` |
| **Controller** | `App\Http\Controllers\SuperAdmin\FrontSetting\SignUpController`                                                                                                        |
| **View**       | `super-admin.front-setting.sign-up-setting.index`, partial AJAX `...sign-up-setting.ajax.lang`                                                                         |
| **Quyền**      | `GlobalSetting::validateSuperAdmin('manage_superadmin_front_settings')`                                                                                                |

**Các cờ cập nhật trong `update()` (GlobalSetting):**  
`registration_open`, `enable_register`, `sign_up_terms`, `terms_link`, `sign_up_phone_field`, `sign_up_phone_required` (+ message theo ngôn ngữ qua `SignUpSetting`, `FrontDetail::sign_in_show`).

**Xác minh email sau đăng ký (OTP):**  
`POST account/signup/verifyEmail` → `SignUpController@verifyEmail`, route `superadmin.signup.verifyEmail` (user đã đăng nhập).

---

## 5. Cài đặt Sign up phía tenant (khác Super Admin SaaS)

| Thành phần     | Vị trí                                                                                 |
| -------------- | -------------------------------------------------------------------------------------- |
| **Route**      | `routes/web-settings.php`: resource `sign-up-settings` → `SignUpSettingController`     |
| **Controller** | `App\Http\Controllers\SignUpSettingController`                                         |
| **View**       | `sign-up-settings.index`                                                               |
| **Phạm vi**    | Chủ yếu **điều khoản** (`sign_up_terms`, `terms_link`), quyền `manage_company_setting` |

Đây là **kênh cấu hình khác** với màn Super Admin Front settings → Sign up; khi test cần phân biệt ai chỉnh và `global_setting` dùng chung.

---

## 6. Luồng liên quan khác (không phải “signup company”)

- **Client signup** (khách đăng ký vào một company): `front.client-signup` / `front.client-register`, `FrontendController` — **không** phải `CompanyRegisterController`.
- **Fortify `CreateNewUser`**: đăng ký client khi `allow_client_signup` — **khác** luồng tạo company mới.

---

## 7. Checklist test E2E (gợi ý)

### Front (`CompanyRegisterController`)

1. GET `/signup` khi chưa login: form hiển thị đúng variant view (saas / front / register).
2. `registration_open = 0`: POST → 403 / thông báo đăng ký tắt.
3. `registration_open = 1`, happy path: company + admin tạo đúng, redirect/login đúng theo `company_need_approval` và Subdomain.
4. `company_need_approval = 1`: không auto login (non-subdomain), flash message, company `approved = 0`, Super Admin duyệt được và user đăng nhập sau đó.
5. `email_verification = 1` (kết hợp không duyệt, không subdomain): mail/verify flow; sau verify có thể cần `superadmin.signup.verifyEmail` tùy UI.
6. reCAPTCHA v2: thiếu `g-recaptcha-response` → validation; v3: token `g_recaptcha` + `recaptchaValidate`.
7. `sign_up_terms = yes`: thiếu checkbox → lỗi; `sign_up_phone_field` / `sign_up_phone_required` nếu bật.
8. Subdomain bật: subdomain required, regex, `banned_sub_domain`, `unique:companies,sub_domain`, `prepareForValidation` gắn suffix domain.

### Super Admin

9. Tạo company thủ công (`superadmin.companies.store`): dữ liệu, currency, địa chỉ, edit sau tạo không 500.
10. Duyệt (`superadmin.companies.approve_company`): `approved`, `admin_approval`, notification.
11. Front settings Sign up: bật/tắt `registration_open`, đổi message đa ngôn ngữ, phone fields — rồi lặp lại bước 2–4 trên front.

---

## 8. Edge case & điểm dễ lỗi

| Edge case                          | Ghi chú theo code                                                                                                                                                                                                                                          |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Email trùng**                    | `StoreRequest`: rule `check_superadmin`; nếu đã có `company_email` trùng thì ép `unique:users,email`; nếu user tồn tại và có role `employee` thì thêm `unique:users` — cần test từng trường hợp (email superadmin, email company cũ, email user employee). |
| **SMTP lỗi**                       | `TransportException` trong `store()` → rollback, `Reply::error` với `smtp_error`; có thể khi `NewUser` gửi mail.                                                                                                                                           |
| **Đăng ký tắt**                    | `registration_open` trong controller; `DisableFrontend` có thể chặn toàn bộ front trừ `front.signup.index`.                                                                                                                                                |
| **Frontend tắt + custom homepage** | Dùng view `super-admin.register` — vẫn test POST/redirect.                                                                                                                                                                                                 |
| **Đã đăng nhập mở `/signup`**      | `index()` redirect về login (tenant) hoặc login superadmin.                                                                                                                                                                                                |
| **Trùng route name Subdomain**     | GET `front.signup.index` đăng ký ở cả `web-public` và module Subdomain — xác minh môi trường không bị override sai.                                                                                                                                        |
| **Thiếu default company address**  | ảnh hưởng màn Super Admin edit company (xử lý riêng nếu đã merge trong codebase).                                                                                                                                                                          |

---

## 9. Kết quả rà soát bug (đã xử lý trong code)

| Mức độ     | Vấn đề                                              | Mô tả                                                                                                                                                                                                                                                           | Xử lý                                                                                                                                                                                     |
| ---------- | --------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Cao        | **`enable_register` không khớp server/UI**          | `super-admin.front.register` đã kiểm tra cả `registration_open` và `enable_register`, nhưng `CompanyRegisterController@store` và theme SaaS / `super-admin.register` chỉ dựa vào `registration_open` → có thể POST tạo company khi “ẩn đăng ký” hoặc ngược lại. | `store()` thêm `abort_403` khi `!enable_register`; view SaaS và `super-admin.register` đồng bộ điều kiện hiển thị form.                                                                   |
| Cao        | **Validation subdomain chỉ trả về một rule**        | `StoreRequest::rules()` `return` sớm chỉ với `sub_domain` khi regex fail → **bỏ qua** validate `company_name`, `email`, `password`, v.v.                                                                                                                        | Bỏ `return` sớm; gộp `regex` vào chuỗi rule `sub_domain` khi bật module Subdomain.                                                                                                        |
| Cao        | **Ghi đè mật khẩu `UserAuth` khi email đã tồn tại** | `createUserAuthCredentials` tái sử dụng `UserAuth` cùng email; `addUser` luôn `update` password → có thể đổi mật khẩu tài khoản đang dùng cho workspace khác.                                                                                                   | Chỉ cập nhật password khi `$userAuth->wasRecentlyCreated`. Email welcome: mật khẩu chỉ gửi khi auth mới; nếu auth cũ thì `NewUser` với password rỗng (template dùng “previous password”). |
| Trung bình | **`google_recaptcha_v2` khi `$global` null**        | Truy cập `$global->google_recaptcha_v2_status` không qua kiểm tra null (trường hợp DB chưa seed).                                                                                                                                                               | Dùng `$global && $global->google_recaptcha_v2_status == 'active'`.                                                                                                                        |
| Trung bình | **Duyệt company gọi notify trên null**              | `CompanyController@approveCompany` gọi `$user->notify(...)` khi `firstActiveAdmin` trả về `null` → 500.                                                                                                                                                         | Chỉ gửi notification khi `$user` khác null.                                                                                                                                               |
| Thấp       | **Nhánh `Reply::error('Recaptcha not validated')`** | `recaptchaValidate()` khi fail thường **ném** `ValidationException` thay vì trả về `false`, nên nhánh `! $this->recaptchaValidate()` hầu như không chạy.                                                                                                        | Giữ nguyên hành vi (exception vẫn hợp lệ); có thể dọn dead code sau nếu muốn.                                                                                                             |

**Test tự động:** `tests/Feature/CompanyRegisterGateTest.php` — chặn signup khi `enable_register = 0`. Case subdomain đa field được bật khi module Subdomain active.

---

## 10. Tham chiếu file nhanh

| Mục                          | File                                                                   |
| ---------------------------- | ---------------------------------------------------------------------- |
| Route front signup           | `routes/SuperAdmin/web-public.php`                                     |
| Controller đăng ký company   | `app/Http/Controllers/SuperAdmin/CompanyRegisterController.php`        |
| Validation                   | `app/Http/Requests/SuperAdmin/Register/StoreRequest.php`               |
| Super Admin companies        | `app/Http/Controllers/SuperAdmin/CompanyController.php`                |
| Cài đặt signup (SaaS)        | `app/Http/Controllers/SuperAdmin/FrontSetting/SignUpController.php`    |
| Điều khoản (tenant settings) | `app/Http/Controllers/SignUpSettingController.php`                     |
| Subdomain routes             | `Modules/Subdomain/Routes/web.php`                                     |
| Đăng ký route Craveva        | `app/Providers/RouteServiceProvider.php` (`mapSuperAdminPublicRoutes`) |

---

_Cập nhật: mục 9 ghi nhận bug đã sửa; có thể đồng bộ với `MASTER_DOCUMENTATION.md` khi luồng nghiệp vụ thay đổi._
