# Sales & Purchase Flow — End-to-End (Laravel Codebase)

**Purpose:** Describe how Sales Orders (SO), Purchase Orders (PO), warehouses, stock movements, and invoices relate in this application, based on the current codebase.  
**Audience:** Architects, BAs, and engineers onboarding to order → invoice → stock behaviour.  
**Scope:** Core app (`App\Models\*`) plus modules **Purchase** and **Warehouse**. Standalone invoices (from Estimates/Proposals without an Order) follow the same **invoice + stock** rules but are not drawn in the SO diagram below.

**Multi-company:** `Order`, `Invoice`, `PurchaseOrder`, `ClientDetails`, warehouse entities, and `StockMovement` use the **`HasCompany` trait**, which applies **`CompanyScope`** — queries default to `company_id = company()->id`. Always assume tenant isolation unless `withoutGlobalScopes()` is used (e.g. some stock paths).

**GRN / Sales DO tables (2026-04):** Purchase receiving persists to **`grns` / `grn_items`** (`Grn`, `GrnItem`); sales outbound documents use **`sales_dos` / `sales_do_items`** (`SalesDo`, `SalesDoItem`). Tables **`delivery_orders`** / **`sales_shipments`** (và bảng dòng tương ứng) **đã DROP** trên các môi trường triển khai đã xác nhận — xem [`ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md`](ERP_SO_PO_DO_GRN_SCHEMA_AND_LEGACY_MATRIX_VI.md).

---

## 1. Overview

### Sales module (high level)

- **Order** (`App\Models\Order`) is the **sales order**: lines are **`OrderItems`**, linked to **`client_id`** (a `User` row representing the customer).
- **Invoice** (`App\Models\Invoice`) can exist **with** `order_id` (order-origin) or **without** (direct / recurring / estimate conversion).
- **Payment** records tie to `invoice_id` and optionally `order_id`.
- **Warehouse stock deduction on sales** is **not** tied to Order status alone. When the **Warehouse** module is enabled and **`WAREHOUSE_SALES_OUTBOUND_ENABLED`** is true, behaviour depends on **`WAREHOUSE_SALES_OUTBOUND_MODE`** (default in config: **`shipment`**):
    - **`shipment`:** **`SalesDoService::ship`** (status → `shipped`) calls **`SalesShipmentStockService::applyOutboundForShipment`** — outbound from **`sales_dos` / `sales_do_items`** (`quantity_shipped`), not from the Invoice. **`InvoiceWarehouseStockService::syncInvoiceStock`** returns early and does **not** post invoice outbound.
    - **`invoice`:** **`InvoiceObserver`** → **`InvoiceWarehouseStockService::syncInvoiceStock`** posts outbound per **non-draft** invoice lines (goods with `product_id`), with **`invoice_warehouse_stock_postings`** for reversal/idempotency.

### Purchase module (high level)

- **PurchaseOrder** (`Modules\Purchase\Entities\PurchaseOrder`) belongs to a **vendor**, has **`warehouse_id`** (receiving warehouse), lines are **`PurchaseItem`**.
- **Delivery status** on the PO drives legacy **`PurchaseStockAdjustment`** and, when configured, **warehouse inbound** via **`StockMovementService`**.
- **PurchaseBill** is the vendor **bill** linked to a PO; it updates **`billed_status`** on the PO but **does not** post warehouse movements in **`PurchaseBillObserver`**.

### Mental model

| Document                                     | Role                                                                                                                                                                                                                                                                                                                               |
| -------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Order**                                    | Customer commitment / cart frozen as SO; payment + completion drives invoice creation in several flows.                                                                                                                                                                                                                            |
| **Invoice**                                  | Accounts receivable document; **triggers sales outbound stock** (v1) when not draft and not credit note.                                                                                                                                                                                                                           |
| **PurchaseOrder**                            | Commitment to vendor; **delivered** + `warehouse_id` → inbound stock (if `inbound_from_purchase_order_delivered`).                                                                                                                                                                                                                 |
| **GRN** (`App\Models\Grn`, table **`grns`**) | Optional receiving path; observer **`DeliveryOrderObserver`** handles **`Grn` \| `DeliveryOrder`**; when **`status === 'received'`** and **`inbound_from_delivery_order_received`** → **`recordInboundBatch`**; idempotent via **`inbound_stock_applied`**. Must not double-count with PO delivered for the same physical receipt. |
| **PurchaseBill**                             | AP / billing record against PO; accounting visibility, not stock engine.                                                                                                                                                                                                                                                           |

