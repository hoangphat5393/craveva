# Performance Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Performance
- Alias: performance
- Provider: Modules\Performance\Providers\PerformanceServiceProvider, Modules\Performance\Providers\EventServiceProvider
- Source root: Modules/Performance/

## Business Purpose

Quản lý OKR, objective, key result, check-in, meeting, agenda/action và scoring.

## Main Business Flow Draft

- Tạo goal/objective.
- Gắn key result/metric/owner.
- Check-in, meeting, scoring và theo dõi progress.

## Code Evidence

### Routes

- Modules/Performance/Routes/api.php
- Modules/Performance/Routes/web.php

### Route Entry Points Snapshot

- Modules/Performance/Routes/api.php:18 Route::get('performance', fn (Request $request) => $request->user())->name('performance');
- Modules/Performance/Routes/web.php:29 Route::get('objectives/show-description/{id}', [ObjectiveController::class, 'showDescription'])->name('objectives.show-description');
- Modules/Performance/Routes/web.php:30 Route::resource('objectives', ObjectiveController::class)->names('objectives');
- Modules/Performance/Routes/web.php:32 Route::get('key-results/send-reminder/{id}', [KeyResultsController::class, 'sendReminder'])->name('key-results.send-reminder');
- Modules/Performance/Routes/web.php:33 Route::get('key-results/show-description/{id}', [KeyResultsController::class, 'showDescription'])->name('key-results.show-description');
- Modules/Performance/Routes/web.php:34 Route::resource('key-results', KeyResultsController::class)->names('key-results');
- Modules/Performance/Routes/web.php:36 Route::get('okr-scoring/export-report', [OkrScoringController::class, 'exportReport'])->name('okr-scoring.export-report');
- Modules/Performance/Routes/web.php:37 Route::resource('okr-scoring', OkrScoringController::class)->names('okr-scoring');
- Modules/Performance/Routes/web.php:39 Route::resource('check-ins', CheckInController::class)->names('check-ins');
- Modules/Performance/Routes/web.php:42 Route::post('performance-dashboard/objective-progress', [DashboardController::class, 'objectiveChartData'])->name('performance-dashboard.chart');
- Modules/Performance/Routes/web.php:43 Route::resource('performance-dashboard', DashboardController::class)->names('performance-dashboard');
- Modules/Performance/Routes/web.php:46 Route::resource('goal-type-settings', GoalTypeController::class)->names('goal-type-settings');
- Modules/Performance/Routes/web.php:47 Route::resource('key-results-metric', KeyResultsMetricsController::class)->names('key-results-metrics');
- Modules/Performance/Routes/web.php:48 Route::put('performance-settings/meeting-setting/{id}', [PerformanceSettingController::class, 'updateMeeting'])->name('performance-settings.meeting-setting');
- Modules/Performance/Routes/web.php:49 Route::resource('performance-settings', PerformanceSettingController::class)->names('performance-settings');
- Modules/Performance/Routes/web.php:52 Route::get('meetings/view-meeting-list', [MeetingController::class, 'viewMeetingList'])->name('meetings.view_meeting_list');
- Modules/Performance/Routes/web.php:53 Route::get('meetings/send-reminder/{id?}', [MeetingController::class, 'sendReminder'])->name('meetings.send_reminder');
- Modules/Performance/Routes/web.php:54 Route::post('meetings/mark-as-cancelled/{id}', [MeetingController::class, 'markAsCancelled'])->name('meetings.mark_as_cancelled');
- Modules/Performance/Routes/web.php:55 Route::post('meetings/mark-as-complete/{id}', [MeetingController::class, 'markAsComplete'])->name('meetings.mark_as_complete');
- Modules/Performance/Routes/web.php:56 Route::post('meetings/event-monthly-on', [MeetingController::class, 'monthlyOn'])->name('meetings.monthly_on');
- Modules/Performance/Routes/web.php:57 Route::get('meetings/calendar-view', [MeetingController::class, 'calendarView'])->name('meetings.calendar_view');

