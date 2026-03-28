# Sales & Purchase Flow — End-to-End (Laravel Codebase)

**Purpose:** Describe how Sales Orders (SO), Purchase Orders (PO), warehouses, stock movements, and invoices relate in this application, based on the current codebase.  
**Audience:** Architects, BAs, and engineers onboarding to order → invoice → stock behaviour.  
**Scope:** Core app (`App\Models\*`) plus modules **Purchase** and **Warehouse**. Standalone invoices (from Estimates/Proposals without an Order) follow the same **invoice + stock** rules but are not drawn in the SO diagram below.

**Multi-company:** `Order`, `Invoice`, `PurchaseOrder`, `ClientDetails`, warehouse entities, and `StockMovement` use the **`HasCompany` trait**, which applies **`CompanyScope`** — queries default to `company_id = company()->id`. Always assume tenant isolation unless `withoutGlobalScopes()` is used (e.g. some stock paths).

---

## 1. Overview

### Sales module (high level)

- **Order** (`App\Models\Order`) is the **sales order**: lines are **`OrderItems`**, linked to **`client_id`** (a `User` row representing the customer).
- **Invoice** (`App\Models\Invoice`) can exist **with** `order_id` (order-origin) or **without** (direct / recurring / estimate conversion).
- **Payment** records tie to `invoice_id` and optionally `order_id`.
- **Warehouse stock deduction on sales** is **not** tied to Order status. When the **Warehouse** module is enabled and **`WAREHOUSE_SALES_OUTBOUND_ENABLED`** is true, **`InvoiceWarehouseStockService`** posts **outbound** stock from **`Invoice`** lines (after invoice create/update), not from the Order itself.

### Purchase module (high level)

- **PurchaseOrder** (`Modules\Purchase\Entities\PurchaseOrder`) belongs to a **vendor**, has **`warehouse_id`** (receiving warehouse), lines are **`PurchaseItem`**.
- **Delivery status** on the PO drives legacy **`PurchaseStockAdjustment`** and, when configured, **warehouse inbound** via **`StockMovementService`**.
- **PurchaseBill** is the vendor **bill** linked to a PO; it updates **`billed_status`** on the PO but **does not** post warehouse movements in **`PurchaseBillObserver`**.

### Mental model

| Document                    | Role                                                                                                         |
| --------------------------- | ------------------------------------------------------------------------------------------------------------ |
| **Order**                   | Customer commitment / cart frozen as SO; payment + completion drives invoice creation in several flows.      |
| **Invoice**                 | Accounts receivable document; **triggers sales outbound stock** (v1) when not draft and not credit note.     |
| **PurchaseOrder**           | Commitment to vendor; **delivered** + `warehouse_id` → inbound stock (if flag on).                           |
| **DeliveryOrder** (inbound) | Optional receiving path; **received** → inbound batch (if flag on); must not double-count with PO delivered. |
| **PurchaseBill**            | AP / billing record against PO; accounting visibility, not stock engine.                                     |

### Note: Sales invoice vs purchase “invoice” — **separate**, not shared

In this codebase, **customer billing (SO path)** and **vendor billing (PO path)** do **not** use the same model or database table.

| Flow              | Document in code | Model / table (concept)                                                                                               |
| ----------------- | ---------------- | --------------------------------------------------------------------------------------------------------------------- |
| **Sales (SO)**    | Customer invoice | `App\Models\Invoice` → **`invoices`**, lines **`invoice_items`**, links to **`order_id`** when created from an order. |
| **Purchase (PO)** | Vendor bill      | `Modules\Purchase\Entities\PurchaseBill` → **purchase bill** tables, linked by **`purchase_order_id`**.               |

**Tiếng Việt (dễ hiểu):** “Hóa đơn” bán hàng cho khách và “hóa đơn” nhận từ nhà cung cấp **là hai luồng tách biệt**: bán dùng **`Invoice`**, mua dùng **`PurchaseBill`** — **không** gộp chung một entity. Chỉ trùng từ nghiệp vụ “có hóa đơn”, không trùng code.

---

## 2. Workflow Diagram (Text)

### Sales (typical B2B path)

