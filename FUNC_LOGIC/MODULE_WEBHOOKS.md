# Webhooks Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Webhooks
- Alias: webhooks
- Provider: Modules\Webhooks\Providers\WebhooksServiceProvider, Modules\Webhooks\Providers\EventServiceProvider
- Source root: Modules/Webhooks/

## Business Purpose

Quản lý webhook setting, request và log tích hợp ngoài.

## Main Business Flow Draft

- Cấu hình webhook endpoint.
- Nhận request webhook.
- Ghi log và theo dõi trạng thái xử lý.

## Code Evidence

### Routes

- Modules/Webhooks/Routes/api.php
- Modules/Webhooks/Routes/web.php

### Route Entry Points Snapshot

- Modules/Webhooks/Routes/web.php:20 Route::post('webhooks/apply-quick-action', [WebhooksController::class, 'applyQuickAction'])->name('webhooks.apply_quick_action');
- Modules/Webhooks/Routes/web.php:21 Route::post('webhooks/{webhook}/duplicate', [WebhooksController::class, 'duplicate'])->name('webhooks.duplicate');
- Modules/Webhooks/Routes/web.php:22 Route::resource('webhooks', WebhooksController::class);
- Modules/Webhooks/Routes/web.php:23 Route::post('webhooks-log/apply-quick-action', [WebhooksLogController::class, 'applyQuickAction'])->name('webhooks-log.apply_quick_action');
- Modules/Webhooks/Routes/web.php:24 Route::resource('webhooks-log', WebhooksLogController::class);
- Modules/Webhooks/Routes/web.php:25 Route::get('webhooks-for-variable/{webhookFor}', [WebhooksController::class, 'webhooksForVariable'])->name('webhooks.webhooks_for_variable');

### Controllers

- Modules/Webhooks/Http/Controllers/WebhooksController.php
- Modules/Webhooks/Http/Controllers/WebhooksLogController.php

### Entities / Models

- Modules/Webhooks/Entities/WebhooksGlobalSetting.php
- Modules/Webhooks/Entities/WebhooksLog.php
- Modules/Webhooks/Entities/WebhooksRequest.php
- Modules/Webhooks/Entities/WebhooksSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Webhooks/Resources/views/sections/sidebar.blade.php
- Modules/Webhooks/Resources/views/webhooks/ajax/create.blade.php
- Modules/Webhooks/Resources/views/webhooks/ajax/edit.blade.php
- Modules/Webhooks/Resources/views/webhooks/create.blade.php
- Modules/Webhooks/Resources/views/webhooks/index.blade.php
- Modules/Webhooks/Resources/views/webhooks-log/ajax/show.blade.php
- Modules/Webhooks/Resources/views/webhooks-log/index.blade.php
- Modules/Webhooks/Resources/views/webhooks-log/show.blade.php

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
