# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### qr_code_data

- Columns: background_color, company_id, data, foreground_color, form_data, logo, logo_size, margin, size, title, type
- Migrations: 2023_12_26_100154_create_qr_code_data_table.php

### qr_code_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2023_10_30_051415_create_qr_code_settings_table.php

## Entities (table + casts)

- Modules/QRCode/Entities/QRCodeSetting.php (table=qr_code_settings)
- Modules/QRCode/Entities/QrCodeData.php