### Controllers

- Modules/Performance/Http/Controllers/ActionController.php
- Modules/Performance/Http/Controllers/AgendaController.php
- Modules/Performance/Http/Controllers/CheckInController.php
- Modules/Performance/Http/Controllers/DashboardController.php
- Modules/Performance/Http/Controllers/GoalTypeController.php
- Modules/Performance/Http/Controllers/KeyResultsController.php
- Modules/Performance/Http/Controllers/KeyResultsMetricsController.php
- Modules/Performance/Http/Controllers/MeetingController.php
- Modules/Performance/Http/Controllers/ObjectiveController.php
- Modules/Performance/Http/Controllers/OkrScoringController.php
- Modules/Performance/Http/Controllers/PerformanceSettingController.php

### Entities / Models

- Modules/Performance/Entities/Action.php
- Modules/Performance/Entities/Agenda.php
- Modules/Performance/Entities/CheckIn.php
- Modules/Performance/Entities/Dashboard.php
- Modules/Performance/Entities/GoalType.php
- Modules/Performance/Entities/KeyResults.php
- Modules/Performance/Entities/KeyResultsMetrics.php
- Modules/Performance/Entities/Meeting.php
- Modules/Performance/Entities/Objective.php
- Modules/Performance/Entities/ObjectiveOwner.php
- Modules/Performance/Entities/ObjectiveProgressStatus.php
- Modules/Performance/Entities/OkrScoring.php
- Modules/Performance/Entities/PerformanceGlobalSetting.php
- Modules/Performance/Entities/PerformanceSetting.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Performance/Resources/views/check-ins/ajax/create.blade.php
- Modules/Performance/Resources/views/check-ins/ajax/edit.blade.php
- Modules/Performance/Resources/views/components/line-chart.blade.php
- Modules/Performance/Resources/views/dashboard/chart.blade.php
- Modules/Performance/Resources/views/dashboard/checkins.blade.php
- Modules/Performance/Resources/views/dashboard/counts.blade.php
- Modules/Performance/Resources/views/dashboard/index.blade.php
- Modules/Performance/Resources/views/dashboard/meetings.blade.php
- Modules/Performance/Resources/views/key-results/ajax/create.blade.php
- Modules/Performance/Resources/views/key-results/ajax/edit.blade.php
- Modules/Performance/Resources/views/key-results/ajax/show.blade.php
- Modules/Performance/Resources/views/key-results/create.blade.php
- Modules/Performance/Resources/views/meetings/action/add-action.blade.php
- Modules/Performance/Resources/views/meetings/action/create.blade.php
- Modules/Performance/Resources/views/meetings/action/edit.blade.php
- Modules/Performance/Resources/views/meetings/action/show.blade.php
- Modules/Performance/Resources/views/meetings/agenda/add-agenda.blade.php
- Modules/Performance/Resources/views/meetings/agenda/create.blade.php
- Modules/Performance/Resources/views/meetings/agenda/edit.blade.php
- Modules/Performance/Resources/views/meetings/agenda/show.blade.php
- Modules/Performance/Resources/views/meetings/ajax/action.blade.php
- Modules/Performance/Resources/views/meetings/ajax/create.blade.php
- Modules/Performance/Resources/views/meetings/ajax/discussion.blade.php
- Modules/Performance/Resources/views/meetings/ajax/edit.blade.php
- Modules/Performance/Resources/views/meetings/ajax/meeting-detail.blade.php
- Modules/Performance/Resources/views/meetings/ajax/meetings-list.blade.php
- Modules/Performance/Resources/views/meetings/ajax/meetings-load-more.blade.php
- Modules/Performance/Resources/views/meetings/ajax/past-meeting-list.blade.php
- Modules/Performance/Resources/views/meetings/ajax/past-meeting-load-more.blade.php
- Modules/Performance/Resources/views/meetings/ajax/show.blade.php

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
