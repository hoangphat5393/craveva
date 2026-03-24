# MAOLIN Multi-Warehouse Analysis & Implementation Plan (ERP/B2B)

**Input scope analyzed**

- Folder: `PROJECT MAOLIN New/`
- Excel files:
    1. `Craveva customer.xlsx`
    2. `Craveva product.xlsx`
    3. `Quote, unit price, inventory.xlsx`
    4. `Craveva full inventory.xlsx`

**Current ERP modules in scope**

- Client
- Product
- Inventory
- Invoice
- Purchase Order (PO)
- Delivery Order (DO)

---

## 1) Requirement Summary

## 1.1 Warehouse-related fields from client Excel

### Inventory-related sheets

1. `Quote, unit price, inventory.xlsx` -> sheet `產品庫存表`

- Warehouse fields:
    - `庫別` (warehouse_code)
    - `庫別名稱` (warehouse_name)
- Batch/expiry fields:
    - `批號` (batch_number)
    - `有效日期` (expiration_date)
    - `製造日期` (manufacturing_date)
    - `結案碼` (close_status_code)
- Stock movement/snapshot fields:
    - `期初庫存`, `本期入庫`, `本期出庫`, `期末庫存`
    - plus packaging stock fields

2. `Craveva full inventory.xlsx` -> sheet `庫存明細總表`

- Warehouse fields:
    - `庫別名稱` (warehouse_name)
- Batch/expiry fields:
    - `批號`
    - `有效日期(C)`
- Quantity/aging fields:
    - `庫存量`
    - `剩餘有效天數`

### Client sheet

`Craveva customer.xlsx`

- Warehouse-related client preference:
    - `指定庫別名稱` (designated warehouse name)
- Potential segmentation field:
    - `地區別` (region)

### Product / Pricing sheets

- `Craveva product.xlsx`: product master (no direct warehouse split)
- `Quote, unit price, inventory.xlsx` -> `產品價格表`: pricing for SKU (tier/pricing logic input)

## 1.2 What client implicitly expects from multi-warehouse

From these columns, expected behavior is:

- Stock managed **per warehouse**, not global only
- Stock managed **per batch + expiry date**
- Ability to distinguish available stock by warehouse
- Client-level preferred warehouse (`指定庫別名稱`) for fulfillment routing
- Inventory flow can support both:
    - snapshot view (ending stock)
    - movement view (opening/in/out/closing)

## 1.3 Business rules visible vs unclear

- Visible from files:
    - batch + expiry are operationally important
    - warehouse identity is mandatory in inventory data
- **Chưa rõ** (not explicit in docs/files):
    - reservation strategy (when to reserve stock)
    - transfer approval workflow
    - FEFO/FIFO rule enforcement timing
    - whether `結案碼` should block outbound selection

---

## 2) Current System Analysis

## 2.1 Where warehouse concept already exists

1. Warehouse master + stock table

- `warehouses` table exists
- `warehouse_product_stock` exists (`warehouse_id`, `product_id`, `quantity`)

2. Inventory adjustment (Purchase module)

- `purchase_stock_adjustments` has `warehouse_id`, `manufacturing_date`, `expiration_date`
- `PurchaseInventoryController` already accepts `warehouse_id`

3. Delivery order table

- `delivery_orders` has `warehouse_id`
- model `App\Models\DeliveryOrder` includes `warehouse_id`

4. Stock movement table

- `stock_movements` exists (movement_type, warehouse_from/to, batch_number, expiry_date, reference fields)

## 2.2 Where warehouse integration is missing or partial

1. Sales Order module (`orders`)

- Current `orders` model/table has no clear warehouse dimension
- No standard warehouse allocation at order line level in core sales flow

2. Invoice module

- Invoice is financial document, no warehouse linkage in core structure
- Expected: invoice should not own stock, but reference shipment/stock-out result

3. PO/DO linkage maturity

- Purchase side has warehouse in PO/DO flows, but full end-to-end consistency is partial
- Observer logic shows inventory sync on PO delivery status changes, but rule consistency is not fully normalized

4. Import inventory mapping

- Current import fields do not fully include warehouse_code/name + batch mapping strategy
- Import job still has gaps for warehouse mapping from client file format