### Note: Sales invoice vs purchase “invoice” — **separate**, not shared

In this codebase, **customer billing (SO path)** and **vendor billing (PO path)** do **not** use the same model or database table.

| Flow              | Document in code | Model / table (concept)                                                                                               |
| ----------------- | ---------------- | --------------------------------------------------------------------------------------------------------------------- |
| **Sales (SO)**    | Customer invoice | `App\Models\Invoice` → **`invoices`**, lines **`invoice_items`**, links to **`order_id`** when created from an order. |
| **Purchase (PO)** | Vendor bill      | `Modules\Purchase\Entities\PurchaseBill` → **`purchase_bills`**, linked by **`purchase_order_id`**.                   |

**Tiếng Việt (dễ hiểu):** “Hóa đơn” bán hàng cho khách và “hóa đơn” nhận từ nhà cung cấp **là hai luồng tách biệt**: bán dùng **`Invoice`**, mua dùng **`PurchaseBill`** — **không** gộp chung một entity. Chỉ trùng từ nghiệp vụ “có hóa đơn”, không trùng code.

---

## 2. Workflow Diagram (Text)

### Sales (typical B2B path)

**Mode `shipment` (default):**

```
Client (User + ClientDetails)
    → Order + OrderItems
    → Sales DO (sales_dos) from order: draft → confirm (reservations) → ship
    → SalesDoService::ship → SalesShipmentStockService::applyOutboundForShipment
         → StockMovement outbound (per sales_do line quantity_shipped, FEFO batches)
    → [Optional / parallel] Invoice from order (AR) — does not post warehouse outbound in this mode
    → Payment on invoice
```

**Mode `invoice` (legacy-style):**

```
    → Invoice created (non-draft) + InvoiceItems (product_id)
    → InvoiceObserver → InvoiceWarehouseStockService::syncInvoiceStock
         → StockMovement outbound (per invoice line, goods only) + postings
```

**Sales return (credit note):** when **`credit_note_items`** are created for returned goods, **`CreditNoteWarehouseStockService`** posts **inbound** (idempotent); deleting the credit note reverses via outbound. See **`SALES_RETURN_CREDIT_NOTE_STOCK_VI.md`**.

**Note:** Confirm DO reserves stock; **ship** is the step that applies physical outbound in shipment mode—not “delivered” alone.

### 2.1 One SO → one Customer Invoice? (Cách 1 vs Cách 2)

**What the codebase does today — aligned with “Cách 1” only (for orders linked via `order_id`):**

