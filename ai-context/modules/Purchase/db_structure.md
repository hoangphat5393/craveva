# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### delivery_order_items

- Columns: batch_number, company_id, created_by, delivery_date, delivery_fee, delivery_number, delivery_order_id, erp_shipment_reference, expiry_date, inbound_stock_applied, notes, order_id, order_item_id, outbound_stock_applied, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, quantity_shipped, sales_shipment_id, shipment_date, shipment_number, status, type, unit_id, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_31_091500_drop_legacy_sales_shipment_and_delivery_order_tables.php

### delivery_orders

- Columns: batch_number, company_id, created_by, delivery_date, delivery_fee, delivery_number, delivery_order_id, erp_shipment_reference, expiry_date, inbound_stock_applied, notes, order_id, order_item_id, outbound_stock_applied, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, quantity_shipped, sales_shipment_id, shipment_date, shipment_number, status, type, unit_id, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_31_091500_drop_legacy_sales_shipment_and_delivery_order_tables.php

### grn_items

- Columns: batch_number, company_id, created_by, delivery_fee, erp_shipment_reference, expiry_date, grn_date, grn_id, grn_number, inbound_stock_applied, legacy_delivery_order_id, legacy_delivery_order_item_id, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, status, type, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_30_191000_create_grn_tables.php

### grns

- Columns: batch_number, company_id, created_by, delivery_fee, erp_shipment_reference, expiry_date, grn_date, grn_id, grn_number, inbound_stock_applied, legacy_delivery_order_id, legacy_delivery_order_item_id, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, status, type, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_30_191000_create_grn_tables.php

### purchase_bill_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_bills

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_inventory_adjustment

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_inventory_files

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_inventory_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_item_images

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_item_taxes

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_items

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_management_settings

- Columns: purchase_code, supported_until
- Migrations: 2023_10_23_071216_create_purchase_management_settings.php

### purchase_notification_settings

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_order_files

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_order_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_order_settings

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_orders

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_payment_bills

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_payment_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_product_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_settings

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_stock_adjustment_reasons

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_stock_adjustments

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_categories

- Columns: category_name, company_id
- Migrations: 2025_06_23_120000_create_purchase_vendor_categories_table.php

### purchase_vendor_contacts

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_credit_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_credit_item_images

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_credits

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_histories

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_items

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_notes

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_payments

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendor_user_notes

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### purchase_vendors

- Columns: added_by, address_id, amount, ask_password, bank_account_id, bill_date, bill_number_digit, bill_number_separator, bill_prefix, billed_status, billing_address, calculate_tax, company_id, company_name, contact_name, credit_note, currency_id, date, default_currency_id, default_image, delivery_status, discount, discount_type, due_amount, email, exchange_rate, expected_delivery_date, hsn_sac_code, item_name, item_summary, last_updated_by, member_id, name, note, note_details, note_title, note_type, opening_balance, opening_stock, phone
- Migrations: 2023_05_03_070320_create_vendor.php

### sales_do_items

- Columns: batch_number, company_id, created_by, do_date, do_number, legacy_sales_shipment_id, legacy_sales_shipment_item_id, notes, order_id, order_item_id, outbound_stock_applied, product_id, quantity_ordered, quantity_shipped, sales_do_id, status, unit_id, updated_by, warehouse_id
- Migrations: 2026_03_30_190000_create_sales_do_tables.php

### sales_dos

- Columns: batch_number, company_id, created_by, do_date, do_number, legacy_sales_shipment_id, legacy_sales_shipment_item_id, notes, order_id, order_item_id, outbound_stock_applied, product_id, quantity_ordered, quantity_shipped, sales_do_id, status, unit_id, updated_by, warehouse_id
- Migrations: 2026_03_30_190000_create_sales_do_tables.php

### sales_shipment_items

- Columns: batch_number, company_id, created_by, delivery_date, delivery_fee, delivery_number, delivery_order_id, erp_shipment_reference, expiry_date, inbound_stock_applied, notes, order_id, order_item_id, outbound_stock_applied, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, quantity_shipped, sales_shipment_id, shipment_date, shipment_number, status, type, unit_id, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_29_100000_create_sales_shipments_tables.php, 2026_03_31_091500_drop_legacy_sales_shipment_and_delivery_order_tables.php

