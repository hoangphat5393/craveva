# Warehouse Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Warehouse
- Alias: warehouse
- Provider: Modules\Warehouse\Providers\WarehouseServiceProvider
- Source root: Modules/Warehouse/

## Business Purpose

Quản lý warehouse, stock, batch, movement, transfer, reservation và flow setting theo công ty.

## Main Business Flow Draft

- Tạo warehouse và cấu hình flow theo công ty.
- Ghi nhận stock theo product/batch.
- Transfer/movement/reservation/posting phát sinh từ purchase, sales, production.

## Code Evidence

### Routes

- Modules/Warehouse/Routes/api.php
- Modules/Warehouse/Routes/web.php

### Route Entry Points Snapshot

- Modules/Warehouse/Routes/api.php:18 Route::get('warehouse/availability', [WarehouseAvailabilityController::class, 'show'])->name('warehouse.availability');
- Modules/Warehouse/Routes/web.php:23 Route::get('warehouse/{path?}', function (Request $request, ?string $path = null) {
- Modules/Warehouse/Routes/web.php:33 Route::get('warehouse-stock/{path?}', function (Request $request, ?string $path = null) {
- Modules/Warehouse/Routes/web.php:43 Route::get('warehouse-movements', function (Request $request) {
- Modules/Warehouse/Routes/web.php:49 Route::redirect('warehouse-transfer', '/account/warehouse-transfer', 301);
- Modules/Warehouse/Routes/web.php:57 Route::get('import', [WarehouseController::class, 'importWarehouse'])->name('warehouse.import');
- Modules/Warehouse/Routes/web.php:58 Route::post('import', [WarehouseController::class, 'importStore'])->name('warehouse.import.store');
- Modules/Warehouse/Routes/web.php:59 Route::post('import/process', [WarehouseController::class, 'importProcess'])->name('warehouse.import.process');
- Modules/Warehouse/Routes/web.php:62 Route::post('warehouse/update-order', [WarehouseController::class, 'updateOrder'])->name('warehouse.update-order');
- Modules/Warehouse/Routes/web.php:63 Route::post('warehouse/change-status', [WarehouseController::class, 'changeStatus'])->name('warehouse.change_status');
- Modules/Warehouse/Routes/web.php:64 Route::post('warehouse/apply-quick-action', [WarehouseController::class, 'applyQuickAction'])->name('warehouse.apply_quick_action');
- Modules/Warehouse/Routes/web.php:65 Route::get('warehouse/company-flow-settings', [WarehouseCompanyFlowSettingController::class, 'index'])
- Modules/Warehouse/Routes/web.php:67 Route::put('warehouse/company-flow-settings', [WarehouseCompanyFlowSettingController::class, 'update'])
- Modules/Warehouse/Routes/web.php:69 Route::resource('warehouse', WarehouseController::class)->names('warehouse');
- Modules/Warehouse/Routes/web.php:70 Route::get('warehouse-movements', [WarehouseMovementController::class, 'index'])->name('warehouse.movements.index');
- Modules/Warehouse/Routes/web.php:71 Route::get('warehouse-product-batches', [WarehouseProductBatchController::class, 'index'])->name('warehouse.product-batches.index');
- Modules/Warehouse/Routes/web.php:72 Route::get('warehouse-product-batches/{warehouseProductBatch}', [WarehouseProductBatchController::class, 'show'])->name('warehouse.product-batches.show');
- Modules/Warehouse/Routes/web.php:73 Route::resource('warehouse-stock', WarehouseStockController::class)->names('warehouse.stock');
- Modules/Warehouse/Routes/web.php:74 Route::get('warehouse-transfer', [WarehouseTransferController::class, 'create'])->name('warehouse.transfer.create');
- Modules/Warehouse/Routes/web.php:75 Route::post('warehouse-transfer', [WarehouseTransferController::class, 'store'])->name('warehouse.transfer.store');

### Controllers

- Modules/Warehouse/Http/Controllers/Concerns/HandlesWarehouseErrors.php
- Modules/Warehouse/Http/Controllers/WarehouseAvailabilityController.php
- Modules/Warehouse/Http/Controllers/WarehouseCompanyFlowSettingController.php
- Modules/Warehouse/Http/Controllers/WarehouseController.php
- Modules/Warehouse/Http/Controllers/WarehouseMovementController.php
- Modules/Warehouse/Http/Controllers/WarehouseProductBatchController.php
- Modules/Warehouse/Http/Controllers/WarehouseStockController.php
- Modules/Warehouse/Http/Controllers/WarehouseTransferController.php

### Entities / Models

- Modules/Warehouse/Entities/InvoiceWarehouseStockPosting.php
- Modules/Warehouse/Entities/ProductUnitConversion.php
- Modules/Warehouse/Entities/StockReservation.php
- Modules/Warehouse/Entities/Warehouse.php
- Modules/Warehouse/Entities/WarehouseCompanyFlowSetting.php
- Modules/Warehouse/Entities/WarehouseProductBatch.php
- Modules/Warehouse/Entities/WarehouseProductStock.php
- Modules/Warehouse/Entities/WarehouseSyncReconciliationLog.php

### Services

- Modules/Warehouse/Services/AllowAllSalesReturnInboundGate.php
- Modules/Warehouse/Services/CreditNoteWarehouseStockService.php
- Modules/Warehouse/Services/EnsureDefaultWarehouseService.php
- Modules/Warehouse/Services/InvoiceWarehouseStockService.php
- Modules/Warehouse/Services/OrderCompletionShippedSalesDoGate.php
- Modules/Warehouse/Services/ProductSellableUnitsService.php
- Modules/Warehouse/Services/ProductUnitConversionSyncService.php
- Modules/Warehouse/Services/ProductUnitPriceResolver.php
- Modules/Warehouse/Services/ProductUnitQuantityHintService.php
- Modules/Warehouse/Services/SalesShipmentStockService.php
- Modules/Warehouse/Services/StockMovementService.php
- Modules/Warehouse/Services/StockReservationService.php
- Modules/Warehouse/Services/VendorCreditWarehouseStockService.php
- Modules/Warehouse/Services/WarehouseAvailabilityService.php
- Modules/Warehouse/Services/WarehouseFlowConfigService.php
- Modules/Warehouse/Services/WarehouseFlowPolicyService.php
- Modules/Warehouse/Services/WarehouseQueryService.php
- Modules/Warehouse/Services/WarehouseReconciliationService.php
- Modules/Warehouse/Services/WarehouseUnitConversionService.php

### Views Snapshot

- Modules/Warehouse/Resources/views/ajax/create.blade.php
- Modules/Warehouse/Resources/views/ajax/edit.blade.php
- Modules/Warehouse/Resources/views/ajax/import.blade.php
- Modules/Warehouse/Resources/views/ajax/import_progress.blade.php
- Modules/Warehouse/Resources/views/company-flow-settings/index.blade.php
- Modules/Warehouse/Resources/views/create.blade.php
- Modules/Warehouse/Resources/views/edit.blade.php
- Modules/Warehouse/Resources/views/import.blade.php
- Modules/Warehouse/Resources/views/index.blade.php
- Modules/Warehouse/Resources/views/layouts/master.blade.php
- Modules/Warehouse/Resources/views/movements/index.blade.php
- Modules/Warehouse/Resources/views/partials/ajax-form-submit-script.blade.php
- Modules/Warehouse/Resources/views/partials/warehouse-type-label.blade.php
- Modules/Warehouse/Resources/views/product-batches/index.blade.php
- Modules/Warehouse/Resources/views/product-batches/show.blade.php
- Modules/Warehouse/Resources/views/sections/setting-sidebar.blade.php
- Modules/Warehouse/Resources/views/sections/sidebar.blade.php
- Modules/Warehouse/Resources/views/show.blade.php
- Modules/Warehouse/Resources/views/stock/ajax/create.blade.php
- Modules/Warehouse/Resources/views/stock/create.blade.php
- Modules/Warehouse/Resources/views/stock/index.blade.php
- Modules/Warehouse/Resources/views/stock/partials/inventory-reconciliation-widget.blade.php
- Modules/Warehouse/Resources/views/transfer/ajax/create.blade.php
- Modules/Warehouse/Resources/views/transfer/create.blade.php

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

- FUNC_LOGIC/WAREHOUSE_BUSINESS.md
- FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md
- FUNC_LOGIC/SALES_BUSINESS.md
- FUNC_LOGIC/SALES_RETURN_BUSINESS.md
- FUNC_LOGIC/PURCHASE_RETURN_BUSINESS.md
- FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md
- FUNC_LOGIC/SALES_FULFILLMENT_QA_CHECKLIST.md
- FUNC_LOGIC/SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md
- FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md
- FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK.md
- docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md

## Reading Map

| Need | Read |
| --- | --- |
| PO / GRN / SO / Sales DO / Invoice / Warehouse overview | SALES_BUSINESS.md |
| Warehouse-only flows: adjustment, transfer, movement | WAREHOUSE_BUSINESS.md |
| Sales return / Credit Note inbound stock | SALES_RETURN_BUSINESS.md |
| Purchase return / Vendor Credit outbound stock | PURCHASE_RETURN_BUSINESS.md |
| Warehouse technical details: DB, URL, permission, future bin/location | WAREHOUSE_MASTER_GUIDE.md |
| Sales DO / GRN schema and removed legacy tables | SALES_FULFILLMENT_SCHEMA_MATRIX.md |
| Current QA and test coverage status | SALES_FULFILLMENT_QA_CHECKLIST.md |
| End-to-end UAT for purchase, sales, and warehouse | SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md |
| Environment/config flags for warehouse, PO, GRN, Sales DO, AI REST | docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md |
| Warehouse runbook and WUP upgrade plan | FUNC_IMPROVE/04_WH_RUNBOOK_UPGRADE.md |
| Opening stock and inventory direction | FUNC_IMPROVE/13_OPENING_STOCK_VS_WAREHOUSE_STOCK.md |

## Operations Menu

Warehouse sits in the Operations menu group:

- Warehouses
- Adjust stock
- Transfer stock
- Stock movements

For UI steps and UAT flow, use SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md appendix C.

## Terminology

- Inbound: an external system calls into ERP, for example POST /api/integrations/orders to create an SO through AI or a third party.
- Outbound: ERP calls out to another system, for example Webhooks pushing an event.
- Sales outbound: stock leaves for sales, usually when Sales DO is shipped if mode is shipment.
- Purchase inbound: stock enters from purchase, usually through PO delivered or GRN received. Do not use both for the same receiving event.

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