```
Client (User + ClientDetails)
    → Order created (status e.g. pending) + OrderItems
    → [Optional: payment gateways / offline payment]
    → Order status → completed (and/or explicit makeInvoice / changeStatus)
    → Invoice created (order_id set) + InvoiceItems (product_id copied from order lines where applicable)
    → Payment (invoice_id, optionally order_id)
    → [Warehouse] InvoiceObserver → InvoiceWarehouseStockService::syncInvoiceStock
         → StockMovement outbound (per invoice line, goods only)
```

**Note:** There is **no separate “picking” entity** in code between Order and Invoice. Physically picking can be a process outside the app; stock is reduced when the **invoice** is synced per rules below.

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

    → DeliveryOrder (inbound, status received)
         • If warehouse.inbound_from_delivery_order_received = true:
              StockMovementService::recordInboundBatch (reference DeliveryOrder)

    → PurchaseBill (optional, against PO)
         • billed_status on PO updated; no stock in PurchaseBillObserver
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

4. **Stock deduction (sales)**
    - **`InvoiceObserver::created`** and **`updated`** call **`InvoiceWarehouseStockService::syncInvoiceStock`** (unless seeding).
    - Service is active only if:
        - `config('warehouse.sales_outbound_enabled')` is true,
        - **Warehouse** module enabled, and user modules include **`warehouse`**.
    - **`shouldPostOutbound`:** not a credit note, **`status !== 'draft'`**.
    - For each **`InvoiceItems`** line with **`type === 'item'`**, **`product_id`** set, and product not a **service**:
        - Resolve **`warehouse_id`**: **`ClientDetails.default_warehouse_id`** (if valid for company) → else company **default warehouse** → else first active warehouse → else exception.
        - **`StockMovementService::recordOutbound`** (FEFO from batches when data exists).
        - Record **`InvoiceWarehouseStockPosting`** for idempotent reverse/replay.
    - On invoice delete / draft transitions, postings are reversed via inbound reversal movements (see service).

**Implication:** For Miaolin-style “inventory-aware sales”, **invoice line `product_id` and client default warehouse** must be correct; **Order** completion alone does not move stock unless an invoice is created and synced.

### 3.2 PO flow: Vendor → PO → receiving → PurchaseBill (AP)

1. **Create PO** — `PurchaseOrderController` + **`PurchaseOrderObserver`**
    - **`company_id`**, **`vendor_id`**, **`warehouse_id`** (for stock path), line items as **`PurchaseItem`**.
    - **`purchase_status`** can be **`draft`** if `request()->type == 'draft'`.
    - **`delivery_status`** enum: **`not_started`**, **`in_transaction`**, **`delivery_failed`**, **`delivered`**.

2. **Stock increase when PO is “delivered”**
    - **On create:** if **`delivery_status === 'delivered'`**, lines update **`PurchaseStockAdjustment`** and, when **`warehouse_id`** and **`product_id`** present, **`recordPurchaseOrderInbound`** → **`StockMovementService::recordInbound`** with **`reference_type` = `PurchaseOrder`**, if **`config('warehouse.inbound_from_purchase_order_delivered', true)`**.
    - **On update:** when **`delivery_status`** changes to **`delivered`**, same adjustment + inbound per item.

3. **Delivery Order (inbound) alternative**
    - **`DeliveryOrderObserver`**: when **`status === 'received'`**, **`type === 'inbound'`**, and **`warehouse.inbound_from_delivery_order_received`** is true, posts **`recordInboundBatch`** and sets **`inbound_stock_applied`** to avoid duplicates.
    - **Risk:** enabling **both** PO-delivered inbound and DO-received inbound for the same physical receipt can **double-count** — align with `config/warehouse.php` and UAT checklist.

4. **PurchaseBill (vendor invoice)**
    - **`PurchaseBillObserver::created`**: updates linked **`PurchaseOrder.billed_status`** to **`billed`**, logs history, fires event — **no** call to **`StockMovementService`**.

---

## 4. Database Reference (Main Tables / Columns)

