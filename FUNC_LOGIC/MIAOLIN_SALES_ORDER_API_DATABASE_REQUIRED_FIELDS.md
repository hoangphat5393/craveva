# Miaolin — AI sales order API: required database fields only

**Source:** Internal request (current phase: API for AI to create **sales orders** into ERP).  
**Purpose:** List only columns that are **mandatory** for persisting a sales order in this app’s schema.

**Codebase mapping:** Sales order header = table **`orders`** (`App\Models\Order`); lines = **`order_items`** (`App\Models\OrderItems`). Flow reference: `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`.

---

## Required on insert (no DB default, NOT NULL)

These are the only columns you **must** supply on a plain SQL `INSERT` (other columns are nullable or have defaults).

### `orders` (SO header)

| Column       | Notes                         |
| ------------ | ----------------------------- |
| `order_date` | `DATE`.                       |
| `sub_total`  | Numeric; subtotal for header. |
| `total`      | Numeric; grand total header.  |

### `order_items` (SO lines)

| Column       | Notes                                   |
| ------------ | --------------------------------------- |
| `order_id`   | FK to `orders.id` (header must exist).  |
| `item_name`  | Line title / label.                     |
| `quantity`   | Line quantity.                          |
| `unit_price` | Integer in DB; align with API/UI units. |
| `amount`     | Line amount.                            |

---

## Integration note (not enforced as NOT NULL in DB)

For a real tenant-scoped order, the API will normally still require **`company_id`** and **`client_id`** even though they are nullable in the schema.

**Custom fields:** `Order` may use `CustomFieldsTrait`; extra values can live outside `orders` per company `custom_field_groups` — not listed here.

---

_Confirm on your environment with `SHOW CREATE TABLE orders`, `order_items` after all migrations._
