# Miaolin — Sales Order API: database fields

**Mapping:** Header = `orders` (`App\Models\Order`); lines = `order_items` (`App\Models\OrderItems`).  
**Flow:** `FUNC_LOGIC/SALES_BUSINESS.md`

---

## 1. Required on insert (NOT NULL, no default)

Plain SQL `INSERT` must supply these unless noted.

### `orders` (header)

| Column       | Notes               |
| ------------ | ------------------- |
| `order_date` | `DATE`              |
| `sub_total`  | Numeric subtotal    |
| `total`      | Numeric grand total |

### `order_items` (lines)

| Column       | Notes                         |
| ------------ | ----------------------------- |
| `order_id`   | FK → `orders.id`              |
| `item_name`  | Line title                    |
| `quantity`   | Line quantity                 |
| `unit_price` | Integer in DB; align with API |
| `amount`     | Line amount                   |

**Integration (not enforced NOT NULL in DB):** API should still set **`company_id`** and **`client_id`** for tenant-scoped orders. Custom fields via `CustomFieldsTrait` may live outside `orders`.

---

## 2. Full field list — `orders`

| Column                      | Required on insert | Short notes                           |
| --------------------------- | ------------------ | ------------------------------------- |
| `id`                        | Auto               | PK                                    |
| `company_id`                | Per tenant         | Multi-company                         |
| `client_id`                 | Recommended        | Customer (`users` client)             |
| `project_id`                | No                 | FK `projects`, nullable               |
| `estimate_id`               | No                 | If SO from estimate                   |
| `order_date`                | Yes                |                                       |
| `sub_total`                 | Yes                |                                       |
| `discount`                  | Yes (default 0)    |                                       |
| `discount_type`             | Yes                | `percent` / `fixed`                   |
| `total`                     | Yes                |                                       |
| `due_amount`                | Yes                | Often synced with payments            |
| `status`                    | Yes                | `pending`, `completed`, `canceled`, … |
| `currency_id`               | No                 |                                       |
| `show_shipping_address`     | Yes                | `yes` / `no`                          |
| `note`                      | No                 |                                       |
| `added_by`                  | No                 |                                       |
| `last_updated_by`           | No                 |                                       |
| `company_address_id`        | No                 |                                       |
| `custom_order_number`       | No                 |                                       |
| `order_number`              | No                 | Display / sortable                    |
| `original_order_number`     | No                 |                                       |
| `created_at` / `updated_at` | Auto               |                                       |

---

## 3. Full field list — `order_items`

| Column                      | Required on insert | Short notes               |
| --------------------------- | ------------------ | ------------------------- |
| `id`                        | Auto               |                           |
| `order_id`                  | Yes                | FK header                 |
| `product_id`                | No                 | Catalog link              |
| `item_name`                 | Yes                |                           |
| `item_summary`              | No                 |                           |
| `type`                      | Yes                | `item`, `discount`, `tax` |
| `quantity`                  | Yes                |                           |
| `unit_price`                | Yes                | INT in DB                 |
| `amount`                    | Yes                |                           |
| `hsn_sac_code`              | No                 |                           |
| `taxes`                     | No                 | Often serialized tax ids  |
| `unit_id`                   | No                 | UOM                       |
| `sku`                       | No                 |                           |
| `field_order`               | No                 | Display order             |
| `created_at` / `updated_at` | Auto               |                           |

**Related:** `order_item_images` if API supports line attachments.

---

## 4. API hints

- **Minimum payload:** `company_id`, `client_id`, `order_date`, valid lines, header totals — clarify whether API **recalculates** or **trusts** client totals.
- **Idempotency:** no standard `external_reference` on `orders` today; add migration if needed.

_Verify with `SHOW CREATE TABLE orders, order_items` after migrations._
