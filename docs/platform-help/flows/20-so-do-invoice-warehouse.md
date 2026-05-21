# Flow: Sales → DO → stock → invoice

**Scope:** Orders, delivery/shipment, warehouse, invoices, payments.

## Main URLs

1. [clients](../pages/sales/clients.md)
2. [orders](../pages/sales/orders.md)
3. [delivery-orders](../pages/operations/delivery-orders.md) or [sales-shipments](../pages/operations/sales-shipments.md)
4. [warehouse](../pages/operations/warehouse.md)
5. [invoices](../pages/sales/invoices.md)
6. [payments](../pages/finance/payments.md)

## Business steps

1. Create **sales order** with lines and UOM.
2. Reserve/issue stock per warehouse rules.
3. Create **delivery order** / shipment.
4. Issue **invoice**.
5. Record **payment**.

## Returns

Credit note + stock in — covered in [REFERENCE/BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md).

## More detail

[REFERENCE/BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md)