### sales_shipments

- Columns: batch_number, company_id, created_by, delivery_date, delivery_fee, delivery_number, delivery_order_id, erp_shipment_reference, expiry_date, inbound_stock_applied, notes, order_id, order_item_id, outbound_stock_applied, picking_rule_applied, product_id, purchase_item_id, purchase_order_id, quantity_ordered, quantity_received, quantity_shipped, sales_shipment_id, shipment_date, shipment_number, status, type, unit_id, updated_by, warehouse_id, wms_shipment_reference
- Migrations: 2026_03_29_100000_create_sales_shipments_tables.php, 2026_03_31_091500_drop_legacy_sales_shipment_and_delivery_order_tables.php

## Entities (table + casts)

- Modules/Purchase/Entities/DeliveryOrderItem.php (table=delivery_order_items)
- Modules/Purchase/Entities/GrnItem.php (table=grn_items)
- Modules/Purchase/Entities/OrderDeliveryItem.php
- Modules/Purchase/Entities/PurchaseBill.php
- Modules/Purchase/Entities/PurchaseBillHistory.php
- Modules/Purchase/Entities/PurchaseBillItem.php
- Modules/Purchase/Entities/PurchaseBillNumberSetting.php
- Modules/Purchase/Entities/PurchaseInventory.php (table=purchase_inventory_adjustment)
- Modules/Purchase/Entities/PurchaseInventoryFile.php (table=purchase_inventory_files)
- Modules/Purchase/Entities/PurchaseInventoryHistory.php
- Modules/Purchase/Entities/PurchaseItem.php
- Modules/Purchase/Entities/PurchaseItemImage.php
- Modules/Purchase/Entities/PurchaseItemTax.php (table=purchase_item_taxes)
- Modules/Purchase/Entities/PurchaseManagementSetting.php (table=purchase_management_settings)
- Modules/Purchase/Entities/PurchaseNotificationSetting.php
- Modules/Purchase/Entities/PurchaseOrder.php
- Modules/Purchase/Entities/PurchaseOrderFile.php
- Modules/Purchase/Entities/PurchaseOrderHistory.php
- Modules/Purchase/Entities/PurchaseOrderSetting.php
- Modules/Purchase/Entities/PurchasePaymentBill.php
- Modules/Purchase/Entities/PurchasePaymentHistory.php
- Modules/Purchase/Entities/PurchaseProduct.php (table=products)
- Modules/Purchase/Entities/PurchaseProductHistory.php
- Modules/Purchase/Entities/PurchaseSetting.php (table=purchase_settings)
- Modules/Purchase/Entities/PurchaseStockAdjustment.php (table=purchase_stock_adjustments)
- Modules/Purchase/Entities/PurchaseStockAdjustmentReason.php (table=purchase_stock_adjustment_reasons)
- Modules/Purchase/Entities/PurchaseVendor.php
- Modules/Purchase/Entities/PurchaseVendorCategory.php (table=purchase_vendor_categories)
- Modules/Purchase/Entities/PurchaseVendorContact.php
- Modules/Purchase/Entities/PurchaseVendorCredit.php
- Modules/Purchase/Entities/PurchaseVendorCreditHistory.php
- Modules/Purchase/Entities/PurchaseVendorCreditItemImage.php
- Modules/Purchase/Entities/PurchaseVendorHistory.php
- Modules/Purchase/Entities/PurchaseVendorItem.php
- Modules/Purchase/Entities/PurchaseVendorNote.php
- Modules/Purchase/Entities/PurchaseVendorPayment.php
- Modules/Purchase/Entities/PurchaseVendorUserNotes.php
- Modules/Purchase/Entities/SalesDo.php (table=sales_dos)
- Modules/Purchase/Entities/SalesDoItem.php (table=sales_do_items)
- Modules/Purchase/Entities/SalesShipment.php (table=sales_shipments)
- Modules/Purchase/Entities/SalesShipmentItem.php (table=sales_shipment_items)
