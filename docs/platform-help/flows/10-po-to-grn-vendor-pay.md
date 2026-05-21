# Flow: Purchase → GRN → pay vendor

**Scope:** Purchase module, warehouse, vendor payments.

## Main URLs

1. [vendors](../pages/operations/vendors.md)
2. [purchase-products](../pages/operations/purchase-products.md)
3. [purchase-order](../pages/operations/purchase-order.md)
4. GRN / goods receipt (Purchase module — Operations menu)
5. [bills](../pages/operations/bills.md)
6. Vendor payments (Operations → Vendor Payments)

## Business steps

1. Create **vendor** and **product** (cost, UOM).
2. Create **purchase order** with lines and receiving warehouse if used.
3. Post **GRN** — stock in.
4. Create **vendor bill** from PO/GRN.
5. Record **vendor payment**.

## Expected outcome

Stock matches GRN; AP matches bill and payment.

## More detail

[REFERENCE/BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md)
