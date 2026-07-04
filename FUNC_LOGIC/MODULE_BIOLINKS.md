# Biolinks Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Biolinks
- Alias: biolinks
- Provider: Modules\Biolinks\Providers\BiolinksServiceProvider, Modules\Biolinks\Providers\EventServiceProvider
- Source root: Modules/Biolinks/

## Business Purpose

Quản lý trang bio link công khai và các block nội dung.

## Main Business Flow Draft

- Tạo biolink.
- Thêm/sắp xếp block nội dung.
- Public page hiển thị theo cấu hình biolink.

## Code Evidence

### Routes

- Modules/Biolinks/Routes/web.php

### Route Entry Points Snapshot

- Modules/Biolinks/Routes/web.php:9 Route::get('bio/{slug}', [BiolinkPageController::class, 'index'])->name('biolink.index');
- Modules/Biolinks/Routes/web.php:10 Route::post('bio-page/{slug}', [BiolinkPageController::class, 'checkPassword'])->name('biolink.check-password');
- Modules/Biolinks/Routes/web.php:11 Route::post('sensitive-warning/{slug}', [BiolinkPageController::class, 'checkSensitive'])->name('biolink.check-sensitive');
- Modules/Biolinks/Routes/web.php:12 Route::get('biolink-public/open-email-modal', [BiolinkPageController::class, 'emailModal'])->name('biolink.open-email-modal');
- Modules/Biolinks/Routes/web.php:13 Route::post('subscribe-newsletter/{id}', [BiolinkPageController::class, 'subscribe'])->name('biolink.subscribe-newsletter');
- Modules/Biolinks/Routes/web.php:14 Route::get('biolink-public/open-phone-modal', [BiolinkPageController::class, 'phoneModal'])->name('biolink.open-phone-modal');
- Modules/Biolinks/Routes/web.php:15 Route::post('phone-collector/{id}', [BiolinkPageController::class, 'phoneCollector'])->name('biolink.phone-collector');
- Modules/Biolinks/Routes/web.php:19 Route::post('biolinks/change-status', [BiolinksController::class, 'changeStatus'])->name('biolinks.change_status');
- Modules/Biolinks/Routes/web.php:20 Route::get('biolinks-preview/{id}', [BiolinksController::class, 'showPreview'])->name('biolinks.show-preview');
- Modules/Biolinks/Routes/web.php:21 Route::get('biolinks/{id}/edit-slug', [BiolinksController::class, 'editSlug'])->name('biolinks.editSlug');
- Modules/Biolinks/Routes/web.php:22 Route::resource('biolinks', BiolinksController::class)->names('biolinks');
- Modules/Biolinks/Routes/web.php:24 Route::resource('biolink-settings', BiolinkSettingsController::class)->names('biolink-settings')->only(['update']);
- Modules/Biolinks/Routes/web.php:26 Route::get('biolink-blocks/{id}/create/', [BiolinkBlocksController::class, 'create'])->name('biolink-blocks.create');
- Modules/Biolinks/Routes/web.php:27 Route::get('biolink-blocks/{biolinkId}/create-block/{blockId}', [BiolinkBlocksController::class, 'createBlock'])->name('biolink-blocks.createBlock');
- Modules/Biolinks/Routes/web.php:28 Route::resource('biolink-blocks', BiolinkBlocksController::class)->names('biolink-blocks')->only(['store', 'update', 'destroy']);
- Modules/Biolinks/Routes/web.php:29 Route::get('duplicate-block/{duplicateId}', [BiolinkBlocksController::class, 'duplicateBlock'])->name('biolink-blocks.duplicate');
- Modules/Biolinks/Routes/web.php:30 Route::post('biolink-blocks/sortFields', [BiolinkBlocksController::class, 'sortFields'])->name('biolink-blocks.sortFields');

### Controllers

- Modules/Biolinks/Http/Controllers/BiolinkBlocksController.php
- Modules/Biolinks/Http/Controllers/BiolinkPageController.php
- Modules/Biolinks/Http/Controllers/BiolinksController.php
- Modules/Biolinks/Http/Controllers/BiolinkSettingsController.php

### Entities / Models

- Modules/Biolinks/Entities/Biolink.php
- Modules/Biolinks/Entities/BiolinkBlocks.php
- Modules/Biolinks/Entities/BiolinkSetting.php
- Modules/Biolinks/Entities/BiolinksGlobalSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Biolinks/Resources/views/biolink-blocks/ajax/avatar-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/email-collector-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/heading-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/image-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/link-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/paragraph-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/paypal-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/phone-collector-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/socials-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/sound-cloud-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/spotify-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/threads-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/tiktok-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/twitch-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/ajax/youtube-block.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/create.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/avatar-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/email-collector-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/embeds-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/heading-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/image-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/link-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/paragraph-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/paypal-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/phone-collector-form.blade.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/socials-form.blade.php
- Modules/Biolinks/Resources/views/biolink-page/index.blade.php
- Modules/Biolinks/Resources/views/biolink-page/password-page.blade.php
- Modules/Biolinks/Resources/views/biolink-page/sensitive-warning.blade.php
- Modules/Biolinks/Resources/views/biolink-settings/advanced.blade.php

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
