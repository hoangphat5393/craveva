# Sms Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Sms
- Alias: sms
- Provider: Modules\Sms\Providers\SmsServiceProvider
- Source root: Modules/Sms/

## Business Purpose

Quản lý SMS setting, notification setting và template id.

## Main Business Flow Draft

- Cấu hình SMS provider/notification.
- Map template id.
- Các notification dùng setting/template để gửi SMS.

## Code Evidence

### Routes

- Modules/Sms/Routes/api.php
- Modules/Sms/Routes/web.php

### Route Entry Points Snapshot

- Modules/Sms/Routes/web.php:23 Route::get('sms-setting/test-message', [SmsSettingsController::class, 'testMessage'])->name('sms-setting.test_message');
- Modules/Sms/Routes/web.php:24 Route::POST('sms-setting/send-test-message', [SmsSettingsController::class, 'sendTestMessage'])->name('sms-setting.send_test_message');
- Modules/Sms/Routes/web.php:25 Route::resource('sms-setting', SmsSettingsController::class);

### Controllers

- Modules/Sms/Http/Controllers/SmsSettingsController.php

### Entities / Models

- Modules/Sms/Entities/SmsNotificationSetting.php
- Modules/Sms/Entities/SmsSetting.php
- Modules/Sms/Entities/SmsTemplateId.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Sms/Resources/views/sections/setting-sidebar.blade.php
- Modules/Sms/Resources/views/sections/superadmin/setting-sidebar.blade.php
- Modules/Sms/Resources/views/sms/index.blade.php
- Modules/Sms/Resources/views/sms/test-message.blade.php

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
