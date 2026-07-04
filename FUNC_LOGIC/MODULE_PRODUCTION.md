# Production Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Production
- Alias: production
- Provider: Modules\Production\Providers\ProductionServiceProvider
- Source root: Modules/Production/

## Business Purpose

Quản lý BOM, production order, batch, consumption/output, rework, variance và nhập kho thành phẩm.

## Main Business Flow Draft

- Tạo BOM cho thành phẩm.
- Tạo/release production order và snapshot BOM.
- Tạo batch, ghi nhận tiêu hao/output, xử lý variance/rework, post FG receipt vào kho.

## Code Evidence

### Routes

- Modules/Production/Routes/api.php
- Modules/Production/Routes/web.php

### Route Entry Points Snapshot

- Modules/Production/Routes/web.php:18 Route::get('production/{path?}', function (Request $request, ?string $path = null) {
- Modules/Production/Routes/web.php:34 Route::get('fg-quantity-policy', [ProductionFgQuantityPolicySettingController::class, 'index'])->name('fg-quantity-policy.index');
- Modules/Production/Routes/web.php:35 Route::put('fg-quantity-policy', [ProductionFgQuantityPolicySettingController::class, 'update'])->name('fg-quantity-policy.update');
- Modules/Production/Routes/web.php:37 Route::resource('boms', ProductionBomController::class);
- Modules/Production/Routes/web.php:39 Route::get('orders/bom-preview', [ProductionOrderController::class, 'bomPreview'])->name('orders.bom-preview');
- Modules/Production/Routes/web.php:40 Route::resource('orders', ProductionOrderController::class)->except(['destroy']);
- Modules/Production/Routes/web.php:41 Route::get('material-shortages', [ProductionMaterialSummaryController::class, 'index'])->name('material-shortages.index');
- Modules/Production/Routes/web.php:42 Route::get('material-shortages/orders', [ProductionMaterialSummaryController::class, 'orders'])->name('material-shortages.orders');
- Modules/Production/Routes/web.php:44 Route::post('orders/{order}/release', [ProductionOrderController::class, 'release'])->name('orders.release');
- Modules/Production/Routes/web.php:45 Route::post('orders/{order}/cancel', [ProductionOrderController::class, 'cancel'])->name('orders.cancel');
- Modules/Production/Routes/web.php:47 Route::get('batches/{batch}', [ProductionBatchController::class, 'show'])->name('batches.show');
- Modules/Production/Routes/web.php:48 Route::get('batches/{batch}/print-label-slip', [ProductionBatchController::class, 'printLabelSlip'])->name('batches.print-label-slip');
- Modules/Production/Routes/web.php:49 Route::get('batches/{batch}/trace', [ProductionBatchController::class, 'trace'])->name('batches.trace');
- Modules/Production/Routes/web.php:50 Route::post('batches/{batch}/apply-planned-from-bom-snapshot', [ProductionBatchController::class, 'applyPlannedFromBomSnapshot'])->name('batches.apply-planned-from-bom-snapshot');
- Modules/Production/Routes/web.php:51 Route::post('batches/{batch}/consumptions', [ProductionBatchController::class, 'storeConsumption'])->name('batches.consumptions.store');
- Modules/Production/Routes/web.php:52 Route::post('batches/{batch}/consumptions/{consumption}/assign-warehouse-batch', [ProductionBatchController::class, 'assignConsumptionWarehouseBatch'])->name('batches.consumptions.assign-warehouse-batch');
- Modules/Production/Routes/web.php:53 Route::post('batches/{batch}/post-consumptions', [ProductionBatchController::class, 'postConsumptions'])->name('batches.post-consumptions');
- Modules/Production/Routes/web.php:54 Route::post('batches/{batch}/outputs', [ProductionBatchController::class, 'storeOutput'])->name('batches.outputs.store');
- Modules/Production/Routes/web.php:55 Route::post('batches/{batch}/rework-orders', [ProductionBatchController::class, 'storeReworkOrder'])->name('batches.rework-orders.store');
- Modules/Production/Routes/web.php:56 Route::post('batches/{batch}/rework-orders/{rework}/approve', [ProductionBatchController::class, 'approveReworkOrder'])->name('batches.rework-orders.approve');

### Controllers

- Modules/Production/Http/Controllers/ProductionBatchController.php
- Modules/Production/Http/Controllers/ProductionBomController.php
- Modules/Production/Http/Controllers/ProductionFgQuantityPolicySettingController.php
- Modules/Production/Http/Controllers/ProductionMaterialSummaryController.php
- Modules/Production/Http/Controllers/ProductionOrderController.php

### Entities / Models

- Modules/Production/Entities/ProductionBatch.php
- Modules/Production/Entities/ProductionBatchConsumption.php
- Modules/Production/Entities/ProductionBatchOutput.php
- Modules/Production/Entities/ProductionBom.php
- Modules/Production/Entities/ProductionBomItem.php
- Modules/Production/Entities/ProductionCompanyFgPolicy.php
- Modules/Production/Entities/ProductionOrder.php
- Modules/Production/Entities/ProductionOrderBomSnapshotItem.php
- Modules/Production/Entities/ProductionReworkOrder.php

### Services

- Modules/Production/app/Services/ProductionMaterialSummaryService.php
- Modules/Production/Services/ProductionBatchPlannedLinesApplicator.php
- Modules/Production/Services/ProductionBomFgCostSyncService.php
- Modules/Production/Services/ProductionFgQuantityPolicyService.php
- Modules/Production/Services/ProductionMaterialSummaryService.php
- Modules/Production/Services/ProductionOrderMaterialRequirementsSummary.php
- Modules/Production/Services/ProductionOrderMaterialReservationService.php
- Modules/Production/Services/ProductionOrderSalesOrderPrefill.php
- Modules/Production/Services/ProductionPlannedConsumptionFromSnapshotService.php
- Modules/Production/Services/ProductionPostingService.php

### Views Snapshot

- Modules/Production/Resources/views/batches/partials/completion-workflow.blade.php
- Modules/Production/Resources/views/batches/print-label-slip.blade.php
- Modules/Production/Resources/views/batches/show.blade.php
- Modules/Production/Resources/views/batches/trace.blade.php
- Modules/Production/Resources/views/boms/ajax/create.blade.php
- Modules/Production/Resources/views/boms/ajax/edit.blade.php
- Modules/Production/Resources/views/boms/create.blade.php
- Modules/Production/Resources/views/boms/edit.blade.php
- Modules/Production/Resources/views/boms/index.blade.php
- Modules/Production/Resources/views/boms/partials/bom-line-row.blade.php
- Modules/Production/Resources/views/boms/partials/bom-line-unit-select.blade.php
- Modules/Production/Resources/views/boms/partials/form.blade.php
- Modules/Production/Resources/views/boms/show.blade.php
- Modules/Production/Resources/views/fg-quantity-policy/index.blade.php
- Modules/Production/Resources/views/material-shortages/index.blade.php
- Modules/Production/Resources/views/material-shortages/orders.blade.php
- Modules/Production/Resources/views/orders/ajax/create.blade.php
- Modules/Production/Resources/views/orders/ajax/edit.blade.php
- Modules/Production/Resources/views/orders/create.blade.php
- Modules/Production/Resources/views/orders/edit.blade.php
- Modules/Production/Resources/views/orders/index.blade.php
- Modules/Production/Resources/views/orders/partials/bom-fg-sync-script.blade.php
- Modules/Production/Resources/views/orders/partials/bom-preview-fragment.blade.php
- Modules/Production/Resources/views/orders/partials/bom-preview-panel.blade.php
- Modules/Production/Resources/views/orders/partials/bom-preview-script.blade.php
- Modules/Production/Resources/views/orders/partials/material-requirements.blade.php
- Modules/Production/Resources/views/orders/partials/material-requirements-table.blade.php
- Modules/Production/Resources/views/orders/partials/order-bom-header-fields.blade.php
- Modules/Production/Resources/views/orders/partials/order-warehouse-row.blade.php
- Modules/Production/Resources/views/orders/show.blade.php

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

- FUNC_LOGIC/PRODUCTION_BUSINESS.md
- PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md
- PROJECT BIOMIXING/UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md
- PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION.md
- FUNC_TEST/01_BIOMIXING_TEST_MATRIX.md
- FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md

## Reading Map

| Need | Read |
| --- | --- |
| Production module lifecycle, BOM, reserve, batch, shortage summary | PRODUCTION_BUSINESS.md |
| Customer-facing Biomixing SOP | PROJECT BIOMIXING/PRODUCTION_MODULE_SOP.md |
| Quotation to sales order UI runbook | PROJECT BIOMIXING/UI_RUNBOOK_PHASE1_QUOTATION_TO_SO.md |
| Planning and pre-production UI runbook | PROJECT BIOMIXING/UI_RUNBOOK_PHASE2_PLANNING_PREPRODUCTION.md |
| Biomixing test matrix | FUNC_TEST/01_BIOMIXING_TEST_MATRIX.md |
| BOM finished-good cost sync plan | FUNC_IMPROVE/20_BOM_FG_COST_SYNC_IMPLEMENTATION_PLAN.md |

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
