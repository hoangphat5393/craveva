# Pricing Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Pricing
- Alias: pricing
- Provider: Modules\Pricing\Providers\PricingServiceProvider, Modules\Pricing\Providers\RouteServiceProvider
- Source root: Modules/Pricing/

## Business Purpose

Quản lý bảng giá khách hàng/sản phẩm, tier pricing và volume discount.

## Main Business Flow Draft

- Cấu hình customer/product pricing hoặc pricing tier.
- Áp dụng volume discount nếu đủ điều kiện.
- Luồng báo giá/order tham chiếu giá phù hợp theo khách hàng/sản phẩm.

## Code Evidence

### Routes

- Modules/Pricing/Routes/api.php
- Modules/Pricing/Routes/web.php

### Route Entry Points Snapshot

- Modules/Pricing/Routes/api.php:7 Route::get('pricing/preview', [PricingController::class, 'preview']);
- Modules/Pricing/Routes/web.php:13 Route::post('tiers/apply-quick-action', [PricingTierController::class, 'applyQuickAction'])->name('pricing.tiers.apply_quick_action');
- Modules/Pricing/Routes/web.php:14 Route::post('tiers/change-status', [PricingTierController::class, 'changeStatus'])->name('pricing.tiers.change_status');
- Modules/Pricing/Routes/web.php:15 Route::get('tiers', [PricingTierController::class, 'index'])->name('pricing.tiers.index');
- Modules/Pricing/Routes/web.php:16 Route::get('tiers/create', [PricingTierController::class, 'create'])->name('pricing.tiers.create');
- Modules/Pricing/Routes/web.php:17 Route::post('tiers', [PricingTierController::class, 'store'])->name('pricing.tiers.store');
- Modules/Pricing/Routes/web.php:18 Route::get('tiers/{id}/edit', [PricingTierController::class, 'edit'])->name('pricing.tiers.edit');
- Modules/Pricing/Routes/web.php:19 Route::put('tiers/{id}', [PricingTierController::class, 'update'])->name('pricing.tiers.update');
- Modules/Pricing/Routes/web.php:20 Route::delete('tiers/{id}', [PricingTierController::class, 'destroy'])->name('pricing.tiers.destroy');
- Modules/Pricing/Routes/web.php:21 Route::get('tiers/{id}', [PricingTierController::class, 'show'])->name('pricing.tiers.show');
- Modules/Pricing/Routes/web.php:24 Route::post('tiers/{id}/items', [PricingTierController::class, 'storeItem'])->name('pricing.tiers.items.store');
- Modules/Pricing/Routes/web.php:25 Route::delete('tiers/items/{itemId}', [PricingTierController::class, 'destroyItem'])->name('pricing.tiers.items.destroy');
- Modules/Pricing/Routes/web.php:26 Route::post('tiers/items/apply-quick-action', [PricingTierController::class, 'applyItemsQuickAction'])->name('pricing.tiers.items.apply_quick_action');
- Modules/Pricing/Routes/web.php:28 Route::get('client-tiers', [ClientTierController::class, 'index'])->name('pricing.client_tiers.index');
- Modules/Pricing/Routes/web.php:29 Route::get('client-tiers/{id}/edit', [ClientTierController::class, 'edit'])->name('pricing.client_tiers.edit');
- Modules/Pricing/Routes/web.php:30 Route::put('client-tiers/{id}', [ClientTierController::class, 'update'])->name('pricing.client_tiers.update');
- Modules/Pricing/Routes/web.php:32 Route::get('client-pricing', [ClientPricingController::class, 'index'])->name('pricing.client_pricing.index');
- Modules/Pricing/Routes/web.php:33 Route::post('client-pricing/apply-quick-action', [ClientPricingController::class, 'applyQuickAction'])->name('pricing.client_pricing.apply_quick_action');
- Modules/Pricing/Routes/web.php:34 Route::post('client-pricing/change-status', [ClientPricingController::class, 'changeStatus'])->name('pricing.client_pricing.change_status');
- Modules/Pricing/Routes/web.php:35 Route::get('client-pricing/create', [ClientPricingController::class, 'create'])->name('pricing.client_pricing.create');
- Modules/Pricing/Routes/web.php:36 Route::post('client-pricing', [ClientPricingController::class, 'store'])->name('pricing.client_pricing.store');