| Pattern (nghiệp vụ)                                                                                                          | Supported?                           | Behaviour in code                                                                                                                                                                                                                                                                                                         |
| ---------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Cách 1:** 1 SO → **1** Customer Invoice (dù giao nhiều lần ngoài thực tế, vẫn một hóa đơn tổng ví dụ 10 laptop)            | **Yes**                              | `Order::invoice()` is **`hasOne(Invoice::class, 'order_id')`**. `OrderController::makeOrderInvoice()` **returns the existing invoice** if `$order->invoice` already exists — it **does not** create a second invoice for the same order. New invoice lines are built from **all** `OrderItems` on that order in one pass. |
| **Cách 2:** 1 SO → **nhiều** Invoice (DO #1 → Invoice #1, DO #2 → Invoice #2, giao / thanh toán từng phần theo từng hóa đơn) | **No** (not modelled on the same SO) | There is **no** sales-side `DeliveryOrder` tied to `orders.id` (purchase `DeliveryOrder` is linked to **purchase** PO). No workflow splits one `Order` into multiple `Invoice` rows sharing the same `order_id`.                                                                                                          |

**Partial payment (thanh toán từng phần) without multiple invoices per SO:**  
The app can still use **`Invoice.status`** (e.g. **`partial`**) and multiple **`Payment`** rows against **the same single invoice** — that is **not** the same as “Cách 2” (multiple invoices per SO).

**Workaround if you need multiple billing documents per customer without changing code:**  
Create **standalone** `Invoice` rows **without** `order_id` (manual / recurring / other flows). They will **not** be linked to the SO in the database and **will not** appear as `order->invoice`.

**Tiếng Việt (tóm tắt):** Hiện tại hệ thống **chỉ hỗ trợ rõ ràng kiểu 1 SO → tối đa 1 hóa đơn khách gắn `order_id`**, copy **toàn bộ** dòng SO. **Không** có luồng 1 SO → nhiều invoice theo từng đợt giao (Cách 2). Thanh toán từng phần = **nhiều lần thanh toán trên cùng một invoice**, không phải nhiều invoice cho cùng một SO.

### Purchase / receiving

```
Vendor
    → PurchaseOrder (warehouse_id, items)
    → delivery_status: not_started | in_transaction | delivery_failed | delivered
    → When delivered (+ warehouse_id + product lines):
         • PurchaseStockAdjustment (legacy net qty, per product/warehouse where applicable)
         • If config warehouse.inbound_from_purchase_order_delivered = true:
              StockMovementService::recordInbound (reference PurchaseOrder)

    OR (alternative path, if enabled — do not combine with PO inbound blindly):

    → GRN (grns, type inbound, status received) — model may be Grn; same observer as legacy DO
         • If warehouse.inbound_from_delivery_order_received = true:
              StockMovementService::recordInboundBatch (reference class of header, e.g. Grn)

    → PurchaseBill (optional, against PO)
         • billed_status on PO updated; no stock in PurchaseBillObserver

    → Vendor Credit (PurchaseVendorCredit) — reduces AP vs bill/PO; product lines can post **outbound** stock via VendorCreditWarehouseStockService (see FUNC_LOGIC/PURCHASE_RETURN_VENDOR_CREDIT_STOCK_VI.md)
```

---

## 3. Detailed Flow

### 3.1 SO flow: Client → SO → Invoice (→ warehouse outbound)

1. **Create order** — `OrderController::store` / `saveOrder`
    - Sets `client_id`, totals, `status` (default **`pending`** if not provided).
    - Persists **`OrderItems`** (product, qty, price, taxes, etc.).
    - **`Order`** and **`ClientDetails`** are linked by **`client_id` = `users.id` = `client_details.user_id`**.
    - **`company_id`** on Order comes from **`HasCompany`** / session company when saved.

2. **Order status machine (observed in `OrderController`)**
    - **`pending`** — default on create.
    - **`completed`** — set when payment succeeds or when staff marks complete; often paired with invoice creation.
    - **`failed`** — e.g. failed Stripe/Razorpay (`paymentFailed`).
    - **`canceled`**, **`refunded`** — `changeStatus`; refunded may create **credit note** if conditions met.
    - Editing is blocked for **`completed`**, **`canceled`**, **`refunded`**.

3. **Invoice from order**
    - **`makeOrderInvoice`** / **`InvoiceController::makeInvoice`** pattern: create **`Invoice`** with **`order_id`**, **`client_id`**, totals, **`status`** often **`paid`** when coming from completed payment flow.
    - Copy lines to **`InvoiceItems`** with **`product_id`** where present (important for warehouse sync).
    - **`InvoiceObserver::creating`** sets **`company_id`** from **`company()`**.

4. **Stock deduction (sales) — two modes**

    **`InvoiceObserver::created`** and **`updated`** always call **`syncInvoiceStock`** when not seeding, but **`InvoiceWarehouseStockService::shouldPostOutboundFromInvoice()`** is **false** when **`sales_outbound_mode === 'shipment'`**, so **no invoice outbound** in the default mode.

    **A) Mode `shipment` (default)**
    - Outbound: **`Modules\Purchase\Services\SalesDoService::ship`** → **`SalesShipmentStockService::applyOutboundForShipment`** ( **`outbound_stock_applied`** flag on **`sales_dos`** ).
    - Reservations: **`StockReservationService`** on **confirm** (`SalesDoService::confirm`).
    - Reversal: **`reverseOutboundForShipment`** on cancel (if applied) or **`SalesDoService::reverse`**.

    **B) Mode `invoice`**
    - Service active only if **`sales_outbound_enabled`**, **Warehouse** module enabled, and (when logged in) user has **`warehouse`** in **`user_modules`**.
    - **`shouldPostOutbound`:** not a credit note, **`status !== 'draft'`**.
    - For each **`InvoiceItems`** line with **`type === 'item'`**, **`product_id`**, product not **service**:
        - Resolve **`warehouse_id`** via client default → company default → first active warehouse.
        - **`StockMovementService::recordOutbound`** + **`InvoiceWarehouseStockPosting`**.
    - Delete/draft: reverse postings.

