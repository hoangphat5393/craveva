# UAT Execution Result - SO/DO + PO/GRN (2026-03-30)

## Automated Test Result (Local)

- Command bundle:
    - `php artisan test tests/Feature/SalesDoCutoverRuntimeTest.php tests/Feature/SalesDoMigrateDataCommandTest.php tests/Feature/SalesDoMigrateRollbackCommandTest.php tests/Feature/SalesDoServiceLifecycleTest.php tests/Feature/SalesShipmentOptionBTest.php tests/Feature/GrnCutoverRuntimeTest.php tests/Feature/GrnMigrateDataCommandTest.php tests/Feature/GrnMigrateRollbackCommandTest.php tests/Feature/GrnServiceLifecycleTest.php tests/Feature/PurchaseInboundStockFlowTest.php tests/Unit/DeliveryOrderObserverGuardTest.php tests/Feature/FlowPermissionAliasTest.php`
- Result: **PASS**
    - Tests: `24`
    - Assertions: `79`
    - Failed: `0`

## Staging Technical Verification

- Sales DO migration dry-run status:
    - `source.shipments_count = 1`
    - `target.headers_migrated_count = 1`
    - `target.items_migrated_count = 1`
    - `pending.shipments_count = 0`
    - `pending.items_count = 0`
- GRN migration dry-run status:
    - `source.headers_count = 3`
    - `target.headers_migrated_count = 3`
    - `target.items_migrated_count = 0`
    - `pending.headers_count = 0`
    - `pending.items_count = 0`
- Environment:
    - HTTP health: `200`
    - Cutover flags active in `.env`:
        - `PURCHASE_FLOW_NAMING_MODE=compat_v2`
        - `PURCHASE_DO_GRN_CUTOVER_ENABLED=true`
    - Latest log check window (`15:00+`): `0` critical/error entries

## Manual UAT Checklist (To Be Completed by Business Team)

### Sales Side

- [ ] Create Sales DO from SO.
- [ ] Confirm -> Ship -> Deliver lifecycle works.
- [ ] Reverse/Cancel behavior is correct.
- [ ] Stock out posts once only (no double-post).
- [ ] Invoice flow remains valid after DO lifecycle.

### Purchase Side

- [ ] Create GRN from PO.
- [ ] Status change `draft -> inbound -> received` works.
- [ ] Stock in posts once only (guard prevents double inbound).
- [ ] Bill flow remains valid after GRN process.

### Permissions

- [ ] Sales DO permission matrix works as expected.
- [ ] GRN permission matrix works as expected.

## Decision

- Business UAT Owner:
- QA Owner:
- Date/Time:
- Final decision:
    - [ ] GO
    - [ ] NO-GO
- Notes / blockers:
    - ...
