# Multi-Warehouse Module Impact Summary & Relationship Map

## 1. Overview

Transitioning from Single-Warehouse to Multi-Warehouse architecture requires coordinated changes across multiple modules. The core concept shifts from "Product has Stock" to "Warehouse has Stock of Product".

## 2. Module Relationship Map

The following diagram and description explain how data flows between modules in the new Multi-Warehouse system.

### Core Flow: Purchase -> Stock -> Sale

1.  **Purchase Module (Source of Stock)**
    - **Purchase Order (PO)**: Initiates the request.
        - _Relationship_: Specifies the `Target Warehouse` (optional at header, mandatory at line item if split).
    - **Delivery Order (DO)**: The "Gatekeeper" for incoming stock.
        - _Action_: When a PO is converted to a DO (Goods Received Note), the system must capture **which warehouse** received the goods.
        - _Impact_: Triggers a `StockMovement` (Inbound).

2.  **Inventory/Warehouse Module (Storage & Logic)**
    - **Warehouse Model**: Defines physical locations (e.g., Main Warehouse, Frozen Storage, Damaged Goods).
    - **StockMovement Model**: The central ledger for ALL stock changes.
        - _Relationship_: Links `DeliveryOrder` (Source) -> `Warehouse` (Destination).
    - **Inventory State**: Calculated from `StockMovements` or stored in a summary table (`inventory_warehouse`).

3.  **Sale Module (Consumption of Stock)**
    - **Invoice/Order**: Represents demand.
        - _Relationship_: Must specify `Source Warehouse` for fulfillment.
    - **Delivery Note**: The "Gatekeeper" for outgoing stock.
        - _Impact_: Triggers a `StockMovement` (Outbound) from the specific Warehouse.

## 3. Impact Analysis by Module

### A. Purchase Module

- **Current State**: `PurchaseOrder` and `DeliveryOrder` exist but lack explicit Warehouse selection logic in the main flow.
- **Required Changes**:
    - Add `warehouse_id` to `PurchaseOrder` and `DeliveryOrder` tables.
    - Update `PurchaseOrderController::store()` to save warehouse selection.
    - **Critical**: Update `DeliveryOrderController` to generate `StockMovement` records upon creation/approval.

### B. Inventory Module (Core)

- **Current State**: Stock is a simple integer column (`quantity`) on the `products` table.
- **Required Changes**:
    - Create `warehouses` table.
    - Create `inventory_warehouse` pivot table (`product_id`, `warehouse_id`, `quantity`).
    - Migrate existing `products.quantity` to a default "Main Warehouse".
    - Deprecate direct usage of `products.quantity` for stock checks.

### C. Stock Movement (The Bridge)

- **Current State**: `StockMovement` model exists but is underutilized.
- **Required Changes**:
    - Ensure every stock change (Purchase, Sale, Adjustment, Transfer) writes to this table.
    - Fields: `warehouse_from_id`, `warehouse_to_id`, `product_id`, `quantity`, `reference_id` (PO# or Invoice#).

## 4. Implementation Steps (Summary)

1.  **Database**: Create `warehouses` and `inventory_warehouse` tables.
2.  **Data Migration**: Move current stock to Warehouse ID 1.
3.  **Backend Logic**:
    - Update `DeliveryOrder` to increase stock in specific warehouse.
    - Update `Invoice` to decrease stock from specific warehouse.
4.  **Frontend**: Add "Select Warehouse" dropdowns in PO and Invoice creation screens.

## 5. Visual Data Flow

```mermaid
graph TD
    PO[Purchase Order] -->|Convert| DO[Delivery Order]
    DO -->|Trigger| SM_IN[Stock Movement (Inbound)]
    SM_IN -->|Update| INV[Inventory (Warehouse A)]

    SALE[Sale Order] -->|Convert| DN[Delivery Note]
    DN -->|Trigger| SM_OUT[Stock Movement (Outbound)]
    SM_OUT -->|Update| INV

    INV -->|Transfer| SM_TR[Stock Movement (Transfer)]
    SM_TR -->|Update| INV2[Inventory (Warehouse B)]
```
