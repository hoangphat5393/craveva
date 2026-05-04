# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### blacklist_emails

- Columns: email
- Migrations: 2023_11_23_110035_create_blacklist_emails_table.php

### blacklist_ips

- Columns: ip_address
- Migrations: 2023_11_23_044655_create_blacklist_ips_table.php

### cyber_securities

- Columns: alert_after_lockouts, email, extended_lockout_time, ip, ip_check, lockout_time, max_lockouts, max_retries, reset_retries, unique_session, user_timeout
- Migrations: 2023_11_22_082732_create_cyber_securities_table.php

### cyber_security_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2023_11_11_090216_create_cyber_security_settings_table.php

### login_expiries

- Columns: expiry_date, user_id
- Migrations: 2023_11_23_164003_create_login_expiries_table.php

## Entities (table + casts)

- Modules/CyberSecurity/Entities/BlacklistEmail.php
- Modules/CyberSecurity/Entities/BlacklistIp.php
- Modules/CyberSecurity/Entities/CyberSecurity.php
- Modules/CyberSecurity/Entities/CyberSecuritySetting.php
- Modules/CyberSecurity/Entities/LoginExpiry.php
