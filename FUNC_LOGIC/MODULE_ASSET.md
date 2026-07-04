# Asset Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Asset
- Alias: asset
- Provider: Modules\Asset\Providers\AssetServiceProvider, Modules\Asset\Providers\EventServiceProvider
- Source root: Modules/Asset/

## Business Purpose

Quản lý tài sản, loại tài sản, lịch sử bàn giao/thu hồi và cấu hình tài sản.

## Main Business Flow Draft

- Tạo loại tài sản và tài sản.
- Gán/bàn giao tài sản cho người dùng hoặc bộ phận.
- Theo dõi lịch sử asset và cập nhật trạng thái.

## Code Evidence

### Routes

- Modules/Asset/Routes/api.php
- Modules/Asset/Routes/web.php

### Route Entry Points Snapshot

- Modules/Asset/Routes/web.php:21 Route::resource('assets', AssetController::class);
- Modules/Asset/Routes/web.php:25 Route::get('/asset/{asset}/history/return/{history}', [AssetHistoryController::class, 'returnAsset'])->name('assets.return');
- Modules/Asset/Routes/web.php:26 Route::resource('/asset/{asset}/history', AssetHistoryController::class)->names([
- Modules/Asset/Routes/web.php:34 Route::resource('asset-type', AssetTypeController::class);
- Modules/Asset/Routes/web.php:37 Route::resource('/asset-setting', AssetSettingController::class);

### Controllers

- Modules/Asset/Http/Controllers/AssetController.php
- Modules/Asset/Http/Controllers/AssetHistoryController.php
- Modules/Asset/Http/Controllers/AssetSettingController.php
- Modules/Asset/Http/Controllers/AssetTypeController.php

### Entities / Models

- Modules/Asset/Entities/Asset.php
- Modules/Asset/Entities/AssetHistory.php
- Modules/Asset/Entities/AssetSetting.php
- Modules/Asset/Entities/AssetType.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Asset/Resources/views/asset/ajax/create.blade.php
- Modules/Asset/Resources/views/asset/ajax/edit.blade.php
- Modules/Asset/Resources/views/asset/ajax/history.blade.php
- Modules/Asset/Resources/views/asset/ajax/history-edit.blade.php
- Modules/Asset/Resources/views/asset/ajax/lend.blade.php
- Modules/Asset/Resources/views/asset/ajax/return.blade.php
- Modules/Asset/Resources/views/asset/ajax/show.blade.php
- Modules/Asset/Resources/views/asset/create.blade.php
- Modules/Asset/Resources/views/asset/index.blade.php
- Modules/Asset/Resources/views/asset-settings/create-asset-type-settings-modal.blade.php
- Modules/Asset/Resources/views/asset-settings/edit-asset-type-settings-modal.blade.php
- Modules/Asset/Resources/views/asset-settings/index.blade.php
- Modules/Asset/Resources/views/asset-settings/type.blade.php
- Modules/Asset/Resources/views/asset-type/create.blade.php
- Modules/Asset/Resources/views/sections/setting-sidebar.blade.php
- Modules/Asset/Resources/views/sections/sidebar.blade.php

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
