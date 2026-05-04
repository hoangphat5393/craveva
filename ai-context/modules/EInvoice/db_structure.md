# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### e_invoice_company_settings

- Columns: company_id, e_invoice_company_id, e_invoice_company_id_scheme, electronic_address, electronic_address_scheme
- Migrations: 2023_11_03_114439_create_e_invoice_company_settings_table.php

### e_invoice_settings

- Columns: notify_update, purchase_code, supported_until
- Migrations: 2023_11_03_071005_create_e_invoice_settings_table.php

## Entities (table + casts)

- Modules/EInvoice/Entities/EInvoiceCompanySetting.php
- Modules/EInvoice/Entities/EInvoiceSetting.php
