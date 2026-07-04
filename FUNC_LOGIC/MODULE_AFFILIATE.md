# Affiliate Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Affiliate
- Alias: affiliate
- Provider: Modules\Affiliate\Providers\AffiliateServiceProvider, Modules\Affiliate\Providers\EventServiceProvider
- Source root: Modules/Affiliate/

## Business Purpose

Quản lý affiliate, referral và payout hoa hồng.

## Main Business Flow Draft

- Admin cấu hình affiliate setting.
- Affiliate/referral được ghi nhận.
- Payout được tạo và theo dõi trạng thái thanh toán.

## Code Evidence

### Routes

- Modules/Affiliate/Routes/web.php

### Route Entry Points Snapshot

- Modules/Affiliate/Routes/web.php:13 Route::resource('affiliates-dashboard', DashBoardController::class)->names('dashboard')->only(['index']);
- Modules/Affiliate/Routes/web.php:14 Route::post('affiliates/change-status', [AffiliateController::class, 'changeStatus'])->name('affiliates.change_status');
- Modules/Affiliate/Routes/web.php:15 Route::resource('affiliates', AffiliateController::class)->names('affiliate')->except(['edit', 'update']);
- Modules/Affiliate/Routes/web.php:16 Route::get('affiliates/get-affiliates/{id}', [ReferralsController::class, 'getAffiliates'])->name('affiliates.get_affiliates');
- Modules/Affiliate/Routes/web.php:17 Route::resource('referrals', ReferralsController::class)->names('referral');
- Modules/Affiliate/Routes/web.php:19 Route::resource('payouts', PayoutController::class)->names('payout');
- Modules/Affiliate/Routes/web.php:20 Route::post('payouts/change-status', [PayoutController::class, 'changeStatus'])->name('payouts.change_status');
- Modules/Affiliate/Routes/web.php:21 Route::get('payouts-confirm-paid/{payout}', [PayoutController::class, 'paidConfirmation'])->name('payouts.confirm_paid');
- Modules/Affiliate/Routes/web.php:23 Route::resource('affiliate-dashboard', AffiliateDashboardController::class)->names('affiliate-dashboard')->only(['index', 'edit', 'update']);
- Modules/Affiliate/Routes/web.php:26 Route::get('affiliates/{referral}', [AffiliatePublicController::class, 'redirectReferral'])->name('affiliate.redirectReferral');
- Modules/Affiliate/Routes/web.php:29 Route::resource('affiliate-settings', AffiliateSettingController::class);

### Controllers

- Modules/Affiliate/Http/Controllers/AffiliateController.php
- Modules/Affiliate/Http/Controllers/AffiliateDashboardController.php
- Modules/Affiliate/Http/Controllers/AffiliatePublicController.php
- Modules/Affiliate/Http/Controllers/AffiliateSettingController.php
- Modules/Affiliate/Http/Controllers/DashBoardController.php
- Modules/Affiliate/Http/Controllers/PayoutController.php
- Modules/Affiliate/Http/Controllers/ReferralsController.php

### Entities / Models

- Modules/Affiliate/Entities/Affiliate.php
- Modules/Affiliate/Entities/AffiliateGlobalSetting.php
- Modules/Affiliate/Entities/AffiliateSetting.php
- Modules/Affiliate/Entities/Payout.php
- Modules/Affiliate/Entities/Referral.php
- Modules/Affiliate/Entities/User.php

### Services

- Chưa thấy service riêng trong module.

### Views Snapshot

- Modules/Affiliate/Resources/views/affiliate/ajax/affiliates.blade.php
- Modules/Affiliate/Resources/views/affiliate/ajax/create.blade.php
- Modules/Affiliate/Resources/views/affiliate/ajax/edit-slug.blade.php
- Modules/Affiliate/Resources/views/affiliate/ajax/payouts.blade.php
- Modules/Affiliate/Resources/views/affiliate/ajax/referrals.blade.php
- Modules/Affiliate/Resources/views/affiliate/create.blade.php
- Modules/Affiliate/Resources/views/affiliate/index.blade.php
- Modules/Affiliate/Resources/views/affiliate/show.blade.php
- Modules/Affiliate/Resources/views/affiliate-dashboard/ajax/affiliates.blade.php
- Modules/Affiliate/Resources/views/affiliate-dashboard/ajax/payouts.blade.php
- Modules/Affiliate/Resources/views/affiliate-dashboard/ajax/referrals.blade.php
- Modules/Affiliate/Resources/views/affiliate-dashboard/index.blade.php
- Modules/Affiliate/Resources/views/affiliate-settings/index.blade.php
- Modules/Affiliate/Resources/views/components/affiliate-option.blade.php
- Modules/Affiliate/Resources/views/dashboard/companies.blade.php
- Modules/Affiliate/Resources/views/dashboard/index.blade.php
- Modules/Affiliate/Resources/views/dashboard/referrals.blade.php
- Modules/Affiliate/Resources/views/payout/ajax/confirm-paid.blade.php
- Modules/Affiliate/Resources/views/payout/ajax/create.blade.php
- Modules/Affiliate/Resources/views/payout/ajax/edit.blade.php
- Modules/Affiliate/Resources/views/payout/ajax/show.blade.php
- Modules/Affiliate/Resources/views/payout/index.blade.php
- Modules/Affiliate/Resources/views/referrals/ajax/create.blade.php
- Modules/Affiliate/Resources/views/referrals/create.blade.php
- Modules/Affiliate/Resources/views/referrals/index.blade.php
- Modules/Affiliate/Resources/views/sections/sidebar.blade.php
- Modules/Affiliate/Resources/views/sections/superadmin/setting-sidebar.blade.php
- Modules/Affiliate/Resources/views/sections/superadmin/sidebar.blade.php

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
