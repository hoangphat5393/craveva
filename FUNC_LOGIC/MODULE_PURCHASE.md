# Purchase Business Logic

Status: Draft từ source code scan 2026-07-04. File này là bản khởi tạo để BA/CTO tiếp tục xác nhận nghiệp vụ, không phải đặc tả đã khóa.

## Module Metadata

- Module: Purchase
- Alias: purchase
- Provider: Modules\Purchase\Providers\PurchaseServiceProvider, Modules\Purchase\Providers\EventServiceProvider
- Source root: Modules/Purchase/

## Business Purpose

Quản lý sản phẩm mua, vendor, purchase order, delivery/GRN, bill, vendor credit, payment và một phần sales fulfillment.

## Main Business Flow Draft

- Tạo vendor và purchase product.
- Tạo purchase order, theo dõi delivery/GRN.
- Tạo bill, vendor credit/payment; các extension sales DO/shipment kết nối kho.

## Code Evidence

### Routes

- Modules/Purchase/Routes/api.php
- Modules/Purchase/Routes/web.php

### Route Entry Points Snapshot

- Modules/Purchase/Routes/web.php:39 Route::get('purchase-products/layout', [PurchaseProductController::class, 'layout'])->name('purchase_products.layout');
- Modules/Purchase/Routes/web.php:40 Route::get('purchase-products/add-images', [PurchaseProductController::class, 'addImages'])->name('purchase_products.add_images');
- Modules/Purchase/Routes/web.php:41 Route::post('purchase-products/store-images', [PurchaseProductController::class, 'storeImages'])->name('purchase_products.store_images');
- Modules/Purchase/Routes/web.php:42 Route::post('purchase-products/change-status', [PurchaseProductController::class, 'changeStatus'])->name('purchase_products.change_status');
- Modules/Purchase/Routes/web.php:43 Route::post('purchase-products/change-purchase-allow', [PurchaseProductController::class, 'changePurchaseAllow'])->name('purchase_products.change_purchase_allow');
- Modules/Purchase/Routes/web.php:44 Route::get('purchase-products/adjust-inventory', [PurchaseProductController::class, 'adjustInventory'])->name('purchase_products.adjust_inventory');
- Modules/Purchase/Routes/web.php:45 Route::post('purchase-products/update-inventory', [PurchaseProductController::class, 'updateInventory'])->name('purchase_products.update_inventory');
- Modules/Purchase/Routes/web.php:46 Route::post('purchase-products/apply-quick-action', [PurchaseProductController::class, 'applyQuickAction'])->name('purchase_products.apply_quick_action');
- Modules/Purchase/Routes/web.php:47 Route::get('purchase-products/options', [PurchaseProductController::class, 'allPurchaseProductOption'])->name('purchase_products.options');
- Modules/Purchase/Routes/web.php:49 Route::get('import', [PurchaseProductController::class, 'importProduct'])->name('purchase-products.import');
- Modules/Purchase/Routes/web.php:50 Route::post('import', [PurchaseProductController::class, 'importStore'])->name('purchase-products.import.store');
- Modules/Purchase/Routes/web.php:51 Route::post('import/process', [PurchaseProductController::class, 'importProcess'])->name('purchase-products.import.process');
- Modules/Purchase/Routes/web.php:53 Route::resource('purchase-products', PurchaseProductController::class);
- Modules/Purchase/Routes/web.php:56 Route::resource('adjustment-reasons', StockAdjustmentReasonController::class);
- Modules/Purchase/Routes/web.php:63 Route::post('apply-quick-action', [PurchaseVendorPaymentController::class, 'applyQuickAction'])->name('vendor-payments.apply_quick_action');
- Modules/Purchase/Routes/web.php:66 Route::get('fetch-bills/{id?}', [PurchaseVendorPaymentController::class, 'fetchBills'])->name('vendor-payments-fetch.fetch_bill');
- Modules/Purchase/Routes/web.php:67 Route::resource('vendor-payments', PurchaseVendorPaymentController::class);
- Modules/Purchase/Routes/web.php:68 Route::get('vendor-payments/download/{id}', [PurchaseVendorPaymentController::class, 'download'])->name('vendor-payments.download');
- Modules/Purchase/Routes/web.php:69 Route::get('vendor-payments/clearAmount', [PurchaseVendorPaymentController::class, 'clearAmount'])->name('vendor-payments.clearAmount');
- Modules/Purchase/Routes/web.php:72 Route::resource('purchase-contacts', PurchaseContactController::class);

