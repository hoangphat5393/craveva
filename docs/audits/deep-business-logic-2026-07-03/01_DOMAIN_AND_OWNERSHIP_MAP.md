# Domain And Ownership Map

| Domain / aggregate | Owner va entry point | Bang chinh | Service / observer | Ledger / side effect |
|---|---|---|---|---|
| Estimate | `EstimateController`, import job | `estimates`, `estimate_items`, `estimate_bom_lines`, `estimate_approval_events` | `EstimateTotalsCalculator`, `EstimateImportProcessor` | Commercial total; chua co import source ledger |
| Sales Order | `OrderController`, Estimate conversion, integrations | `orders`, `order_items` | controller/observer va integration services | Nguon cho Sales DO, invoice, Production Order |
| Sales DO | `SalesShipmentController` | `sales_dos`, `sales_do_items` | `SalesDoService`, `SalesShipmentStockService` | `stock_reservations`, `stock_movements` outbound |
| Invoice | `InvoiceController`, `InvoiceObserver` | `invoices`, `invoice_items`, `invoice_warehouse_stock_postings` | `InvoiceWarehouseStockService`, `SalesDoInvoiceGuardService` | Outbound neu mode invoice; finance totals/payment |
| Credit Note | Credit note controllers/observers | `credit_notes`, `credit_note_items` | `CreditNoteWarehouseStockService` | Sales return inbound va delete reversal outbound |
| Purchase Order | `PurchaseOrderController` | `purchase_orders`, `purchase_items` | `PurchaseOrderObserver` | Legacy `purchase_stock_adjustments`; optional warehouse inbound |
| GRN | `DeliveryOrderController` | `grns`, `grn_items` | `GrnService`, `DeliveryOrderObserver` | Warehouse inbound movement va `inbound_stock_applied` |
| Vendor Credit | vendor credit controllers/observers | `purchase_vendor_credits`, `purchase_vendor_items` | `VendorCreditWarehouseStockService` | Purchase return outbound va reversal inbound |
| Warehouse batch | Warehouse stock/transfer controllers | `warehouse_product_batches`, `warehouse_product_stock` | `StockMovementService` | Physical quantity va append-only `stock_movements` |
| Reservation | Sales DO, Production | `stock_reservations`, `warehouse_product_batches.reserved_quantity` | `StockReservationService` | Soft commitment, khong doi physical quantity |
| Production Order | `ProductionOrderController` | `production_orders`, snapshot items | `ProductionPostingService`, reservation services | BOM snapshot + RM reservation |
| Production Batch | `ProductionBatchController` | `production_batches`, consumptions, outputs | `ProductionPostingService` | RM outbound, FG inbound, inventory sync |
| Pricing | Pricing controllers/API | pricing tier/rule/client tables | `PricingService`, `VolumeDiscountService` | Don gia/discount, khong phai stock ledger |
| Import | Estimate/SO/product/inventory jobs | domain tables + import metadata | row processors/chunk jobs | Can idempotency source hash theo tung loai |
| Tenant boundary | HTTP auth/company context | hau het bang co `company_id` | `CompanyScope`, explicit company predicates | Khong co tenant execution context chung cho queue |

## Ownership rules rut ra tu code

1. `stock_movements` phai la audit ledger cho moi thay doi physical stock.
2. `warehouse_product_batches.quantity` la physical stock theo batch; `warehouse_product_stock.quantity` la tong cache theo warehouse/product.
3. `stock_reservations` va `reserved_quantity` chi la commitment, khong phai movement.
4. Chung tu nguon phai chiu trach nhiem idempotency va reversal; generic stock service phai dam bao atomic invariant.
5. Moi background job phai mang company ID explicit vi `CompanyScope` khong hoat dong khi khong co authenticated user.

## Boundary dang bi mo

- Purchase con song song legacy `PurchaseStockAdjustment` va Warehouse ledger.
- Invoice mode va shipment mode cung ton tai, lam tang ma tran reversal/idempotency.
- GRN observer post stock, trong khi service cho phep mutate item/status sau posting.
- Production FG con sync them legacy purchase inventory ledger sau warehouse inbound.

