# Evidence Commands

Tat ca command chay tai `E:\web\craveva-staging`. Khong co command ghi/xoa du lieu nghiep vu.

## Config

```powershell
php artisan config:show warehouse
php artisan config:show production
```

Ket qua chinh:

- PO delivered inbound: false.
- GRN received inbound: true.
- Sales outbound: enabled, shipment mode.
- Negative stock: false.
- Production BOM-first va variance approval: true.
- Yield/UOM shadow: false.

## Routes

```powershell
php artisan route:list --path=production
php artisan route:list --path=sales-do
php artisan route:list --path=grn
php artisan route:list --path=estimates
php artisan route:list --path=warehouse
```

Exit code: 0 cho moi command. Route counts: Production 36, Sales DO 15, GRN 10, Estimate 23, Warehouse 33.

## Targeted tests

```powershell
php artisan test --compact `
  tests\Unit\StockMovementServiceTest.php `
  tests\Feature\ProductionPostingServiceTest.php `
  tests\Feature\PurchaseInboundStockFlowTest.php `
  tests\Feature\SalesShipmentOptionBTest.php `
  tests\Feature\GrnServicePersistenceTest.php `
  tests\Feature\GrnServiceLifecycleTest.php `
  tests\Feature\EstimateConvertToSalesOrderTest.php `
  tests\Unit\SalesDoInvoiceGuardServiceTest.php `
  tests\Feature\CreditNoteSalesReturnStockTest.php `
  tests\Feature\VendorCreditPurchaseReturnStockTest.php
```

Exit code: 0. Ket qua: 47 passed, 1 skipped, 139 assertions, 51.72s.

## Remediation verification 2026-07-04

```powershell
php artisan test --compact tests\Feature\StockMovementCommandIdempotencyTest.php `
  tests\Feature\GrnServiceLifecycleTest.php `
  tests\Feature\GrnServicePersistenceTest.php `
  tests\Feature\PurchaseInboundStockFlowTest.php `
  tests\Feature\SalesShipmentOptionBTest.php `
  tests\Feature\ProductionPostingServiceTest.php `
  tests\Feature\CreditNoteSalesReturnStockTest.php `
  tests\Feature\VendorCreditPurchaseReturnStockTest.php
```

Ket qua P0 batch: 45 passed, 144 assertions. Production sau own-reservation/locking: 21 passed, 76 assertions. Reservation/cancel regression batch: 33 passed, 105 assertions.

Final combined targeted run: 59 passed, 1 skipped, 178 assertions. Case Estimate conversion retry duoc bo sung nhung fixture hien tai skip khi database test khong co tenant Estimate hop le; source locking da duoc static-verified, runtime fixture van la test gap.

```powershell
$env:DB_DATABASE='craveva_codex06_default_fix_20260702'
php artisan migrate --force --path=database/migrations/2026_07_04_000001_create_stock_movement_commands_table.php
```

Ket qua: migration pass, batch 2, chi tren DB disposable.

## Static evidence searches

```powershell
rg -n "DB::transaction|lockForUpdate|idempotency|status|release|cancel|reverse" Modules/Warehouse/Services Modules/Production/Services Modules/Purchase/Services Modules/Purchase/Observers
rg -n "stock_movements|idempotency_key|unique\(" database/migrations database/schema/mysql-schema.dump
rg -n "function convertToSalesOrder|lastOrderNumber|estimate_id" app database/schema/mysql-schema.dump
```

## Read-only reconciliation

Reconciliation duoc chay bang PHP bootstrap Laravel va `DB::selectOne()`; chi `SELECT COUNT`, aggregate va danh sach 5 Production batch anomaly. Khong goi model save/update/delete.

Ket qua day du nam trong `06_RECONCILIATION_RESULTS.md`.

## Database note

- Reconciliation dung DB local hien tai o che do chi doc.
- Fresh-install contract khong chay lai trong audit nay; bang chung pass nam trong `docs/audits/full-system-2026-07-02/`.
- Khong dung staging/hub.
- Khong tao, drop, truncate hay update database trong audit nay.