| Table / entity                                                   | Role in flow                                                                                                                                                     |
| ---------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`orders`**                                                     | SO header: `client_id`, `company_id`, `status`, totals, `order_number`, optional `project_id`, `company_address_id`.                                             |
| **`order_items`**                                                | SO lines: `order_id`, `product_id`, `quantity`, `unit_price`, `amount`, `type`, taxes, `unit_id`.                                                                |
| **`invoices`**                                                   | AR header: `client_id`, `company_id`, `order_id` (nullable), `status`, `issue_date`, `due_amount`, `credit_note`, etc.                                           |
| **`invoice_items`**                                              | AR lines: `invoice_id`, `product_id`, `quantity`, `type` — **must be `item` + `product_id` for outbound stock**.                                                 |
| **`payments`**                                                   | `invoice_id`, `order_id`, `amount`, `status`, gateway fields.                                                                                                    |
| **`client_details`**                                             | `user_id`, **`company_id`**, **`default_warehouse_id`** (sales outbound resolution).                                                                             |
| **`users`**                                                      | Client is a user row; `client_id` on orders/invoices points here.                                                                                                |
| **`purchase_orders`**                                            | PO header: `company_id`, `vendor_id`, **`warehouse_id`**, **`delivery_status`**, `purchase_status`, totals, `billed_status`.                                     |
| **`purchase_items`**                                             | PO lines: `purchase_order_id`, `product_id`, `quantity`, etc.                                                                                                    |
| **`purchase_bills`**                                             | Vendor bill: `purchase_order_id`, `company_id`, amounts, dates.                                                                                                  |
| **`delivery_orders`**                                            | Receiving doc: `warehouse_id`, `status`, `inbound_stock_applied`, link to PO where applicable.                                                                   |
| **`stock_movements`**                                            | Ledger: `company_id`, `product_id`, `movement_type`, `warehouse_from_id` / `warehouse_to_id`, `quantity`, `reference_type`, `reference_id`, batch/expiry fields. |
| **`warehouse_product_batches`** / **`warehouse_product_stocks`** | Used by **`StockMovementService`** for quantities (not listed column-by-column here).                                                                            |
| **`invoice_warehouse_stock_postings`**                           | Links invoice lines to posted outbound qty for reversal/idempotency.                                                                                             |

---

## 5. Code Reference

| Area                                               | Key classes                                                                                                                       |
| -------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| **Sales order CRUD / status / invoice from order** | `App\Http\Controllers\OrderController` (`store`, `update`, `changeStatus`, `makeInvoice`, `makeOrderInvoice`, `paymentFailed`, …) |
| **Invoice CRUD**                                   | `App\Http\Controllers\InvoiceController` (create/update; stock validation via **`InvoiceWarehouseStockService`** when enabled)    |
| **Invoice lifecycle / notifications**              | `App\Observers\InvoiceObserver` — calls **`InvoiceWarehouseStockService::syncInvoiceStock`** on create/update                     |
| **Sales outbound stock**                           | `Modules\Warehouse\Services\InvoiceWarehouseStockService`                                                                         |
| **Stock engine**                                   | `Modules\Warehouse\Services\StockMovementService` (`recordInbound`, `recordOutbound`, `recordInboundBatch`, transfers)            |
| **PO lifecycle / inbound from PO**                 | `Modules\Purchase\Observers\PurchaseOrderObserver` (`recordPurchaseOrderInbound`)                                                 |
| **Inbound from DO**                                | `Modules\Purchase\Observers\DeliveryOrderObserver`                                                                                |
| **Vendor bill**                                    | `Modules\Purchase\Observers\PurchaseBillObserver`, `Modules\Purchase\Http\Controllers\PurchaseBillController`                     |
| **Models**                                         | `App\Models\Order`, `OrderItems`, `Invoice`, `InvoiceItems`, `ClientDetails`, `StockMovement`, `Payment`                          |
|                                                    | `Modules\Purchase\Entities\PurchaseOrder`, `PurchaseItem`, `PurchaseBill`                                                         |
|                                                    | `Modules\Warehouse\Entities\Warehouse`, `WarehouseProductStock`, `InvoiceWarehouseStockPosting`                                   |

**Config (behaviour switches):** `config/warehouse.php` — e.g. `sales_outbound_enabled`, `inbound_from_purchase_order_delivered`, `inbound_from_delivery_order_received`, `allow_negative_stock`.

---

## 6. Related Internal Docs

- Warehouse UAT and Miaolin gaps: `FUNC_LOGIC/WAREHOUSE_UAT_CHECKLIST_MIAOLIN.md`
- Warehouse flow (Vietnamese): `FUNC_LOGIC/WAREHOUSE_FLOW_VA_NGHIEP_VU_VI.md`
- Master guide: `FUNC_LOGIC/WAREHOUSE_MASTER_GUIDE.md`

---

_Generated from repository analysis. Behaviour may vary by module flags and permissions; verify on staging before sign-off._
