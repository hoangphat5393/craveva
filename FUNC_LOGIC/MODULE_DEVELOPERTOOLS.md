# DeveloperTools Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: DeveloperTools
- Alias: developertools
- Provider: Modules\DeveloperTools\Providers\DeveloperToolsServiceProvider
- Source root: Modules/DeveloperTools/

## Business Purpose

Công cụ nội bộ để theo dõi file, dependency, mapping user DB, credential và log truy cập DB.

## Main Business Flow Draft

- Khai báo credential/chính sách truy cập.
- Quét hoặc lưu file/dependency.
- Ghi log truy cập DB để audit.

## Code Evidence

### Routes

- Modules/DeveloperTools/Routes/api.php
- Modules/DeveloperTools/Routes/web.php

### Route Entry Points Snapshot

- Modules/DeveloperTools/Routes/api.php:18 Route::get('developertools', fn (Request $request) => $request->user())->name('developertools');
- Modules/DeveloperTools/Routes/web.php:17 Route::redirect('developertools', '/account/developertools', 301);
- Modules/DeveloperTools/Routes/web.php:18 Route::redirect('developertools/codemap/view', '/account/developertools/codemap/view', 301);
- Modules/DeveloperTools/Routes/web.php:19 Route::redirect('funcnews', '/account/funcnews', 301);
- Modules/DeveloperTools/Routes/web.php:20 Route::redirect('funcnews/export', '/account/funcnews/export', 301);
- Modules/DeveloperTools/Routes/web.php:27 Route::get('developertools', [DeveloperToolsController::class, 'index'])->name('developertools.index');
- Modules/DeveloperTools/Routes/web.php:28 Route::post('developertools/preview-tables', [DeveloperToolsController::class, 'previewTables'])->name('developertools.preview_tables');
- Modules/DeveloperTools/Routes/web.php:29 Route::post('developertools/create-credential', [DeveloperToolsController::class, 'store'])->name('developertools.store');
- Modules/DeveloperTools/Routes/web.php:30 Route::delete('developertools/revoke/{id}', [DeveloperToolsController::class, 'destroy'])->name('developertools.destroy');
- Modules/DeveloperTools/Routes/web.php:32 Route::get('developertools/codemap/view', [DeveloperToolsController::class, 'codeMap'])->name('developertools.codemap');
- Modules/DeveloperTools/Routes/web.php:33 Route::post('developertools/codemap/scan', [DeveloperToolsController::class, 'scanCodeMap'])->name('developertools.codemap.scan');
- Modules/DeveloperTools/Routes/web.php:34 Route::get('developertools/codemap/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('developertools.codemap.export');
- Modules/DeveloperTools/Routes/web.php:36 Route::get('funcnews', [DeveloperToolsController::class, 'codeMap'])->name('funcnews.index');
- Modules/DeveloperTools/Routes/web.php:37 Route::get('funcnews/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('funcnews.export');

### Controllers

- Modules/DeveloperTools/Http/Controllers/DeveloperToolsController.php

### Entities / Models

- Modules/DeveloperTools/Entities/DbAccessLog.php
- Modules/DeveloperTools/Entities/DbUserMapping.php
- Modules/DeveloperTools/Entities/DeveloperToolsCredential.php
- Modules/DeveloperTools/Entities/FileDependency.php
- Modules/DeveloperTools/Entities/FileRecord.php

### Services

- Modules/DeveloperTools/Services/DbAccessPolicy.php
- Modules/DeveloperTools/Services/FileScanner.php

### Views Snapshot

- Modules/DeveloperTools/Resources/views/codemap/index.blade.php
- Modules/DeveloperTools/Resources/views/index.blade.php

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