### Controllers

- Modules/Purchase/Http/Controllers/DeliveryOrderController.php
- Modules/Purchase/Http/Controllers/DeliveryOrderSettingsController.php
- Modules/Purchase/Http/Controllers/PurchaseBillController.php
- Modules/Purchase/Http/Controllers/PurchaseContactController.php
- Modules/Purchase/Http/Controllers/PurchaseInventoryController.php
- Modules/Purchase/Http/Controllers/PurchaseInventoryFileController.php
- Modules/Purchase/Http/Controllers/PurchaseOrderController.php
- Modules/Purchase/Http/Controllers/PurchaseOrderFileController.php
- Modules/Purchase/Http/Controllers/PurchaseOrderReportController.php
- Modules/Purchase/Http/Controllers/PurchaseProductController.php
- Modules/Purchase/Http/Controllers/PurchaseSettingController.php
- Modules/Purchase/Http/Controllers/PurchaseSmtpSettingController.php
- Modules/Purchase/Http/Controllers/PurchaseVendorCategoryController.php
- Modules/Purchase/Http/Controllers/PurchaseVendorController.php
- Modules/Purchase/Http/Controllers/PurchaseVendorPaymentController.php
- Modules/Purchase/Http/Controllers/ReportsController.php
- Modules/Purchase/Http/Controllers/SalesShipmentController.php
- Modules/Purchase/Http/Controllers/StockAdjustmentReasonController.php
- Modules/Purchase/Http/Controllers/VendorCreditController.php
- Modules/Purchase/Http/Controllers/VendorNotesController.php

### Entities / Models

- Modules/Purchase/Entities/DeliveryOrderItem.php
- Modules/Purchase/Entities/GrnItem.php
- Modules/Purchase/Entities/OrderDeliveryItem.php
- Modules/Purchase/Entities/PurchaseBill.php
- Modules/Purchase/Entities/PurchaseBillHistory.php
- Modules/Purchase/Entities/PurchaseBillItem.php
- Modules/Purchase/Entities/PurchaseBillNumberSetting.php
- Modules/Purchase/Entities/PurchaseInventory.php
- Modules/Purchase/Entities/PurchaseInventoryFile.php
- Modules/Purchase/Entities/PurchaseInventoryHistory.php
- Modules/Purchase/Entities/PurchaseItem.php
- Modules/Purchase/Entities/PurchaseItemImage.php
- Modules/Purchase/Entities/PurchaseItemTax.php
- Modules/Purchase/Entities/PurchaseManagementSetting.php
- Modules/Purchase/Entities/PurchaseNotificationSetting.php
- Modules/Purchase/Entities/PurchaseOrder.php
- Modules/Purchase/Entities/PurchaseOrderFile.php
- Modules/Purchase/Entities/PurchaseOrderHistory.php
- Modules/Purchase/Entities/PurchaseOrderSetting.php
- Modules/Purchase/Entities/PurchasePaymentBill.php
- Modules/Purchase/Entities/PurchasePaymentHistory.php
- Modules/Purchase/Entities/PurchaseProduct.php
- Modules/Purchase/Entities/PurchaseProductHistory.php
- Modules/Purchase/Entities/PurchaseSetting.php
- Modules/Purchase/Entities/PurchaseStockAdjustment.php
- Modules/Purchase/Entities/PurchaseStockAdjustmentReason.php
- Modules/Purchase/Entities/PurchaseVendor.php
- Modules/Purchase/Entities/PurchaseVendorCategory.php
- Modules/Purchase/Entities/PurchaseVendorContact.php
- Modules/Purchase/Entities/PurchaseVendorCredit.php
- Modules/Purchase/Entities/PurchaseVendorCreditHistory.php
- Modules/Purchase/Entities/PurchaseVendorCreditItemImage.php
- Modules/Purchase/Entities/PurchaseVendorHistory.php
- Modules/Purchase/Entities/PurchaseVendorItem.php
- Modules/Purchase/Entities/PurchaseVendorNote.php
- Modules/Purchase/Entities/PurchaseVendorPayment.php
- Modules/Purchase/Entities/PurchaseVendorUserNotes.php
- Modules/Purchase/Entities/SalesDo.php
- Modules/Purchase/Entities/SalesDoItem.php
- Modules/Purchase/Entities/SalesShipment.php
- Modules/Purchase/Entities/SalesShipmentItem.php

