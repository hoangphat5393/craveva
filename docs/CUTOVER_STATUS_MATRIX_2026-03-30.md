# Cutover Status Matrix (2026-03-30)

## Runtime Status (Staging)

| Flow                      | UI Naming | Runtime Flag                      | Legacy Runtime                             | New Runtime                    | Current Runtime on Staging           |
| ------------------------- | --------- | --------------------------------- | ------------------------------------------ | ------------------------------ | ------------------------------------ |
| SO -> Sales DO -> Invoice | Sales DO  | `PURCHASE_DO_GRN_CUTOVER_ENABLED` | `sales_shipments` + `sales_shipment_items` | `sales_dos` + `sales_do_items` | **New runtime active** (pending = 0) |
| PO -> GRN -> Bill         | GRN       | `PURCHASE_DO_GRN_CUTOVER_ENABLED` | `delivery_orders` + `delivery_order_items` | `grns` + `grn_items`           | **New runtime active** (pending = 0) |

## Data Migration Status

### Sales DO migration status report

- File: `storage/app/reports/sales-do-migrate-status-current.json`
- Snapshot:
    - `source.shipments_count = 1`
    - `source.items_count = 1`
    - `target.headers_migrated_count = 1`
    - `target.items_migrated_count = 1`
    - `pending.shipments_count = 0`
    - `pending.items_count = 0`

### GRN migration status report

- File: `storage/app/reports/grn-migrate-status-current.json`
- Snapshot:
    - `source.headers_count = 3`
    - `source.items_count = 0`
    - `target.headers_migrated_count = 3`
    - `target.items_migrated_count = 0`
    - `pending.headers_count = 0`
    - `pending.items_count = 0`

## Rollback Validation

### Sales DO

- Manifest rollback execute path has been rehearsed and validated earlier in staging.

### GRN

- Rollback execute was validated in staging:
    1. Execute rollback on `grn-migrate-manifest-20260330-143700-f5aca3d3.json` -> deleted 3 headers.
    2. Dry-run confirmed pending restored to 3.
    3. Re-executed migration (`grn-migrate-execute-all-rerun.json`) -> migrated 3 headers again.
- Latest GRN rollback manifest:
    - `storage/app/reports/grn-migrate-manifest-20260330-145207-87f9025a.json`

## Environment Health Snapshot

- `.env`:
    - `PURCHASE_FLOW_NAMING_MODE=compat_v2`
    - `PURCHASE_DO_GRN_CUTOVER_ENABLED=true`
- HTTP health: `200` (`https://staging.craveva.com/`)
- Disk: ~`3.0G` free on `/`

## Remaining for Production Decision

- Business UAT sign-off on:
    - SO -> Sales DO lifecycle and stock behavior.
    - PO -> GRN lifecycle and stock behavior.
- Normalize deployment from hot-upload state back to Git-first clean deploy path.

## Legacy table cleanup status

- Legacy tables are **NOT deleted yet** (intentional in current phase):
    - `sales_shipments`, `sales_shipment_items`
    - `delivery_orders`, `delivery_order_items`
- Cleanup backlog and one-shot removal plan documented in:
    - `docs/DB_CLEANUP_BACKLOG_SO_DO_PO_GRN.md`
