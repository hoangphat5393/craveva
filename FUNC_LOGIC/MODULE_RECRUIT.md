# Recruit Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Recruit
- Alias: recruit
- Provider: Modules\Recruit\Providers\RecruitServiceProvider, Modules\Recruit\Providers\EventServiceProvider
- Source root: Modules/Recruit/

## Business Purpose

Quản lý tuyển dụng: job, application, candidate database, interview, offer letter và cấu hình.

## Main Business Flow Draft

- Tạo job và cấu hình pipeline/status/source.
- Nhận candidate/application.
- Quản lý interview/evaluation, offer letter và báo cáo tuyển dụng.

## Code Evidence

### Routes

- Modules/Recruit/Routes/api.php
- Modules/Recruit/Routes/web.php

### Route Entry Points Snapshot

- Modules/Recruit/Routes/web.php:44 Route::post('save-application', [FrontJobController::class, 'saveApplication'])->name('save_application');
- Modules/Recruit/Routes/web.php:45 Route::get('careers/{slug?}', [FrontJobController::class, 'index'])->name('recruit');
- Modules/Recruit/Routes/web.php:46 Route::get('job-opening/{slug?}', [FrontJobController::class, 'jobOpenings'])->name('job_opening');
- Modules/Recruit/Routes/web.php:47 Route::get('job-opening/fetch-job/{company?}', [FrontJobController::class, 'fetchJob'])->name('job-opening.fetch_job');
- Modules/Recruit/Routes/web.php:48 Route::get('job-apply/{slug}/{location?}/{company?}', [FrontJobController::class, 'jobApply'])->name('job_apply');
- Modules/Recruit/Routes/web.php:49 Route::get('job-detail/{jobId}/{locationId}/{company}', [FrontJobController::class, 'jobDetail'])->name('job-detail');
- Modules/Recruit/Routes/web.php:50 Route::get('job-detail-page/{slug}/{location?}/{company?}', [FrontJobController::class, 'jobDetailPage'])->name('job_detail_page');
- Modules/Recruit/Routes/web.php:51 Route::get('/jobOffer/{hash}/{company?}', [FrontJobController::class, 'jobOfferLetter'])->name('front.jobOffer');
- Modules/Recruit/Routes/web.php:52 Route::get('job-offer-download/{id}/{slug}', [FrontJobController::class, 'download'])->name('jobOffer.download');
- Modules/Recruit/Routes/web.php:53 Route::post('/jobOffer-accept/{id}', [FrontJobController::class, 'jobOfferLetterStatusChange'])->name('front.job-offer.accept');
- Modules/Recruit/Routes/web.php:54 Route::get('/thankyou/{slug?}', [FrontJobController::class, 'thankyouPage'])->name('front.thankyou-page');
- Modules/Recruit/Routes/web.php:55 Route::get('pages/{job?}/{slug?}', [FrontJobController::class, 'customPage'])->name('front.custom-page');
- Modules/Recruit/Routes/web.php:56 Route::get('job-details-modal', [FrontJobController::class, 'jobDetailsModal'])->name('front.job_details_modal');
- Modules/Recruit/Routes/web.php:57 Route::get('job-alert/{slug?}', [FrontJobController::class, 'jobAlert'])->name('front.job_alert');
- Modules/Recruit/Routes/web.php:58 Route::post('job-alert-save', [FrontJobController::class, 'jobAlertStore'])->name('front.job_alert_store');
- Modules/Recruit/Routes/web.php:59 Route::get('job-alert-unsubscribe/{slug?}/{alertHash?}', [FrontJobController::class, 'jobAlertUnsubscribe'])->name('front.job_alert_unsubscribe');
- Modules/Recruit/Routes/web.php:60 Route::get('accept-offer/{id}', [FrontJobController::class, 'acceptOffer'])->name('front.accept_offer');
- Modules/Recruit/Routes/web.php:63 Route::post('jobs/apply-quick-action', [JobController::class, 'applyQuickAction'])->name('jobs.apply_quick_action');
- Modules/Recruit/Routes/web.php:64 Route::get('getJobSubCategories/{id}', [JobSubCategoryController::class, 'getSubCategories'])->name('get_job_sub_categories');
- Modules/Recruit/Routes/web.php:65 Route::get('jobs/fetch-job', [JobController::class, 'fetchJob'])->name('jobs.fetch_job');

### Controllers

- Modules/Recruit/Http/Controllers/ApplicantNoteController.php
- Modules/Recruit/Http/Controllers/CandidateDatabaseController.php
- Modules/Recruit/Http/Controllers/EvaluationController.php
- Modules/Recruit/Http/Controllers/FooterSettingsController.php
- Modules/Recruit/Http/Controllers/Front/FrontBaseController.php
- Modules/Recruit/Http/Controllers/Front/FrontJobController.php
- Modules/Recruit/Http/Controllers/InterviewFileController.php
- Modules/Recruit/Http/Controllers/InterviewRecommendationStatusController.php
- Modules/Recruit/Http/Controllers/InterviewScheduleController.php
- Modules/Recruit/Http/Controllers/InterviewStageController.php
- Modules/Recruit/Http/Controllers/JobApplicationBoardController.php
- Modules/Recruit/Http/Controllers/JobApplicationController.php
- Modules/Recruit/Http/Controllers/JobApplicationFilesController.php
- Modules/Recruit/Http/Controllers/JobCategoryController.php
- Modules/Recruit/Http/Controllers/JobController.php
- Modules/Recruit/Http/Controllers/JobFileController.php
- Modules/Recruit/Http/Controllers/JobOfferLetterController.php
- Modules/Recruit/Http/Controllers/JobOfferLetterFilesController.php
- Modules/Recruit/Http/Controllers/JobSubCategoryController.php
- Modules/Recruit/Http/Controllers/JobTypeController.php
- Modules/Recruit/Http/Controllers/RecruitCandidateFollowUpController.php
- Modules/Recruit/Http/Controllers/RecruitCustomQuestionController.php
- Modules/Recruit/Http/Controllers/RecruitDashboardController.php
- Modules/Recruit/Http/Controllers/RecruitEmailNotificationSettingsController.php
- Modules/Recruit/Http/Controllers/RecruiterController.php
- Modules/Recruit/Http/Controllers/RecruitSettingController.php
- Modules/Recruit/Http/Controllers/RecruitSourceController.php
- Modules/Recruit/Http/Controllers/ReportController.php
- Modules/Recruit/Http/Controllers/SkillController.php
- Modules/Recruit/Http/Controllers/WorkExperienceController.php

