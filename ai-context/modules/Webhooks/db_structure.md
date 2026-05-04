# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### webhooks_global_settings

- Columns: notify_update, purchase_code, supported_until
- Migrations: 2023_10_17_073008_create_webhooks_global_settings_table.php

### webhooks_logs

- Columns: action, headers, method, raw_content, response, response_code, webhook_for, webhooks_setting_id
- Migrations: 2023_11_08_073008_create_webhook_logs_table.php

### webhooks_requests

- Columns: body_key, body_value, company_id, headers_key, headers_value, request_type, webhooks_setting_id
- Migrations: 2023_11_09_083125_create_webhooks_requests_table.php

### webhooks_settings

- Columns: action, company_id, name, request_format, request_method, run_debug, status, url, webhook_for
- Migrations: 2023_11_07_073008_create_webhooks_settings_table.php

## Entities (table + casts)

- Modules/Webhooks/Entities/WebhooksGlobalSetting.php
- Modules/Webhooks/Entities/WebhooksLog.php
- Modules/Webhooks/Entities/WebhooksRequest.php
- Modules/Webhooks/Entities/WebhooksSetting.php