## 2.3 Data limitations

- Inventory not consistently modeled as `(warehouse + batch + expiry)` source of truth for all modules
- Mixed use of summary stock and adjustment rows can cause reconciliation issues
- Unique/index strategy in some purchase inventory tables may not fully match movement-history design

---

## 3) Gap Analysis

## 3.1 Missing vs requirement

1. Warehouse identity gap

- Requirement: warehouse code/name in input files
- Current: importer lacks robust warehouse resolver and validation pipeline

2. Batch-lot control gap

- Requirement: batch-level inventory with expiry
- Current: fields exist, but not fully integrated across all outbound/inbound module events

3. Cross-module warehouse propagation gap

- Requirement: purchase -> warehouse stock in -> sales/delivery stock out
- Current: purchase flow partial, sales/invoice integration not complete for warehouse-aware fulfillment

4. Client fulfillment preference gap

- Requirement: `指定庫別名稱`
- Current: no first-class client default warehouse mapping in core client table

5. Movement vs snapshot reconciliation gap

- Requirement: both style fields appear in client files
- Current: no unified import contract stating whether source is movement-based or snapshot-based per run

## 3.2 Partially implemented

- Warehouse master and per-warehouse stock table: implemented
- Purchase inventory with warehouse/date/expiry: implemented
- Transfer controller exists: implemented
- Stock movement table exists: implemented but integration maturity is partial

## 3.3 Potentially incorrect / risky in current design

- Stock update triggered by PO status changes may bypass standardized movement pipeline if not centralized
- Duplicate or inconsistent stock state risk if both summary table and adjustments are updated independently

---

## 4) Business Flow (step-by-step)

## 4.1 Purchase -> Warehouse (Stock In)

1. Create PO with target warehouse
2. Confirm receiving (DO inbound / GRN equivalent)
3. For each received line:
    - identify SKU + warehouse + batch + expiry
    - write movement `inbound`
    - upsert warehouse-batch stock snapshot
4. Mark PO/DO receiving status
5. Reconcile totals (document qty vs stock qty)

## 4.2 Sales -> Delivery -> Invoice (Stock Out)

1. Create sales order
2. Allocate warehouse (default by client warehouse preference or rule engine)
3. Reserve stock (optional but recommended)
4. Create outbound delivery order
5. At delivery confirmation:
    - consume stock by warehouse + batch (FEFO/FIFO rule)
    - write movement `outbound`
    - update warehouse stock snapshot
6. Create invoice from delivered quantities

## 4.3 Warehouse transfer

1. Create transfer request (from_warehouse -> to_warehouse)
2. Validate source stock availability (warehouse + batch)
3. Confirm transfer:
    - movement `transfer-out` at source
    - movement `transfer-in` at destination
    - snapshot updates both sides
4. Audit with transfer reference id

## 4.4 Stock reservation

1. On order confirmation, reserve stock against warehouse/batch
2. Reserved qty excluded from available qty
3. On delivery confirm, convert reserved -> consumed
4. On order cancel, release reservation

---

## 5) Integration Plan

## 5.1 Inventory module

- Add import contract supporting:
    - `warehouse_code` / `warehouse_name`
    - `batch_number`
    - `expiration_date`
    - snapshot qty or movement qty mode
- Build warehouse resolver and strict validation
- Centralize stock update through movement service

## 5.2 Purchase Order module

- Ensure PO has explicit warehouse assignment (header/line strategy)
- Receiving must always create movement records
- Prevent direct stock mutation outside movement service

## 5.3 Delivery Order module

- Separate inbound DO (purchase receiving) and outbound DO (sales delivery) semantics
- Outbound DO must reference allocation/reservation and batch picking
- DO confirmation triggers stock-out movement

## 5.4 Invoice module

- Invoice should consume delivered data, not mutate stock directly
- Link invoice lines to delivery/shipment references for traceability

## 5.5 Client/Product/Tier Pricing side integration

- Client:
    - map `指定庫別名稱` to `default_warehouse_id`
- Product:
    - keep SKU as primary key for warehouse stock relation
- Tier Pricing:
    - price decision can consider warehouse (optional future extension)

---

## 6) Implementation Roadmap

## Phase 1: Database changes

### What to do

