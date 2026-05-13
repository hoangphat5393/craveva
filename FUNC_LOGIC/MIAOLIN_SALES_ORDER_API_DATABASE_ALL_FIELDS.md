# Miaolin / Maolin — AI API to create Sales Order (SO) in ERP: full database field list

**Requirement source:** Internal discussion (current phase: API for AI to create **sales orders** pushed into ERP).  
**Document purpose:** List **all database columns** related to sales orders (SO) so BAs/BEs can design the API contract and ERP mapping.

**Codebase note:** In this Laravel app, **Sales Order** maps to `App\Models\Order` (table **`orders`**); lines are `App\Models\OrderItems` (table **`order_items`**). Business flow: `FUNC_LOGIC/SALES_PURCHASE_FLOW.md`.

---

## 1. Table `orders` — sales order header (SO)

| Column                  | Type / notes                                                                            | Required on insert | Short notes                                                      |
| ----------------------- | --------------------------------------------------------------------------------------- | ------------------ | ---------------------------------------------------------------- |
| `id`                    | BIGINT UNSIGNED, auto-increment                                                         | Auto               | Primary key.                                                     |
| `company_id`            | INT UNSIGNED, FK → `companies`                                                          | Per tenant         | Multi-company; API usually resolves from auth / webhook context. |
| `client_id`             | INT UNSIGNED, FK → `users`                                                              | Recommended        | Customer (user of type client).                                  |
| `project_id`            | INT UNSIGNED, FK → `projects`, nullable                                                 | No                 | Project link.                                                    |
| `estimate_id`           | BIGINT UNSIGNED, nullable, indexed                                                      | No                 | If the SO was created from an estimate.                          |
| `order_date`            | DATE                                                                                    | Yes                | Order date.                                                      |
| `sub_total`             | DOUBLE(30,2)                                                                            | Yes                | Subtotal before tax/discount (per app rules).                    |
| `discount`              | DOUBLE                                                                                  | Yes (default 0)    | Discount amount.                                                 |
| `discount_type`         | ENUM `percent`, `fixed`                                                                 | Yes                | How discount is applied.                                         |
| `total`                 | DOUBLE(30,2)                                                                            | Yes                | Total after discount (per app).                                  |
| `due_amount`            | DOUBLE, default 0                                                                       | Yes                | Amount still owed (often synced with payments/invoices).         |
| `status`                | ENUM: `pending`, `on-hold`, `failed`, `processing`, `completed`, `canceled`, `refunded` | Yes                | Order status.                                                    |
| `currency_id`           | INT UNSIGNED, FK → `currencies`, nullable                                               | No                 | Currency.                                                        |
| `show_shipping_address` | ENUM `yes`, `no`                                                                        | Yes                | Whether to show shipping address on documents.                   |
| `note`                  | VARCHAR(191), nullable                                                                  | No                 | Header note.                                                     |
| `added_by`              | INT UNSIGNED, FK → `users`, nullable                                                    | No                 | Created by.                                                      |
| `last_updated_by`       | INT UNSIGNED, FK → `users`, nullable                                                    | No                 | Last updated by.                                                 |
| `company_address_id`    | BIGINT UNSIGNED, FK → `company_addresses`, nullable                                     | No                 | Company address / billing–shipping context.                      |
| `custom_order_number`   | VARCHAR, nullable                                                                       | No                 | Custom display number (prefix/format).                           |
| `order_number`          | VARCHAR(255), nullable (per migration)                                                  | No                 | Order number (display / sortable per settings).                  |
| `original_order_number` | VARCHAR, nullable                                                                       | No                 | Raw number before prefix formatting.                             |
| `created_at`            | TIMESTAMP                                                                               | Auto               |                                                                  |
| `updated_at`            | TIMESTAMP                                                                               | Auto               |                                                                  |

**Note:** The `Order` model also supports **custom fields** (`CustomFieldsTrait`) — values may live outside the `orders` table (via `custom_fields` / field group configuration). If the AI needs per-company extensions, add a spec for `custom_field_groups` on model `App\Models\Order`.

---

## 2. Table `order_items` — sales order lines (SO lines)

| Column         | Type / notes                                 | Required on insert | Short notes                                                 |
| -------------- | -------------------------------------------- | ------------------ | ----------------------------------------------------------- |
| `id`           | BIGINT UNSIGNED, auto-increment              | Auto               |                                                             |
| `order_id`     | BIGINT UNSIGNED, FK → `orders`               | Yes                | Link to header.                                             |
| `product_id`   | INT UNSIGNED, FK → `products`, nullable      | No                 | If the line is tied to catalog.                             |
| `item_name`    | VARCHAR(191)                                 | Yes                | Line name (text).                                           |
| `item_summary` | TEXT, nullable                               | No                 | Description / narrative.                                    |
| `type`         | ENUM `item`, `discount`, `tax`               | Yes                | Line kind (product / discount / tax).                       |
| `quantity`     | DOUBLE(30,2)                                 | Yes                | Quantity.                                                   |
| `unit_price`   | INT                                          | Yes                | Unit price (stored as INT in DB — align scale with UI/API). |
| `amount`       | DOUBLE(30,2)                                 | Yes                | Line amount.                                                |
| `hsn_sac_code` | VARCHAR(191), nullable                       | No                 | HSN/SAC code if used.                                       |
| `taxes`        | VARCHAR(191), nullable                       | No                 | Often JSON / list of tax ids (serialized per app).          |
| `unit_id`      | BIGINT UNSIGNED, FK → `unit_types`, nullable | No                 | Unit of measure.                                            |
| `sku`          | VARCHAR, nullable                            | No                 | Line SKU.                                                   |
| `field_order`  | INT, default 0                               | No                 | Display order of lines.                                     |
| `created_at`   | TIMESTAMP                                    | Auto               |                                                             |
| `updated_at`   | TIMESTAMP                                    | Auto               |                                                             |

**Related table (line media):** `order_item_images` (`order_item_id`, …) — only if the API supports image attachments per line.

---

## 3. Related entities often needed in API payloads (not columns on `orders`, but ERP/AI usually resolve them)

- **`users`** (customer via `client_id`): bill-to / ship-to may live on the user + `client_details`.
- **`currencies`**: via `currency_id`.
- **`company_addresses`**: via `company_address_id`.
- **`products`**, **`unit_types`**: via FK columns on order lines.

---

## 4. API spec hints (beyond “column list only”, useful for Miaolin)

- **Minimum to create an SO:** `company_id`, `client_id`, `order_date`, valid `order_items` rows (`type`, `quantity`, `unit_price`, `amount`, …), and header totals (`sub_total`, `discount`, `discount_type`, `total`, `due_amount`) — clarify whether the API **recalculates** totals or **trusts** the client payload to avoid mismatches.
- **Idempotency / trace:** consider an external code (`external_reference`) if the ERP requires it — there is **no** standard column for that on `orders` in this repo today; add a migration + spec if needed.

---

_Generated from migrations + schema dump in the repo; for production, verify with `SHOW CREATE TABLE` on the live database after all migrations._
