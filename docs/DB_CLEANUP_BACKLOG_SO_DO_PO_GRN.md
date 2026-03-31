# DB Cleanup Backlog - SO/DO + PO/GRN

## Current answer (short)

- Sales flow (`SO -> DO`) runtime hiện tại: **`sales_dos` + `sales_do_items`**
- Purchase flow (`PO -> GRN`) runtime hiện tại: **`grns` + `grn_items`**
- Bảng legacy: **chưa xóa** (theo đúng chiến lược bridge/cutover an toàn).

## Canonical flow -> table mapping (current)

### Sales side

- `SO` document: `orders` (lines: `order_items`)
- `DO` document (Sales DO): `sales_dos` (lines: `sales_do_items`)
- `Invoice` document (sales): `invoices` (lines: `invoice_items`)

### Purchase side

- `PO` document: `purchase_orders` (lines: `purchase_items`)
- `GRN` document: `grns` (lines: `grn_items`)
- `PO Invoice/Bill` document: `purchase_bills`
    - payment/link tables around bill flow: `purchase_payment_bills`, `purchase_bill_histories`, `purchase_payment_histories`

## Evidence snapshot (staging)

- Sales DO migration dry-run:
    - `source.shipments_count = 1`
    - `target.headers_migrated_count = 1`
    - `pending.shipments_count = 0`
- GRN migration dry-run:
    - `source.headers_count = 3`
    - `target.headers_migrated_count = 3`
    - `pending.headers_count = 0`

=> Điều này xác nhận bảng nguồn legacy vẫn tồn tại, nhưng dữ liệu đã migrate sang bảng đích mới và không còn pending.

## Tables not deleted yet (legacy candidates)

### A) Legacy headers/items for one-time cleanup (candidate drop in Phase 5)

- `sales_shipments`
- `sales_shipment_items`
- `delivery_orders`
- `delivery_order_items`

### B) Transitional mapping columns (candidate drop after freeze window)

- `sales_dos.legacy_sales_shipment_id`
- `sales_do_items.legacy_sales_shipment_item_id`
- `grns.legacy_delivery_order_id`
- `grn_items.legacy_delivery_order_item_id`

## Important: not cleanup candidates (still business/core)

- `orders`, `order_items` (sales order core)
- `purchase_orders` (purchase core)
- stock tables (`stock_movements`, `warehouse_*`) and billing tables

## Safe delete preconditions (must pass before one-shot cleanup)

1. UAT sign-off completed for both flows.
2. Production cutover stable for a defined soak period.
3. Rollback policy switched from legacy tables to DB backup-only strategy.
4. Command/report checks confirm:
    - Sales DO pending = `0`
    - GRN pending = `0`
5. No code path references legacy tables in app runtime and jobs.

## Suggested one-shot cleanup order (future)

1. Freeze write window.
2. Final backup + final reconcile reports.
3. Deploy code removing runtime fallback to legacy tables.
4. Migration drop legacy FKs/tables (`*_items` first, then headers).
5. Remove legacy mapping columns from new tables.
6. Post-drop smoke + rollback rehearsal from backup.