1. Add/normalize warehouse reference fields where missing:

- client default warehouse reference
- warehouse/batch keys in inventory storage model

2. Add indexes:

- `(warehouse_id, product_id)`
- `(warehouse_id, product_id, batch_number, expiration_date)`

3. Define reservation table (if reservation required)

### Dependencies

- final key strategy (SKU + warehouse + batch)
- warehouse master data quality

### Risks

- migration on large inventory tables
- backfill ambiguity for historical rows without warehouse

## Phase 2: Core logic (inventory)

### What to do

1. Implement centralized stock movement service
2. Standardize update sequence: movement -> snapshot projection
3. Support import modes:

- snapshot mode
- movement mode

### Dependencies

- approved business rules (FEFO/FIFO, negative stock policy)

### Risks

- double-counting if legacy paths still write stock directly

## Phase 3: Module integration

### What to do

1. Integrate Purchase receiving with movement service
2. Integrate Delivery outbound + reservation
3. Ensure Invoice reads fulfillment result only

### Dependencies

- finalized DO semantics (inbound/outbound)

### Risks

- cross-module regression if event flow is not isolated

## Phase 4: UI / reporting

### What to do

1. Warehouse-aware stock screens (by warehouse, by batch, near-expiry)
2. Reconciliation reports:

- movement ledger
- snapshot balance
- variance check

3. Import/export templates aligned with client Excel

### Dependencies

- stable data model from phase 1+2

### Risks

- report performance on large datasets without proper indexing

---

## Excel-to-ERP module mapping summary

| Excel source                  | Main purpose                          | ERP module mapping              |
| ----------------------------- | ------------------------------------- | ------------------------------- |
| `Craveva customer.xlsx`       | client master + preferred warehouse   | Client (+ warehouse preference) |
| `Craveva product.xlsx`        | product master                        | Product                         |
| `Quote...xlsx / 產品價格表`   | price by SKU                          | Product + Tier Pricing          |
| `Quote...xlsx / 產品庫存表`   | warehouse/batch stock + period values | Inventory + Warehouse           |
| `Craveva full inventory.xlsx` | batch stock snapshot by warehouse     | Inventory + Warehouse           |

---

## Tracking notes

- This note is intentionally focused on multi-warehouse requirement and implementation planning only.
- Items marked **Chưa rõ** require workshop confirmation with client before phase lock.

**Non-technical B2B overview (PO / DO / Invoice, flows):** see `FUNC_LOGIC/B2B_ERP_PO_DO_INVOICE_GUIDE.md`.

**Hướng dẫn thao tác trên giao diện (URL, từng màn):** see `FUNC_LOGIC/MULTI_WAREHOUSE_UI_OPERATIONS_GUIDE.md`.

---

## Implementation Status

### Phase 1 (DB + Core Logic) - Done

#### DB changes implemented

- Added `warehouse_product_batches` table (warehouse + product + batch + expiry + quantity + reserved_quantity).
- Added `stock_reservations` table for reservation lifecycle (`active`, `released`, `consumed`).
- Added `client_details.default_warehouse_id` (nullable FK -> `warehouses.id`) for client preferred warehouse mapping.
- Added indexes on `stock_movements` for movement timeline and warehouse/batch lookups.

#### Core logic implemented

- Added centralized service: `Modules/Warehouse/Services/StockMovementService.php`.
- Service supports movement types:
    - `inbound`
    - `outbound`
    - `transfer`
- Service enforces no-negative-stock by default (configurable via `warehouse.allow_negative_stock` / env `WAREHOUSE_ALLOW_NEGATIVE_STOCK`).
- Outbound pick strategy defaults to FEFO ordering by `expiration_date` (null expiry is processed last).
- Backward compatibility maintained by syncing legacy summary table `warehouse_product_stock` from batch-level snapshot.

#### Tests implemented

- Added `tests/Unit/StockMovementServiceTest.php`:
    - block negative stock when policy disabled
    - allow outbound when override enables negative
    - FEFO ordering behavior

#### Outstanding for next phases

- See Phase 2–3 below.

---

### Phase 2 (Core logic — centralize inventory movements) - Done

#### What changed

