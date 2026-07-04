# EInvoice Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: EInvoice
- Alias: einvoice
- Provider: Modules\EInvoice\Providers\EInvoiceServiceProvider, Modules\EInvoice\Providers\EventServiceProvider
- Source root: Modules/EInvoice/

## Business Purpose

Cấu hình hóa đơn điện tử cấp hệ thống và cấp công ty.

## Main Business Flow Draft

- Cấu hình e-invoice.
- Thiết lập e-invoice theo công ty.
- Các luồng invoice dùng setting này khi phát hành hóa đơn điện tử.

## Code Evidence

### Routes

- Modules/EInvoice/Routes/api.php
- Modules/EInvoice/Routes/web.php

### Route Entry Points Snapshot

- Modules/EInvoice/Routes/web.php:22 Route::get('einvoice', [EInvoiceController::class, 'settings'])->name('einvoice.settings');
- Modules/EInvoice/Routes/web.php:23 Route::get('einvoice-modal', [EInvoiceController::class, 'settingsModal'])->name('einvoice.settings_modal');
- Modules/EInvoice/Routes/web.php:29 Route::get('/', [EInvoiceController::class, 'index'])->name('index');
- Modules/EInvoice/Routes/web.php:30 Route::get('/export-xml/{id}', [EInvoiceController::class, 'exportXml'])->name('exportXml');
- Modules/EInvoice/Routes/web.php:31 Route::put('einvoice-save', [EInvoiceController::class, 'saveSettings'])->name('settings.save');
- Modules/EInvoice/Routes/web.php:32 Route::get('einvoice-client-modal/{id}', [EInvoiceController::class, 'clientModal'])->name('client_modal');
- Modules/EInvoice/Routes/web.php:33 Route::put('einvoice-client-save/{id}', [EInvoiceController::class, 'clientSave'])->name('client_save');

### Controllers

- Modules/EInvoice/Http/Controllers/EInvoiceController.php

### Entities / Models

- Modules/EInvoice/Entities/EInvoiceCompanySetting.php
- Modules/EInvoice/Entities/EInvoiceSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/EInvoice/Resources/views/client/modal.blade.php
- Modules/EInvoice/Resources/views/components/form/client.blade.php
- Modules/EInvoice/Resources/views/components/form/setting.blade.php
- Modules/EInvoice/Resources/views/form/client-create.blade.php
- Modules/EInvoice/Resources/views/form/client-edit.blade.php
- Modules/EInvoice/Resources/views/index.blade.php
- Modules/EInvoice/Resources/views/sections/finance/sidebar.blade.php
- Modules/EInvoice/Resources/views/sections/setting-sidebar.blade.php
- Modules/EInvoice/Resources/views/sections/sidebar.blade.php
- Modules/EInvoice/Resources/views/settings/index.blade.php
- Modules/EInvoice/Resources/views/settings/modal.blade.php
- Modules/EInvoice/Resources/views/settings/save-script.blade.php

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
