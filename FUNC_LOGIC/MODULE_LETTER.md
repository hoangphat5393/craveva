# Letter Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Letter
- Alias: letter
- Provider: Modules\Letter\Providers\LetterServiceProvider, Modules\Letter\Providers\EventServiceProvider
- Source root: Modules/Letter/

## Business Purpose

Quản lý mẫu thư và phát hành letter theo template.

## Main Business Flow Draft

- Tạo template.
- Tạo letter từ template.
- Theo dõi/cập nhật letter đã phát hành.

## Code Evidence

### Routes

- Modules/Letter/Routes/web.php

### Route Entry Points Snapshot

- Modules/Letter/Routes/web.php:22 Route::get('ajax/template/{id}', [LetterController::class, 'letterTemplate'])->name('ajax.template');
- Modules/Letter/Routes/web.php:23 Route::get('employee/{id}', [LetterController::class, 'letterEmployee'])->name('employee');
- Modules/Letter/Routes/web.php:24 Route::post('download/preview', [LetterController::class, 'downloadLetterPreviewStore'])->name('download.preview.store');
- Modules/Letter/Routes/web.php:25 Route::get('download/preview', [LetterController::class, 'downloadLetterPreview'])->name('download.preview');
- Modules/Letter/Routes/web.php:26 Route::get('download/{id}', [LetterController::class, 'downloadLetter'])->name('download');
- Modules/Letter/Routes/web.php:27 Route::resource('template', TemplateController::class);
- Modules/Letter/Routes/web.php:28 Route::resource('generate', LetterController::class);

### Controllers

- Modules/Letter/Http/Controllers/LetterController.php
- Modules/Letter/Http/Controllers/TemplateController.php

### Entities / Models

- Modules/Letter/Entities/Letter.php
- Modules/Letter/Entities/LetterSetting.php
- Modules/Letter/Entities/Template.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Letter/Resources/views/letter/ajax/create.blade.php
- Modules/Letter/Resources/views/letter/ajax/edit.blade.php
- Modules/Letter/Resources/views/letter/ajax/show.blade.php
- Modules/Letter/Resources/views/letter/create.blade.php
- Modules/Letter/Resources/views/letter/index.blade.php
- Modules/Letter/Resources/views/letter/pdf/letter.blade.php
- Modules/Letter/Resources/views/letter/pdf/preview.blade.php
- Modules/Letter/Resources/views/letter/show.blade.php
- Modules/Letter/Resources/views/sections/sidebar.blade.php
- Modules/Letter/Resources/views/template/ajax/create.blade.php
- Modules/Letter/Resources/views/template/ajax/edit.blade.php
- Modules/Letter/Resources/views/template/ajax/show.blade.php
- Modules/Letter/Resources/views/template/create.blade.php
- Modules/Letter/Resources/views/template/index.blade.php
- Modules/Letter/Resources/views/template/show.blade.php

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
