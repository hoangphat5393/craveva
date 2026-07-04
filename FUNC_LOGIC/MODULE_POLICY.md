# Policy Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Policy
- Alias: policy
- Provider: Modules\Policy\Providers\PolicyServiceProvider, Modules\Policy\Providers\EventServiceProvider
- Source root: Modules/Policy/

## Business Purpose

Quản lý chính sách nội bộ, file đính kèm và xác nhận đã đọc của nhân viên.

## Main Business Flow Draft

- Tạo policy và file liên quan.
- Công bố policy cho nhân viên.
- Ghi nhận employee acknowledgement.

## Code Evidence

### Routes

- Modules/Policy/Routes/api.php
- Modules/Policy/Routes/web.php

### Route Entry Points Snapshot

- Modules/Policy/Routes/api.php:18 Route::get('policycentre', fn (Request $request) => $request->user())->name('policycentre');
- Modules/Policy/Routes/web.php:20 Route::get('policy-file/download/{id}', [PolicyFileController::class, 'download'])->name('policy-file.download');
- Modules/Policy/Routes/web.php:21 Route::resource('policy-file', PolicyFileController::class);
- Modules/Policy/Routes/web.php:23 Route::post('policy/send-reminder/{id}', [PolicyController::class, 'sendRemainder'])->name('policy.send_remainder');
- Modules/Policy/Routes/web.php:24 Route::post('policy/publish-pilocy/{id}', [PolicyController::class, 'publishPolicy'])->name('policy.publish');
- Modules/Policy/Routes/web.php:25 Route::post('policy/archive-delete/{id}', [PolicyController::class, 'archiveDestroy'])->name('policy.archive_delete');
- Modules/Policy/Routes/web.php:26 Route::post('policy/archive-restore/{id}', [PolicyController::class, 'archiveRestore'])->name('policy.archive_restore');
- Modules/Policy/Routes/web.php:27 Route::get('policy/archive', [PolicyController::class, 'archive'])->name('policy.archive');
- Modules/Policy/Routes/web.php:28 Route::get('policy-signature/{id}', [PolicyController::class, 'policySign'])->name('policy.sign');
- Modules/Policy/Routes/web.php:29 Route::get('policy/download/{id}/{userId}', [PolicyController::class, 'download'])->name('policy.download');
- Modules/Policy/Routes/web.php:30 Route::post('policy-signature/{id}', [PolicyController::class, 'policySignStore'])->name('policy.signStore');
- Modules/Policy/Routes/web.php:31 Route::post('policy-acknowledge/{id}', [PolicyController::class, 'policyAcknowledge'])->name('policy.acknowledge');
- Modules/Policy/Routes/web.php:32 Route::get('policy/download-file/{id}/{userId}', [PolicyController::class, 'downloadFile'])->name('policy.downloadFile');
- Modules/Policy/Routes/web.php:33 Route::resource('policy', PolicyController::class);

### Controllers

- Modules/Policy/Http/Controllers/PolicyController.php
- Modules/Policy/Http/Controllers/PolicyFileController.php

### Entities / Models

- Modules/Policy/Entities/Policy.php
- Modules/Policy/Entities/PolicyEmployeeAcknowledged.php
- Modules/Policy/Entities/PolicyFile.php
- Modules/Policy/Entities/PolicySetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Policy/Resources/views/notifications/policy_acknowledged_notification.blade.php
- Modules/Policy/Resources/views/notifications/policy_published_notification.blade.php
- Modules/Policy/Resources/views/notifications/send_reminder_notification.blade.php
- Modules/Policy/Resources/views/policy/ajax/acknowledged.blade.php
- Modules/Policy/Resources/views/policy/ajax/create.blade.php
- Modules/Policy/Resources/views/policy/ajax/edit.blade.php
- Modules/Policy/Resources/views/policy/ajax/non_acknowledged.blade.php
- Modules/Policy/Resources/views/policy/ajax/policy.blade.php
- Modules/Policy/Resources/views/policy/ajax/sign.blade.php
- Modules/Policy/Resources/views/policy/archive.blade.php
- Modules/Policy/Resources/views/policy/create.blade.php
- Modules/Policy/Resources/views/policy/files/show.blade.php
- Modules/Policy/Resources/views/policy/index.blade.php
- Modules/Policy/Resources/views/policy/pdf/policy.blade.php
- Modules/Policy/Resources/views/policy/pdf/policy-sign.blade.php
- Modules/Policy/Resources/views/policy/show.blade.php
- Modules/Policy/Resources/views/sections/sidebar.blade.php

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
