# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### sms_notification_settings

- Columns: msg91_flow_id, msg91_template, send_sms, setting_name, slug, whatsapp_template
- Migrations: 2022_03_29_090843_create_sms_notification_settings_table.php

### sms_settings

- Columns: account_sid, auth_token, from_number, msg91_auth_key, msg91_from, msg91_status, nexmo_api_key, nexmo_api_secret, nexmo_from_number, nexmo_status, purchase_code, status, supported_until, whatapp_from_number, whatsapp_status
- Migrations: 2020_07_07_085510_create_twilio_settings_table.php

### sms_template_ids

- Columns: msg91_flow_id, sms_setting_slug, whatsapp_template_sid
- Migrations: 2022_08_27_095940_ whatsapp_template_id_sms_notification_setting_table.php

## Entities (table + casts)

- Modules/Sms/Entities/SmsNotificationSetting.php
- Modules/Sms/Entities/SmsSetting.php
- Modules/Sms/Entities/SmsTemplateId.php