### Services

- Modules/Purchase/Services/GrnService.php
- Modules/Purchase/Services/InventoryImportRowProcessor.php
- Modules/Purchase/Services/ProductionFgInventoryLedgerSync.php
- Modules/Purchase/Services/ProductOpeningStockWarehouseSync.php
- Modules/Purchase/Services/ProductSkuGenerator.php
- Modules/Purchase/Services/SalesDoInvoiceGuardService.php
- Modules/Purchase/Services/SalesDoService.php

### Views Snapshot

- Modules/Purchase/Resources/views/bills/ajax/add_item.blade.php
- Modules/Purchase/Resources/views/bills/ajax/create.blade.php
- Modules/Purchase/Resources/views/bills/ajax/edit.blade.php
- Modules/Purchase/Resources/views/bills/ajax/history.blade.php
- Modules/Purchase/Resources/views/bills/ajax/overview.blade.php
- Modules/Purchase/Resources/views/bills/create.blade.php
- Modules/Purchase/Resources/views/bills/index.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice_pdf_css.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice-1.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice-2.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice-3.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice-4.blade.php
- Modules/Purchase/Resources/views/bills/pdf/invoice-5.blade.php
- Modules/Purchase/Resources/views/bills/show.blade.php
- Modules/Purchase/Resources/views/components/purchase-tab.blade.php
- Modules/Purchase/Resources/views/delivery-order/ajax/create.blade.php
- Modules/Purchase/Resources/views/delivery-order/ajax/edit.blade.php
- Modules/Purchase/Resources/views/delivery-order/ajax/items.blade.php
- Modules/Purchase/Resources/views/delivery-order/ajax/overview.blade.php
- Modules/Purchase/Resources/views/delivery-order/create.blade.php
- Modules/Purchase/Resources/views/delivery-order/index.blade.php
- Modules/Purchase/Resources/views/delivery-order/pdf/delivery-order-1.blade.php
- Modules/Purchase/Resources/views/delivery-order/show.blade.php
- Modules/Purchase/Resources/views/layouts/master.blade.php
- Modules/Purchase/Resources/views/notifications/admin_new_vendor_payment.blade.php
- Modules/Purchase/Resources/views/notifications/admin_update_vendor_payment.blade.php
- Modules/Purchase/Resources/views/partials/product-type-select.blade.php
- Modules/Purchase/Resources/views/purchase-inventory/ajax/add_quantity.blade.php
- Modules/Purchase/Resources/views/purchase-inventory/ajax/add_value.blade.php
- Modules/Purchase/Resources/views/purchase-inventory/ajax/create.blade.php

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

- FUNC_LOGIC/SALES_BUSINESS.md
- FUNC_LOGIC/PURCHASE_RETURN_BUSINESS.md
- FUNC_LOGIC/WAREHOUSE_BUSINESS.md
- FUNC_LOGIC/MODULE_WAREHOUSE.md
- FUNC_LOGIC/SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md
- FUNC_LOGIC/SALES_FULFILLMENT_SCHEMA_MATRIX.md
- docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md

## Reading Map

| Need | Read |
| --- | --- |
| Purchase in the full PO / GRN / SO / Invoice / Warehouse flow | SALES_BUSINESS.md |
| Purchase return / Vendor Credit stock impact | PURCHASE_RETURN_BUSINESS.md |
| Purchase and warehouse UAT checklist | SALES_PURCHASE_WAREHOUSE_UAT_CHECKLIST.md |
| Canonical GRN / Sales DO schema and removed legacy tables | SALES_FULFILLMENT_SCHEMA_MATRIX.md |
| Warehouse stock rules used by purchase flows | MODULE_WAREHOUSE.md, WAREHOUSE_BUSINESS.md |
| Environment/config flags for purchase inbound and warehouse | docs/WAREHOUSE_PURCHASE_ENV_REFERENCE.md |

## Next Audit Checklist

- [ ] Đọc controller chính và ghi lại từng action create/update/delete/status.
- [ ] Đối chiếu DB schema/migration với entity fillable/casts/relations.
- [ ] Mở UI route chính và xác nhận workflow thực tế.
- [ ] Kiểm tra permission/menu/role gating.
- [ ] Ghi test URL và dữ liệu mẫu để UAT.