**Implication (shipment mode):** **`sales_do` lines need `product_id` and `quantity_shipped`**, and warehouse on the DO header. **Invoice** AR can exist without moving stock. **Implication (invoice mode):** invoice lines must carry **`product_id`** and client/company warehouse resolution must be valid.

### 3.2 PO flow: Vendor → PO → receiving → PurchaseBill (AP)

1. **Create PO** — `PurchaseOrderController` + **`PurchaseOrderObserver`**
    - **`company_id`**, **`vendor_id`**, **`warehouse_id`** (for stock path), line items as **`PurchaseItem`**.
    - **`purchase_status`** can be **`draft`** if `request()->type == 'draft'`.
    - **`delivery_status`** enum: **`not_started`**, **`in_transaction`**, **`delivery_failed`**, **`delivered`**.

2. **Stock increase when PO is “delivered”**
    - **On create:** if **`delivery_status === 'delivered'`**, lines update **`PurchaseStockAdjustment`** and, when **`warehouse_id`** and **`product_id`** present, **`recordPurchaseOrderInbound`** → **`StockMovementService::recordInbound`** with **`reference_type` = `PurchaseOrder`**, if **`config('warehouse.inbound_from_purchase_order_delivered', true)`**.
    - **On update:** when **`delivery_status`** changes to **`delivered`**, same adjustment + inbound per item.

3. **GRN / inbound receiving alternative**
    - **`DeliveryOrderObserver`** (name kept for history): **`saved`** on **`Grn|DeliveryOrder`** when **`status === 'received'`**, **`type === 'inbound'`**, **`inbound_from_delivery_order_received`** true → **`recordInboundBatch`**, then **`inbound_stock_applied`** on **`grns`** (or legacy **`delivery_orders`** if ever restored). If linked PO is already **`delivered`** and PO inbound flag is on, observer **skips** posting (double-count guard in code).
    - **Risk:** enabling **both** PO-delivered and GRN-received for the same physical receipt can still **double-count** if the guard does not apply — use **one canonical path** per tenant (see `QUY_TRINH_PO_DO_SO_INVOICE_WAREHOUSE_VI.md`).

4. **PurchaseBill (vendor invoice)**
    - **`PurchaseBillObserver::created`**: updates linked **`PurchaseOrder.billed_status`** to **`billed`**, logs history, fires event — **no** call to **`StockMovementService`**.

---

## 4. Database Reference (Main Tables / Columns)

| Table / entity                                                   | Role in flow                                                                                                                                                                                                   |
| ---------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`orders`**                                                     | SO header: `client_id`, `company_id`, `status`, totals, `order_number`, optional `project_id`, `company_address_id`.                                                                                           |
| **`order_items`**                                                | SO lines: `order_id`, `product_id`, `quantity`, `unit_price`, `amount`, `type`, taxes, `unit_id`.                                                                                                              |
| **`invoices`**                                                   | AR header: `client_id`, `company_id`, `order_id` (nullable), `status`, `issue_date`, `due_amount`, `credit_note`, etc.                                                                                         |
| **`invoice_items`**                                              | AR lines: `invoice_id`, `product_id`, `quantity`, `type` — **must be `item` + `product_id` for outbound stock**.                                                                                               |
| **`payments`**                                                   | `invoice_id`, `order_id`, `amount`, `status`, gateway fields.                                                                                                                                                  |
| **`client_details`**                                             | `user_id`, **`company_id`**, **`default_warehouse_id`** (sales outbound resolution).                                                                                                                           |
| **`users`**                                                      | Client is a user row; `client_id` on orders/invoices points here.                                                                                                                                              |
| **`purchase_orders`**                                            | PO header: `company_id`, `vendor_id`, **`warehouse_id`**, **`delivery_status`**, `purchase_status`, totals, `billed_status`.                                                                                   |
| **`purchase_items`**                                             | PO lines: `purchase_order_id`, `product_id`, `quantity`, etc.                                                                                                                                                  |
| **`purchase_bills`**                                             | Vendor bill: `purchase_order_id`, `company_id`, amounts, dates.                                                                                                                                                |
| **`grns`** / **`grn_items`**                                     | Purchase receiving (GRN): `purchase_order_id`, `warehouse_id`, `status`, `inbound_stock_applied`; lines with `quantity_received`. Legacy **`delivery_orders`** removed on cutover DBs — see schema matrix doc. |
| **`stock_movements`**                                            | Ledger: `company_id`, `product_id`, `movement_type`, `warehouse_from_id` / `warehouse_to_id`, `quantity`, `reference_type`, `reference_id`, batch/expiry fields.                                               |
| **`warehouse_product_batches`** / **`warehouse_product_stocks`** | Used by **`StockMovementService`** for quantities (not listed column-by-column here).                                                                                                                          |
| **`sales_dos`** / **`sales_do_items`**                           | Sales delivery / shipment: `order_id`, `warehouse_id`, `status`, `outbound_stock_applied`; shipped qty per line.                                                                                               |
| **`invoice_warehouse_stock_postings`**                           | Used when **sales_outbound_mode = invoice**: links invoice lines to posted outbound qty.                                                                                                                       |

