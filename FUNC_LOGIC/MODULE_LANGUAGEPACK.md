# LanguagePack Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: LanguagePack
- Alias: languagepack
- Provider: Modules\LanguagePack\Providers\LanguagePackServiceProvider
- Source root: Modules/LanguagePack/

## Business Purpose

Quản lý gói ngôn ngữ, bản dịch module và thiết lập language pack.

## Main Business Flow Draft

- Cấu hình language pack.
- Quản lý file dịch app/module.
- Áp dụng bản dịch cho UI và module.

## Code Evidence

### Routes

- Modules/LanguagePack/Routes/api.php
- Modules/LanguagePack/Routes/web.php

### Route Entry Points Snapshot

- Modules/LanguagePack/Routes/web.php:23 Route::post('language-pack/publish-all', [LanguagePackController::class, 'publishAll'])->name('language-pack.publish-all');
- Modules/LanguagePack/Routes/web.php:24 Route::post('language-pack/publish', [LanguagePackController::class, 'publish'])->name('language-pack.publish');
- Modules/LanguagePack/Routes/web.php:25 Route::post('language-pack/sync-keys', [LanguagePackController::class, 'syncKeys'])->name('language-pack.sync-keys');

### Controllers

- Modules/LanguagePack/Http/Controllers/LanguagePackController.php

### Entities / Models

- Modules/LanguagePack/Entities/LanguagePackSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/LanguagePack/Resources/views/components/publish-button.blade.php
- Modules/LanguagePack/Resources/views/module-activated-alert.blade.php
- Modules/LanguagePack/Resources/views/publish.blade.php
- Modules/LanguagePack/Resources/views/publish-all-button.blade.php
- Modules/LanguagePack/Resources/views/script.blade.php
- Modules/LanguagePack/Resources/views/sync-keys-button.blade.php

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

- docs/SYSTEM_MODULE_LANGUAGEPACK_CUSTOM_FIELDS.md

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
