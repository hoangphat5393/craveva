# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### invoice_warehouse_stock_postings

- Columns: company_id, invoice_id, invoice_item_id, product_id, quantity, warehouse_id
- Migrations: 2026_03_28_120000_create_invoice_warehouse_stock_postings_table.php

### product_unit_conversions

- Columns: company_id, factor_to_base, product_id, unit_id
- Migrations: 2026_04_02_220000_create_product_unit_conversions_table.php

### stock_reservations

- Columns: batch_number, company_id, expiration_date, manufacturing_date, product_id, quantity, reference_id, reference_type, reserved_quantity, status, warehouse_id
- Migrations: 2026_03_23_120000_create_warehouse_product_batches_and_stock_reservations_tables.php

### warehouse_company_flow_settings

- Columns: ai_order_webhook_check_stock, allow_negative_stock, company_id, inbound_from_delivery_order_received, inbound_from_purchase_order_delivered, sales_outbound_enabled, sales_outbound_mode, strict_unit_conversion
- Migrations: 2026_04_09_100000_create_warehouse_company_flow_settings_table.php

### warehouse_product_batches

- Columns: batch_number, company_id, expiration_date, manufacturing_date, product_id, quantity, reference_id, reference_type, reserved_quantity, status, warehouse_id
- Migrations: 2026_03_23_120000_create_warehouse_product_batches_and_stock_reservations_tables.php

### warehouse_product_stock

- Columns: product_id, quantity, warehouse_id
- Migrations: 2026_01_19_083641_create_warehouse_product_stock_table.php

### warehouse_sync_reconciliation_logs

- Columns: company_id, report_date, report_type, summary_json
- Migrations: 2026_04_02_220200_create_warehouse_sync_reconciliation_logs_table.php

### warehouses

- Columns: address, code, company_id, description, is_default, name, status
- Migrations: 2026_01_19_083640_create_warehouses_table.php

## Entities (table + casts)

- Modules/Warehouse/Entities/InvoiceWarehouseStockPosting.php (table=invoice_warehouse_stock_postings)
- Modules/Warehouse/Entities/ProductUnitConversion.php (table=product_unit_conversions)
- Modules/Warehouse/Entities/StockReservation.php (table=stock_reservations)
- Modules/Warehouse/Entities/Warehouse.php
- Modules/Warehouse/Entities/WarehouseCompanyFlowSetting.php
- Modules/Warehouse/Entities/WarehouseProductBatch.php (table=warehouse_product_batches)
- Modules/Warehouse/Entities/WarehouseProductStock.php (table=warehouse_product_stock)
- Modules/Warehouse/Entities/WarehouseSyncReconciliationLog.php (table=warehouse_sync_reconciliation_logs)