---

## 5. Code Reference

| Area                                               | Key classes                                                                                                                                       |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sales order CRUD / status / invoice from order** | `App\Http\Controllers\OrderController` (`store`, `update`, `changeStatus`, `makeInvoice`, `makeOrderInvoice`, `paymentFailed`, …)                 |
| **Invoice CRUD**                                   | `App\Http\Controllers\InvoiceController` (create/update; stock validation via **`InvoiceWarehouseStockService`** when enabled)                    |
| **Invoice lifecycle / notifications**              | `App\Observers\InvoiceObserver` — calls **`InvoiceWarehouseStockService::syncInvoiceStock`** on create/update (no-op outbound when mode shipment) |
| **Sales outbound (shipment mode)**                 | `Modules\Purchase\Services\SalesDoService` (`ship`, `reverse`, `cancel`), `Modules\Warehouse\Services\SalesShipmentStockService`                  |
| **Sales outbound (invoice mode)**                  | `Modules\Warehouse\Services\InvoiceWarehouseStockService`                                                                                         |
| **Sales return inbound**                           | `Modules\Warehouse\Services\CreditNoteWarehouseStockService`, `App\Observers\CreditNoteItemObserver`, `CreditNoteObserver`                        |
| **Stock engine**                                   | `Modules\Warehouse\Services\StockMovementService` (`recordInbound`, `recordOutbound`, `recordInboundBatch`, transfers)                            |
| **PO lifecycle / inbound from PO**                 | `Modules\Purchase\Observers\PurchaseOrderObserver` (`recordPurchaseOrderInbound`)                                                                 |
| **Inbound from GRN / DO**                          | `Modules\Purchase\Observers\DeliveryOrderObserver` (`Grn` \| `DeliveryOrder`)                                                                     |
| **Vendor bill**                                    | `Modules\Purchase\Observers\PurchaseBillObserver`, `Modules\Purchase\Http\Controllers\PurchaseBillController`                                     |
| **Models**                                         | `App\Models\Order`, `OrderItems`, `Invoice`, `InvoiceItems`, `CreditNotes`, `CreditNoteItem`, `ClientDetails`, `StockMovement`, `Payment`         |
|                                                    | `App\Models\Grn`, `Modules\Purchase\Entities\GrnItem`, `Modules\Purchase\Entities\SalesDo`, `Modules\Purchase\Entities\SalesDoItem`               |
|                                                    | `Modules\Purchase\Entities\PurchaseOrder`, `PurchaseItem`, `PurchaseBill`                                                                         |
|                                                    | `Modules\Warehouse\Entities\Warehouse`, `WarehouseProductStock`, `InvoiceWarehouseStockPosting`                                                   |

**Config (behaviour switches):** `config/warehouse.php` — e.g. `sales_outbound_enabled`, **`sales_outbound_mode`** (`shipment` \| `invoice`), `inbound_from_purchase_order_delivered`, `inbound_from_delivery_order_received`, `allow_negative_stock`.

---

## 6. Related Internal Docs

- Warehouse UAT (E2E + gap): `FUNC_LOGIC/UAT_CHECKLIST_MUA_BAN_KHO_E2E_VI.md` (stub tên cũ: `WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`)
- Warehouse flow (Vietnamese): `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`
- Master guide: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`

---

_Generated from repository analysis. Behaviour may vary by module flags and permissions; verify on staging before sign-off._