### Controllers

- Modules/Pricing/Http/Controllers/ClientPricingController.php
- Modules/Pricing/Http/Controllers/ClientTierController.php
- Modules/Pricing/Http/Controllers/CompanyPricingController.php
- Modules/Pricing/Http/Controllers/Concerns/ValidatesBulkRowIds.php
- Modules/Pricing/Http/Controllers/PricingController.php
- Modules/Pricing/Http/Controllers/PricingImportController.php
- Modules/Pricing/Http/Controllers/PricingTierController.php
- Modules/Pricing/Http/Controllers/VolumeDiscountController.php
- Modules/Pricing/Http/Controllers/VolumeRuleController.php

### Entities / Models

- Modules/Pricing/Entities/ClientProductPricing.php
- Modules/Pricing/Entities/CompanyCustomerPricing.php
- Modules/Pricing/Entities/CompanyCustomerProductPricing.php
- Modules/Pricing/Entities/DealProposalPricing.php
- Modules/Pricing/Entities/PricingTier.php
- Modules/Pricing/Entities/PricingTierItem.php
- Modules/Pricing/Entities/VolumeDiscountRule.php

### Services

- Modules/Pricing/Services/PricingService.php
- Modules/Pricing/Services/VolumeDiscountService.php

### Views Snapshot

- Modules/Pricing/Resources/views/client_pricing/ajax/create.blade.php
- Modules/Pricing/Resources/views/client_pricing/ajax/edit.blade.php
- Modules/Pricing/Resources/views/client_pricing/create.blade.php
- Modules/Pricing/Resources/views/client_pricing/edit.blade.php
- Modules/Pricing/Resources/views/client_pricing/index.blade.php
- Modules/Pricing/Resources/views/client_tiers/ajax/edit.blade.php
- Modules/Pricing/Resources/views/client_tiers/edit.blade.php
- Modules/Pricing/Resources/views/client_tiers/index.blade.php
- Modules/Pricing/Resources/views/company_pricing/ajax/create.blade.php
- Modules/Pricing/Resources/views/company_pricing/ajax/edit.blade.php
- Modules/Pricing/Resources/views/company_pricing/create.blade.php
- Modules/Pricing/Resources/views/company_pricing/edit.blade.php
- Modules/Pricing/Resources/views/company_pricing/index.blade.php
- Modules/Pricing/Resources/views/import/import_progress.blade.php
- Modules/Pricing/Resources/views/import/index.blade.php
- Modules/Pricing/Resources/views/sections/sidebar.blade.php
- Modules/Pricing/Resources/views/sections/superadmin/sidebar.blade.php
- Modules/Pricing/Resources/views/tiers/ajax/create.blade.php
- Modules/Pricing/Resources/views/tiers/ajax/edit.blade.php
- Modules/Pricing/Resources/views/tiers/ajax/show.blade.php
- Modules/Pricing/Resources/views/tiers/create.blade.php
- Modules/Pricing/Resources/views/tiers/edit.blade.php
- Modules/Pricing/Resources/views/tiers/index.blade.php
- Modules/Pricing/Resources/views/tiers/show.blade.php
- Modules/Pricing/Resources/views/volume_rules/ajax/create.blade.php
- Modules/Pricing/Resources/views/volume_rules/ajax/edit.blade.php
- Modules/Pricing/Resources/views/volume_rules/create.blade.php
- Modules/Pricing/Resources/views/volume_rules/edit.blade.php
- Modules/Pricing/Resources/views/volume_rules/index.blade.php

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

- FUNC_LOGIC/PRICING_BUSINESS.md
- FUNC_IMPROVE/07_PRICING_MODULE_DEV_TASKS.md

## Reading Map

| Need | Read |
| --- | --- |
| Pricing priority, tier pricing, request flow, and business analysis | PRICING_BUSINESS.md |
| Pricing module implementation/dev tasks | FUNC_IMPROVE/07_PRICING_MODULE_DEV_TASKS.md |
| Client/product import context that feeds pricing | CLIENT_BUSINESS.md, PRODUCT_BUSINESS.md, MAOLIN_IMPORT_MAPPING.md |

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
