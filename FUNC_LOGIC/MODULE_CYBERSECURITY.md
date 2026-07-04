# CyberSecurity Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: CyberSecurity
- Alias: cybersecurity
- Provider: Modules\CyberSecurity\Providers\CyberSecurityServiceProvider, Modules\CyberSecurity\Providers\EventServiceProvider
- Source root: Modules/CyberSecurity/

## Business Purpose

Quản lý thiết lập bảo mật, blacklist IP/email và giới hạn đăng nhập.

## Main Business Flow Draft

- Cấu hình rule bảo mật.
- Ghi nhận/kiểm tra blacklist IP hoặc email.
- Áp dụng giới hạn login expiry theo setting.

## Code Evidence

### Routes

- Modules/CyberSecurity/Routes/web.php

### Route Entry Points Snapshot

- Modules/CyberSecurity/Routes/web.php:23 Route::resource('blacklist-ip', BlacklistIpController::class);
- Modules/CyberSecurity/Routes/web.php:24 Route::resource('blacklist-email', BlacklistEmailController::class);
- Modules/CyberSecurity/Routes/web.php:25 Route::resource('login-expiry', LoginExpiryController::class);
- Modules/CyberSecurity/Routes/web.php:27 Route::resource('cybersecurity', CyberSecuritySettingController::class);

### Controllers

- Modules/CyberSecurity/Http/Controllers/BlacklistEmailController.php
- Modules/CyberSecurity/Http/Controllers/BlacklistIpController.php
- Modules/CyberSecurity/Http/Controllers/CyberSecuritySettingController.php
- Modules/CyberSecurity/Http/Controllers/LoginExpiryController.php

### Entities / Models

- Modules/CyberSecurity/Entities/BlacklistEmail.php
- Modules/CyberSecurity/Entities/BlacklistIp.php
- Modules/CyberSecurity/Entities/CyberSecurity.php
- Modules/CyberSecurity/Entities/CyberSecuritySetting.php
- Modules/CyberSecurity/Entities/LoginExpiry.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/CyberSecurity/Resources/views/sections/superadmin/setting-sidebar.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/ajax/blacklist-email.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/ajax/blacklist-ip.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/ajax/login-expiry.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/ajax/security.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/ajax/single-session.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/create-blacklist-email.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/create-blacklist-ip.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/create-login-expiry.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/edit-blacklist-email.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/edit-blacklist-ip.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/edit-login-expiry.blade.php
- Modules/CyberSecurity/Resources/views/security-settings/index.blade.php

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
