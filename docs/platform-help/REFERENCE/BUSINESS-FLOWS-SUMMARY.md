# Business flows summary (in-corpus)

Condensed from ERP domain logic for agent answers without external docs.

## Purchase → pay vendor

1. Maintain **vendors** and **products** (cost, base UOM).
2. Create **purchase order** — lines, UOM, optional warehouse.
3. Receive goods → **GRN** increases stock.
4. Record **vendor bill** from PO/GRN.
5. **Vendor payment** clears payable.

Stock and costing follow company warehouse settings.

## Sales → cash in

1. **Client** master data.
2. **Sales order** — lines, UOM, pricing tier if enabled.
3. **Delivery order** / shipment — stock deduction per warehouse.
4. **Invoice** from SO/DO.
5. **Payment** allocation to invoice.

## Returns

- **Credit note** (sales): return stock in, adjust AR.
- **Vendor credit** (purchase): stock out, adjust AP.

## Production

1. **BOM** defines output product and components (UOM conversion where mapped).
2. **Production order** consumes components, produces output.
3. **Batch** tracking optional per module settings.
4. Stock movements tie to warehouse module.

## Pricing module

- **Tier rules** (volume, conditions).
- **Client tier assignment** overrides default pricing on SO/estimate lines.

## Product UOM rules

- **Base unit** on product (Classification).
- **Selling price** > 0 required before alternate UOM rows.
- Alternate row: unit, factor to base, derived or override selling price, for-sale flag.

## Company setup

- Enable **modules** per role.
- Assign **permissions** (`view_*`, `add_*`, …).
- Optional: bank accounts, taxes, invoice numbering, warehouse flow settings.

Detailed step-by-step URLs: `flows/` and `pages/operations/`, `pages/sales/`.