- `StockMovementService` registered as singleton (`WarehouseServiceProvider`).
- **Purchase PO receiving (delivered):** `PurchaseOrderObserver` no longer increments `warehouse_product_stock` directly; it calls `recordInbound()` so stock follows **batch snapshot + movement ledger** (batch/expiry null until PO lines carry them — **Chưa rõ / default an toàn**).
- **Manual warehouse UI:** `WarehouseStockController@store` uses `recordInbound` / `recordOutbound` (adjustment uses optional `action` add/remove).
- **Transfer UI:** `WarehouseTransferController@store` uses `recordTransfer()` (outbound + inbound legs only; removed duplicate third `transfer` row in service to avoid triple-counting in `stock_movements`).
- **Data safety:** migration `2026_03_23_130000_backfill_warehouse_product_batches_from_legacy_stock` seeds one **null batch / null expiry** batch row from legacy `warehouse_product_stock` where missing, so turning on inbound via service does not “drop” old totals.

#### DB migrations (Phase 2)

- `Modules/Warehouse/Database/Migrations/2026_03_23_130000_backfill_warehouse_product_batches_from_legacy_stock.php`

#### Tests

- Existing unit tests for `StockMovementService` (FEFO sort, negative stock guard) — re-run on change.

#### Explicitly not in Phase 2 (billing unchanged)

- **Invoice** module: no stock mutation; no change to invoice/billing behavior.

#### Outstanding for Phase 3+

- See Phase 3 below; Phase 4 = UI/reporting.

---

### Phase 3 (Module integration) - Done

#### Purchase + DO

- **PO inbound (legacy, default on):** `warehouse.inbound_from_purchase_order_delivered` (env `WAREHOUSE_INBOUND_FROM_PO_DELIVERED`, default `true`). `PurchaseOrderObserver` respects this flag.
- **DO inbound (opt-in):** `warehouse.inbound_from_delivery_order_received` (env `WAREHOUSE_INBOUND_FROM_DO_RECEIVED`, default `false`). When `true`, `DeliveryOrderObserver` posts inbound when DO `type=inbound` and `status=received`, using `quantity_received` lines, **one transaction** via `StockMovementService::recordInboundBatch()`.
- **Idempotency:** `delivery_orders.inbound_stock_applied` prevents double posting on re-save.
- **Warehouse on DO:** `DeliveryOrderController` sets `warehouse_id` from request or falls back to linked PO `warehouse_id`; persists `type` (default `inbound`).
- **Movement ledger:** `stock_movements.delivery_order_item_id` populated for DO-driven inbound lines.

#### Double-counting (Chưa rõ / cần vận hành)

- Nếu **cả** PO delivered **và** DO received đều bật nhập kho cho **cùng một lần nhận vật lý**, tồn có thể **cộng đôi**. Mặc định an toàn: chỉ PO; bật DO khi dùng DO làm phiếu nhận chính và **tắt** nhập từ PO (`WAREHOUSE_INBOUND_FROM_PO_DELIVERED=false`).

#### Reservation (service layer)

- `StockReservationService` (`reserve` / `release`) registered as singleton; cập nhật `warehouse_product_batches.reserved_quantity` + bảng `stock_reservations`. **Chưa** gắn UI Sales Order (sẵn sàng cho phase sau).

#### Invoice (fulfillment traceability only)

- Migration: `invoice_items.delivery_order_item_id` nullable + index (liên kết dòng hóa đơn ↔ dòng DO để đối soát; **không** trừ kho qua Invoice).
- `InvoiceItems::deliveryOrderItem()` relation added.
- **Không** thay đổi `InvoiceObserver` / luồng billing.

#### DB migrations (Phase 3)

- `database/migrations/2026_03_24_100000_add_inbound_stock_applied_to_delivery_orders_table.php`
- `database/migrations/2026_03_24_100100_add_delivery_order_item_id_to_invoice_items_table.php`

#### Tests

- `tests/Unit/WarehousePhase3ConfigTest.php` (config defaults + service registration + `recordInboundBatch`).

#### Outstanding for Phase 4+

- UI form DO: thêm chọn `warehouse_id` / `type` nếu cần (hiện fallback PO).
- **FEFO** khi thiếu expiry — **Chưa rõ** nghiệp vụ.
