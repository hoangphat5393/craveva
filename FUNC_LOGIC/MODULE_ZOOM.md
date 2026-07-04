# Zoom Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Zoom
- Alias: zoom
- Provider: Modules\Zoom\Providers\ZoomServiceProvider, Modules\Zoom\Providers\EventServiceProvider
- Source root: Modules/Zoom/

## Business Purpose

Quản lý meeting Zoom, category, note, webhook và notification setting.

## Main Business Flow Draft

- Cấu hình Zoom.
- Tạo meeting/category/note.
- Webhook/notification cập nhật trạng thái meeting.

## Code Evidence

### Routes

- Modules/Zoom/Routes/api.php
- Modules/Zoom/Routes/web.php

### Route Entry Points Snapshot

- Modules/Zoom/Routes/web.php:23 Route::get('zoom-calendar', [ZoomMeetingController::class, 'calendar'])->name('zoom-meetings.calendar');
- Modules/Zoom/Routes/web.php:24 Route::get('zoom-meetings/start-meeting/{id}', [ZoomMeetingController::class, 'startMeeting'])->name('zoom-meetings.start_meeting');
- Modules/Zoom/Routes/web.php:25 Route::post('zoom-meeting/update-occurrence/{id}', [ZoomMeetingController::class, 'updateOccurrence'])->name('zoom-meetings.update_occurrence');
- Modules/Zoom/Routes/web.php:26 Route::post('zoom-meeting/cancel-meeting', [ZoomMeetingController::class, 'cancelMeeting'])->name('zoom-meetings.cancel_meeting');
- Modules/Zoom/Routes/web.php:27 Route::post('zoom-meeting/end-meeting', [ZoomMeetingController::class, 'endMeeting'])->name('zoom-meetings.end_meeting');
- Modules/Zoom/Routes/web.php:28 Route::post('zoom-meetings/apply-quick-action', [ZoomMeetingController::class, 'applyQuickAction'])->name('zoom-meetings.apply_quick_action');
- Modules/Zoom/Routes/web.php:29 Route::resource('zoom-meetings', ZoomMeetingController::class);
- Modules/Zoom/Routes/web.php:31 Route::resource('zoom-categories', ZoomCategoryController::class);
- Modules/Zoom/Routes/web.php:32 Route::post('zoom-settings/zoom-smtp-settings/{id?}', [ZoomSettingController::class, 'updateEmailSetting'])->name('zoom-settings.zoom-smtp-settings');
- Modules/Zoom/Routes/web.php:33 Route::post('zoom-settings/zoom-slack-settings/{id?}', [ZoomSettingController::class, 'updateSlackSetting'])->name('zoom-settings.zoom-slack-settings');
- Modules/Zoom/Routes/web.php:35 Route::resource('zoom-settings', ZoomSettingController::class);
- Modules/Zoom/Routes/web.php:36 Route::resource('meeting-note', ZoomMeetingNoteController::class);
- Modules/Zoom/Routes/web.php:40 Route::post('zoom-webhook/{hash}', [ZoomWebhookController::class, 'index'])->name('zoom-webhook');
- Modules/Zoom/Routes/web.php:41 Route::get('zoom-webhook/{hash}', [ZoomWebhookController::class, 'getWebhook'])->name('get-zoom-webhook');

### Controllers

- Modules/Zoom/Http/Controllers/ZoomCategoryController.php
- Modules/Zoom/Http/Controllers/ZoomMeetingController.php
- Modules/Zoom/Http/Controllers/ZoomMeetingNoteController.php
- Modules/Zoom/Http/Controllers/ZoomSettingController.php
- Modules/Zoom/Http/Controllers/ZoomWebhookController.php

### Entities / Models

- Modules/Zoom/Entities/ZoomCategory.php
- Modules/Zoom/Entities/ZoomGlobalSetting.php
- Modules/Zoom/Entities/ZoomMeeting.php
- Modules/Zoom/Entities/ZoomMeetingNote.php
- Modules/Zoom/Entities/ZoomNotificationSetting.php
- Modules/Zoom/Entities/ZoomSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Zoom/Resources/views/category/create.blade.php
- Modules/Zoom/Resources/views/index.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/create.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/edit.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/edit_occurrence.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/notes.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/show.blade.php
- Modules/Zoom/Resources/views/meeting/calendar.blade.php
- Modules/Zoom/Resources/views/meeting/create.blade.php
- Modules/Zoom/Resources/views/meeting/index.blade.php
- Modules/Zoom/Resources/views/meeting/notes/edit.blade.php
- Modules/Zoom/Resources/views/meeting/notes/show.blade.php
- Modules/Zoom/Resources/views/meeting/start_meeting.blade.php
- Modules/Zoom/Resources/views/meeting-calendar/start_meeting.blade.php
- Modules/Zoom/Resources/views/notifications/client/meeting_invite.blade.php
- Modules/Zoom/Resources/views/notifications/meeting_invite.blade.php
- Modules/Zoom/Resources/views/notification-settings/ajax/email-setting.blade.php
- Modules/Zoom/Resources/views/notification-settings/ajax/slack-setting.blade.php
- Modules/Zoom/Resources/views/notification-settings/ajax/zoom-setting.blade.php
- Modules/Zoom/Resources/views/sections/setting-sidebar.blade.php
- Modules/Zoom/Resources/views/sections/sidebar.blade.php

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
