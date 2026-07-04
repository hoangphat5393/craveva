# Onboarding Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Onboarding
- Alias: Onboarding
- Provider: Modules\Onboarding\Providers\EventServiceProvider, Modules\Onboarding\Providers\OnboardingServiceProvider, Modules\Onboarding\Providers\RouteServiceProvider
- Source root: Modules/Onboarding/

## Business Purpose

Quản lý onboarding task, cấu hình onboarding và task hoàn tất.

## Main Business Flow Draft

- Cấu hình onboarding.
- Tạo onboarding task.
- Nhân viên hoàn tất task và hệ thống ghi nhận completed task.

## Code Evidence

### Routes

- Modules/Onboarding/Routes/api.php
- Modules/Onboarding/Routes/web.php

### Route Entry Points Snapshot

- Modules/Onboarding/Routes/api.php:18 Route::get('onboarding', fn (Request $request) => $request->user())->name('onboarding');
- Modules/Onboarding/Routes/web.php:19 Route::post('onboarding-dashboard/start-onboarding', [OnboardingCompletedTaskController::class, 'startOnboarding'])->name('start.onboarding');
- Modules/Onboarding/Routes/web.php:20 Route::post('onboarding-dashboard/start-offboarding', [OnboardingCompletedTaskController::class, 'startOffboarding'])->name('start.offboarding');
- Modules/Onboarding/Routes/web.php:21 Route::get('onboarding-dashboard/view-file/{file}', [OnboardingCompletedTaskController::class, 'viewFile'])->name('view.file');
- Modules/Onboarding/Routes/web.php:22 Route::post('onboarding-cancel-request', [OnboardingCompletedTaskController::class, 'cancelRequest'])->name('onboarding-cancel-request');
- Modules/Onboarding/Routes/web.php:23 Route::post('onboarding-completeall-request', [OnboardingCompletedTaskController::class, 'completeAllRequest'])->name('onboarding-completeall-request');
- Modules/Onboarding/Routes/web.php:24 Route::post('onboarding-completealloffboarding-request', [OnboardingCompletedTaskController::class, 'completeAllOffboardingRequest'])->name('onboarding-completealloffboarding-request');
- Modules/Onboarding/Routes/web.php:27 Route::post('onboarding-submit-task', [OnboardingCompletedTaskController::class, 'submitTask'])->name('onboarding-submit-task');
- Modules/Onboarding/Routes/web.php:28 Route::post('onboarding-approve-task', [OnboardingCompletedTaskController::class, 'approveTask'])->name('onboarding-approve-task');
- Modules/Onboarding/Routes/web.php:29 Route::post('onboarding-reject-task', [OnboardingCompletedTaskController::class, 'rejectTask'])->name('onboarding-reject-task');
- Modules/Onboarding/Routes/web.php:30 Route::post('onboarding-cancel-task', [OnboardingCompletedTaskController::class, 'cancelTask'])->name('onboarding-cancel-task');
- Modules/Onboarding/Routes/web.php:32 Route::resource('onboarding-dashboard', OnboardingCompletedTaskController::class);
- Modules/Onboarding/Routes/web.php:34 Route::post('onboarding-settings/onboarding-settings/priority', 'OnboardingSettingController@addPriority')->name('onboarding-settings.priority');
- Modules/Onboarding/Routes/web.php:35 Route::post('onboarding-settings-notification/{id}', 'OnboardingSettingController@updateNotification')->name('onboarding-settings-notification');
- Modules/Onboarding/Routes/web.php:36 Route::resource('onboarding-settings', OnboardingSettingController::class);

### Controllers

- Modules/Onboarding/Http/Controllers/OnboardingCompletedTaskController.php
- Modules/Onboarding/Http/Controllers/OnboardingSettingController.php

### Entities / Models

- Modules/Onboarding/Entities/OnboardingCompletedTask.php
- Modules/Onboarding/Entities/OnboardingNotificationSetting.php
- Modules/Onboarding/Entities/OnboardingSetting.php
- Modules/Onboarding/Entities/OnboardingTask.php

### Services

- Modules/Onboarding/Services/OnboardingService.php

### Views Snapshot

- Modules/Onboarding/Resources/views/boarding-users.blade.php
- Modules/Onboarding/Resources/views/components/boarding-users.blade.php
- Modules/Onboarding/Resources/views/components/employee-offboarding.blade.php
- Modules/Onboarding/Resources/views/components/employee-onboarding.blade.php
- Modules/Onboarding/Resources/views/components/start-onboarding.blade.php
- Modules/Onboarding/Resources/views/employee-dashboard-boarding.blade.php
- Modules/Onboarding/Resources/views/employee-profile-boarding.blade.php
- Modules/Onboarding/Resources/views/onboarding-dashboard/ajax/create-modal.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/ajax/offboarding.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/ajax/onboarding.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/ajax/onboard-notification-setting.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/create-offboarding-settings-modal.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/create-onboarding-settings-modal.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/edit-offboarding-settings-modal.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/edit-onboarding-settings-modal.blade.php
- Modules/Onboarding/Resources/views/onboarding-settings/index.blade.php
- Modules/Onboarding/Resources/views/sections/setting-sidebar.blade.php
- Modules/Onboarding/Resources/views/start-onboarding.blade.php

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