### Entities / Models

- Modules/Recruit/Entities/ApplicationSource.php
- Modules/Recruit/Entities/JobInterviewStage.php
- Modules/Recruit/Entities/OfferLetterHistory.php
- Modules/Recruit/Entities/RecruitApplicantNote.php
- Modules/Recruit/Entities/RecruitApplicationFile.php
- Modules/Recruit/Entities/RecruitApplicationSkill.php
- Modules/Recruit/Entities/RecruitApplicationStatus.php
- Modules/Recruit/Entities/RecruitApplicationStatusCategory.php
- Modules/Recruit/Entities/RecruitCandidateDatabase.php
- Modules/Recruit/Entities/RecruitCandidateFollowUp.php
- Modules/Recruit/Entities/RecruitCustomQuestion.php
- Modules/Recruit/Entities/RecruitEmailNotificationSetting.php
- Modules/Recruit/Entities/Recruiter.php
- Modules/Recruit/Entities/RecruitFooterLink.php
- Modules/Recruit/Entities/RecruitGlobalSetting.php
- Modules/Recruit/Entities/RecruitInterviewComments.php
- Modules/Recruit/Entities/RecruitInterviewEmployees.php
- Modules/Recruit/Entities/RecruitInterviewEvaluation.php
- Modules/Recruit/Entities/RecruitInterviewFile.php
- Modules/Recruit/Entities/RecruitInterviewHistory.php
- Modules/Recruit/Entities/RecruitInterviewSchedule.php
- Modules/Recruit/Entities/RecruitInterviewStage.php
- Modules/Recruit/Entities/RecruitJob.php
- Modules/Recruit/Entities/RecruitJobAddress.php
- Modules/Recruit/Entities/RecruitJobAlert.php
- Modules/Recruit/Entities/RecruitJobApplication.php
- Modules/Recruit/Entities/RecruitJobboardSetting.php
- Modules/Recruit/Entities/RecruitJobCategory.php
- Modules/Recruit/Entities/RecruitJobCustomAnswer.php
- Modules/Recruit/Entities/RecruitJobFile.php
- Modules/Recruit/Entities/RecruitJobHistory.php
- Modules/Recruit/Entities/RecruitJobOfferLetter.php
- Modules/Recruit/Entities/RecruitJobOfferLetterFiles.php
- Modules/Recruit/Entities/RecruitJobOfferQuestion.php
- Modules/Recruit/Entities/RecruitJobQuestion.php
- Modules/Recruit/Entities/RecruitJobSkill.php
- Modules/Recruit/Entities/RecruitJobSubCategory.php
- Modules/Recruit/Entities/RecruitJobType.php
- Modules/Recruit/Entities/RecruitRecommendationStatus.php
- Modules/Recruit/Entities/RecruitSalaryStructure.php
- Modules/Recruit/Entities/RecruitSelectedSalaryComponent.php
- Modules/Recruit/Entities/RecruitSetting.php
- Modules/Recruit/Entities/RecruitSkill.php
- Modules/Recruit/Entities/RecruitWorkExperience.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Recruit/Resources/views/candidate-database/ajax/show.blade.php
- Modules/Recruit/Resources/views/candidate-database/index.blade.php
- Modules/Recruit/Resources/views/candidate-database/show.blade.php
- Modules/Recruit/Resources/views/components/cards/custom-question-field.blade.php
- Modules/Recruit/Resources/views/components/cards/job-card.blade.php
- Modules/Recruit/Resources/views/dashboard/index.blade.php
- Modules/Recruit/Resources/views/front/custom-page.blade.php
- Modules/Recruit/Resources/views/front/fetch-first-job.blade.php
- Modules/Recruit/Resources/views/front/fetch-job.blade.php
- Modules/Recruit/Resources/views/front/index.blade.php
- Modules/Recruit/Resources/views/front/job-alert.blade.php
- Modules/Recruit/Resources/views/front/job-apply.blade.php
- Modules/Recruit/Resources/views/front/job-detail.blade.php
- Modules/Recruit/Resources/views/front/job-detail-page.blade.php
- Modules/Recruit/Resources/views/front/job-details-modal.blade.php
- Modules/Recruit/Resources/views/front/jobOffer.blade.php
- Modules/Recruit/Resources/views/front/job-openings.blade.php
- Modules/Recruit/Resources/views/front/thankyou-page.blade.php
- Modules/Recruit/Resources/views/import/import_exception.blade.php
- Modules/Recruit/Resources/views/import/process-form.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/activity-detail.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/create.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/details.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/edit.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/evaluation.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/file.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/history.blade.php
- Modules/Recruit/Resources/views/interview-schedule/ajax/show.blade.php
- Modules/Recruit/Resources/views/interview-schedule/evaluation/create.blade.php
- Modules/Recruit/Resources/views/interview-schedule/evaluation/edit.blade.php

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
