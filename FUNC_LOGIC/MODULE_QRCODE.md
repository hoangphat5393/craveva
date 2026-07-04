# QRCode Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: QRCode
- Alias: qrcode
- Provider: Modules\QRCode\Providers\QRCodeServiceProvider, Modules\QRCode\Providers\EventServiceProvider
- Source root: Modules/QRCode/

## Business Purpose

Quản lý QR code data và cấu hình QR.

## Main Business Flow Draft

- Tạo QR code data.
- Cấu hình QR code setting.
- Public/scan flow dùng dữ liệu QR đã tạo.

## Code Evidence

### Routes

- Modules/QRCode/Routes/api.php
- Modules/QRCode/Routes/web.php

### Route Entry Points Snapshot

- Modules/QRCode/Routes/web.php:22 Route::get('/download/{id}/{format}', [QRCodeController::class, 'download'])->name('download');
- Modules/QRCode/Routes/web.php:23 Route::get('fields/{type}', [QRCodeController::class, 'fields'])->name('fields');
- Modules/QRCode/Routes/web.php:24 Route::post('preview', [QRCodeController::class, 'preview'])->name('preview');
- Modules/QRCode/Routes/web.php:28 Route::resource('qrcode', QRCodeController::class)->except(['update']);

### Controllers

- Modules/QRCode/Http/Controllers/QRCodeController.php

### Entities / Models

- Modules/QRCode/Entities/QrCodeData.php
- Modules/QRCode/Entities/QRCodeSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/QRCode/Resources/views/qrcode/ajax/create.blade.php
- Modules/QRCode/Resources/views/qrcode/ajax/edit.blade.php
- Modules/QRCode/Resources/views/qrcode/create.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/email.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/event.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/geo.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/paypal.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/skype.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/sms.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/tel.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/text.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/upi.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/url.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/whatsapp.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/wifi.blade.php
- Modules/QRCode/Resources/views/qrcode/fields/zoom.blade.php
- Modules/QRCode/Resources/views/qrcode/index.blade.php
- Modules/QRCode/Resources/views/qrcode/qr-placeholder.blade.php
- Modules/QRCode/Resources/views/qrcode/show.blade.php
- Modules/QRCode/Resources/views/sections/sidebar.blade.php

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
