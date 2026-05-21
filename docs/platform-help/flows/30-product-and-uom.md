# Flow: Product and alternate UOM

## URL

[purchase-products](../pages/operations/purchase-products.md) — list; create/edit via modal or full page.

## Enable alternate UOM

1. **Classification → Unit Type** — base unit (`#unit_type_id`).
2. **Pricing → Selling Price** — value > 0.
3. Section **Units of measure (UOM)** → **+ Add alternate UOM**.

## One alternate row

| Column            | Meaning                             |
| ----------------- | ----------------------------------- |
| Unit              | Selling unit (not base)             |
| Conversion factor | Base units per 1 of this unit       |
| Selling price     | Default base × factor; can override |
| For sale          | Allow selling in this unit          |

## UI issues

- **Add UOM** disabled: missing unit or price.
- Dropdown clipped: use bootstrap-select with `container: body`.

## More detail

[REFERENCE/BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md), [REFERENCE/UI-CONVENTIONS.md](../REFERENCE/UI-CONVENTIONS.md)
