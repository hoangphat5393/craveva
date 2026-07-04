# Subdomain Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Subdomain
- Alias: subdomain
- Provider: Modules\Subdomain\Providers\SubdomainServiceProvider
- Source root: Modules/Subdomain/

## Business Purpose

Quản lý subdomain tenant và cấu hình subdomain.

## Main Business Flow Draft

- Cấu hình subdomain.
- Kiểm tra/đăng ký subdomain tenant.
- Chặn hoặc xử lý banned subdomain nếu có.

## Code Evidence

### Routes

- Modules/Subdomain/Routes/api.php
- Modules/Subdomain/Routes/web.php

### Route Entry Points Snapshot

- Modules/Subdomain/Routes/web.php:4 // Route::get('login', 'Auth\LoginController@showLoginForm')->name('login')->middleware('sub-domain-check');
- Modules/Subdomain/Routes/web.php:5 // Route::get('email-verification/{code}', 'Auth\LoginController@getEmailVerification')->name('front.get-email-verification');
- Modules/Subdomain/Routes/web.php:6 // Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
- Modules/Subdomain/Routes/web.php:7 // Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
- Modules/Subdomain/Routes/web.php:11 // Route::get('/', 'HomeController@index')->name('home');
- Modules/Subdomain/Routes/web.php:13 // Route::get('/contact', 'HomeController@contact')->name('contact');
- Modules/Subdomain/Routes/web.php:14 // Route::post('/contact-us', 'HomeController@contactUs')->name('contact-us');
- Modules/Subdomain/Routes/web.php:16 // Route::get('/feature', ['uses' => 'HomeController@feature'])->name('feature');
- Modules/Subdomain/Routes/web.php:17 // Route::get('/pricing', ['uses' => 'HomeController@pricing'])->name('pricing');
- Modules/Subdomain/Routes/web.php:19 // Route::resource('/signup', 'RegisterController', ['only' => ['index', 'store']]);
- Modules/Subdomain/Routes/web.php:25 // Route::get('signin', 'SubdomainController@workspace')->name('front.workspace');
- Modules/Subdomain/Routes/web.php:26 // Route::get('forgot-company', 'SubdomainController@forgotCompany')->name('front.forgot-company');
- Modules/Subdomain/Routes/web.php:27 // Route::post('forgot-company', 'SubdomainController@submitForgotCompany')->name('front.submit-forgot-password');
- Modules/Subdomain/Routes/web.php:28 // Route::get('super-admin-login', 'Auth\LoginController@showSuperAdminLogin')->name('front.super-admin-login');
- Modules/Subdomain/Routes/web.php:32 // Route::get('push-notify-iframe', ['uses' => 'SubdomainController@iframe'])->name('push-notify-iframe');
- Modules/Subdomain/Routes/web.php:46 Route::get('/', [FrontendController::class, 'index'])->name('front.home');
- Modules/Subdomain/Routes/web.php:47 Route::get('/contact', [FrontendController::class, 'contact'])->name('front.contact');
- Modules/Subdomain/Routes/web.php:48 Route::post('/contact-us', [FrontendController::class, 'contactUs'])->name('front.contact-us');
- Modules/Subdomain/Routes/web.php:49 Route::get('/features', [FrontendController::class, 'feature'])->name('front.feature');
- Modules/Subdomain/Routes/web.php:50 Route::get('/pricing', [FrontendController::class, 'pricing'])->name('front.pricing');

### Controllers

- Modules/Subdomain/Http/Controllers/BannedSubdomainController.php
- Modules/Subdomain/Http/Controllers/SubdomainController.php

### Entities / Models

- Modules/Subdomain/Entities/SubdomainSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Subdomain/Resources/views/forgot-company.blade.php
- Modules/Subdomain/Resources/views/forgot-subdomain.blade.php
- Modules/Subdomain/Resources/views/iframe.blade.php
- Modules/Subdomain/Resources/views/login-subdomain.blade.php
- Modules/Subdomain/Resources/views/saas/forgot-company.blade.php
- Modules/Subdomain/Resources/views/saas/workspace.blade.php
- Modules/Subdomain/Resources/views/sections/superadmin/setting-sidebar.blade.php
- Modules/Subdomain/Resources/views/super-admin/company/create.blade.php
- Modules/Subdomain/Resources/views/super-admin/company/edit.blade.php
- Modules/Subdomain/Resources/views/super-admin/company/script.blade.php
- Modules/Subdomain/Resources/views/super-admin/setting/edit.blade.php
- Modules/Subdomain/Resources/views/workspace.blade.php

## Business Rules To Confirm

- Những trạng thái chính của từng object trong module là gì.
- Object nào là master data, object nào là transaction data.
- Có cần ràng buộc company/tenant, role, permission hoặc approval riêng không.
- Có phát sinh dữ liệu kế toán, kho, invoice, payroll hoặc notification qua module khác không.
- Xóa/sửa record trong module này có ảnh hưởng module nào khác không.

## Integration Points To Audit

- Controllers gọi service/helper/model ngoài module.
- Routes hoặc menu trong core app trỏ vào module này.
- Language keys trong Modules/LanguagePack hoặc lang.
- Tests hiện có liên quan module này.
- Seed/migration và permission/module setting liên quan.

## Related Existing Docs

- Chưa map tài liệu liên quan.

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
