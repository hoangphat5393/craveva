# Warehouse Module — UAT Checklist & Gap Report (Miaolin)

## Tab Title (Google Doc)

`Warehouse Module — UAT Checklist & Gap Report (Miaolin)`

---

## 1) UAT Checklist (Warehouse Module + Miaolin Flows)

### A. Preconditions / Setup

- [ ] Logged in with edit access (Google Doc)
- [ ] Warehouse module enabled and migrations applied
- [ ] Permissions assigned to UAT user(s)
  - [ ] warehouse_view
  - [ ] warehouse_add
  - [ ] warehouse_edit
  - [ ] warehouse_delete
  - [ ] warehouse_stock_view
  - [ ] warehouse_stock_add
  - [ ] warehouse_transfer_add
  - [ ] warehouse_movement_view
- [ ] Config flags confirmed
  - [ ] `WAREHOUSE_ALLOW_NEGATIVE_STOCK=false` (recommended for Miaolin)
  - [ ] Enable exactly ONE inbound source to avoid double counting
    - [ ] Option 1: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` and `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=false`
    - [ ] Option 2: `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=false` and `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`
- [ ] Test data ready
  - [ ] Warehouses: at least 2 (Warehouse A + Warehouse B)
  - [ ] One warehouse set as default
  - [ ] Products: at least 3 (each has SKU)
  - [ ] At least 1 product has batch/expiry data (if used in your process)

### B. Warehouse Master (CRUD + Import + Bulk Actions)

#### Create

- [ ] Create warehouse with Name, Code (optional), Status=Active, Is Default=true
  - Expected: saved successfully; only one default per company
- [ ] Create a second warehouse Is Default=false
  - Expected: saved successfully; visible in warehouse list

#### Edit

- [ ] Edit warehouse Name and Status
  - Expected: changes saved; list updates correctly

#### Delete Guard (Data Protection)

- [ ] Attempt delete warehouse that has existing stock / movements / reservations
  - Expected: blocked with clear message; no deletion occurs
- [ ] Attempt delete empty warehouse (no stock, movements, reservations)
  - Expected: allowed

#### Bulk Quick Actions

- [ ] Select multiple warehouses → change status Active/Inactive
  - Expected: all selected rows updated correctly
- [ ] Select multiple warehouses → delete
  - Expected: blocked if any selected warehouse has stock/movements/reservations; otherwise deleted

#### Import Warehouses (Excel)

- [ ] Import valid file with expected columns
  - Expected: creates/updates by Company + Code; status/default applied as per file
- [ ] Import file with duplicate Code rows
  - Expected: last row wins; default warehouse result is correct

### C. Stock Adjustment (Manual In/Out)

#### Inbound Adjustment

- [ ] Add Stock: Warehouse A + Product P + Quantity 10 + Reason
  - Expected:
    - stock summary increases to 10
    - movement ledger records “Inbound”
    - batch row created/updated (batch may be null if UI doesn’t capture batch)

#### Outbound Adjustment (Sufficient Stock)

- [ ] Remove Stock: Warehouse A + Product P + Quantity 3 + Reason
  - Expected:
    - stock summary decreases to 7
    - movement ledger records “Outbound”
    - batch reduction follows FEFO (earliest expiry first) if expiries exist

#### Outbound Adjustment (Insufficient Stock, Negative Disabled)

- [ ] Remove Stock: Warehouse A + Product P + Quantity 9999
  - Expected:
    - validation error: insufficient available quantity
    - no stock changes saved
    - no movement created

#### Negative Stock Enabled (Policy Test)

- [ ] Temporarily set `WAREHOUSE_ALLOW_NEGATIVE_STOCK=true` (UAT only) and repeat insufficient outbound test
  - Expected:
    - outbound allowed
    - stock may go negative (confirm this is acceptable before production)

### D. Stock Transfer (Warehouse-to-Warehouse)

#### Valid Transfer

- [ ] Transfer: From Warehouse A → Warehouse B, Product P, Quantity 2, Description
  - Expected:
    - Warehouse A stock decreases by 2
    - Warehouse B stock increases by 2
    - ledger shows transfer movements with correct from/to warehouses
    - operation is atomic (no partial updates)

#### Invalid Transfer

- [ ] Transfer with same From and To warehouse
  - Expected: validation error; no movements created

### E. Stock Movements Ledger (Auditability)

- [ ] Open Movements screen
- [ ] Filter by Warehouse A
  - Expected: only movements tied to Warehouse A appear
- [ ] Filter by movement type (Inbound / Outbound / Transfer / Adjustment)
  - Expected: only selected type appears
- [ ] Search by product name or SKU
  - Expected: relevant movements returned
- [ ] Verify “Reference Type/ID” for key sources
  - [ ] Manual adjustment
  - [ ] Purchase inbound (PO delivered or DO received)
  - [ ] Purchase inventory absolute sync

### F. Purchase → Warehouse Inbound Integration (Choose One Canonical Flow)

#### Option 1: PO Delivered → Inbound (default behavior)

- [ ] Create Purchase Order with `warehouse_id` and items
- [ ] Change `delivery_status` to `delivered`
  - Expected: inbound movements created per item; stock increases accordingly

#### Option 2: Delivery Order Received → Inbound (if enabled)

- [ ] Create Delivery Order (Inbound type) with items and `warehouse_id`
- [ ] Set status to `received`
  - Expected: inbound movements created; DO flagged to prevent duplicate inbound

#### Double Count Prevention

- [ ] Confirm you do not get inbound twice for the same goods
  - Expected: only one inbound event is enabled (PO delivered OR DO received)

### G. Purchase Inventory → Warehouse Absolute Sync

- [ ] In Purchase Inventory, set absolute stock quantity for Warehouse A + Product P to a target (e.g., 25)
  - Expected:
    - system calculates delta
    - inbound/outbound movements posted to reach target exactly
    - movement reference points to PurchaseInventory (type + id)

### H. Permissions / Security / Multi-Tenant

- [ ] User without `warehouse_view` cannot access warehouse index
- [ ] User without `warehouse_stock_add` cannot add stock (modal and store)
- [ ] User without `warehouse_transfer_add` cannot transfer stock
- [ ] Company scoping: user from Company A cannot see Company B warehouses/stocks/movements
- [ ] Input validation hardening tests
  - [ ] negative quantity
  - [ ] non-numeric quantity
  - [ ] excessive decimals (precision)
  - [ ] invalid warehouse/product IDs
  - Expected: server-side validation blocks and shows clean error (no movement created)

### I. UX / Accessibility / Regression Smoke

- [ ] Keyboard navigation works for modal forms (tab order, focus)
- [ ] Errors are readable and field-specific
- [ ] Mobile responsive check: stock list and modals usable
- [ ] Movements list performance check at scale (e.g., 1k+ rows): filters + pagination responsive

---

## 2) What’s Currently Missing (Gap Report)

### Critical (Release Blockers for Miaolin “Inventory-Aware Sales”)

- Missing: sales outbound integration (Order/Invoice/Payment completion does not reduce warehouse stock via StockMovementService)
  - Impact:
    - warehouse quantities do not decrement on sales
    - cannot reliably enforce “cannot sell if insufficient stock”
    - movements ledger is incomplete for sales
  - Required decisions:
    - define outbound trigger (invoice created, invoice paid, delivery confirmed, etc.)
    - define warehouse selection rule (client default warehouse vs per-line warehouse)
  - Required implementation:
    - record outbound movements on the chosen trigger
    - add reversal flows (cancel/return/refund) to keep stock consistent

### High

- Risk: double-count inbound if both inbound flags are enabled
  - `WAREHOUSE_INBOUND_FROM_PO_DELIVERED=true` and `WAREHOUSE_INBOUND_FROM_DO_RECEIVED=true`
  - Impact: stock inflated and reporting incorrect
  - Fix: enable only one canonical inbound event in production

- Legacy behavior: payment observer adjusts PurchaseStockAdjustment without warehouse context
  - Impact: desync with warehouse movement ledger and multi-warehouse accuracy
  - Fix: remove stock mutation from payment events or replace with movement-based logic tied to fulfillment (not payment)

### Medium

- UI gap: batch/expiry input not captured in stock adjustment/transfer UI (service supports expiry-driven FEFO)
  - Impact: FEFO benefit limited unless batch/expiry data is imported/managed elsewhere

- Optional: manual ordering requires `warehouses.sort_order` column if UI needs drag-to-reorder
  - Impact: ordering feature cannot persist without column + UI wiring

### Low / Nice-to-have

- Movements ledger references are text-only (no deep links to source documents like PO/DO)
- API route stub exists; either formalize minimal warehouse APIs or remove if unused

---

## 3) Acceptance Criteria for Sign-Off

- Inbound (PO delivered OR DO received) posts a single set of inbound movements per receiving event (no double count)
- Manual adjustments create correct movements, update summary stock, and enforce negative-stock policy
- Transfers are atomic and ledger reflects correct from/to warehouses
- Movements ledger filters and search work and display correct references
- Delete is blocked when warehouse has stock/movements/reservations
- Permissions correctly gate access and actions per role/user
- Miaolin readiness: sales outbound is implemented and reliably reduces stock at the correct warehouse

