# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### server_domains

- Columns: annual_cost, assigned_to, auto_renewal, billing_cycle, client_id, company_id, created_by, dns_provider, dns_records, dns_status, domain_name, domain_provider, domain_type, expiry_date, expiry_notification, hosting_id, last_notification_sent, nameservers, notes, notification_days_before, notification_time_unit, password, project_id, provider_url, registrar, registrar_password, registrar_status, registrar_url, registrar_username, registration_date, renewal_date, status, updated_by, username, whois_protection
- Migrations: 2025_07_28_000002_create_server_domains_table.php

### server_hostings

- Columns: annual_cost, assigned_to, backup_frequency, backup_info, bandwidth, billing_cycle, client, company_id, control_panel, control_panel_url, cpanel_url, created_by, database_host, database_limit, database_name, database_password, database_username, disk_space, domain_name, email_limit, expiry_notification, ftp_host, ftp_password, ftp_username, hosting_provider, ip_address, last_backup_date, last_notification_sent, monthly_cost, name, notes, notification_days_before, notification_time_unit, password, project, provider_url, purchase_date, renewal_date, server_location, server_type
- Migrations: 2025_07_28_000001_create_server_hostings_table.php

### server_logs

- Columns: action, company_id, description, entity_id, entity_type, ip_address, log_type, new_values, old_values, performed_by, user_agent
- Migrations: 2025_07_28_000003_create_server_logs_table.php

### server_manager_global_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2025_01_01_00001_create_server_global_settings_table.php

### server_providers

- Columns: company_id, created_by, description, name, status, type, updated_by, url
- Migrations: 2025_07_28_000006_create_server_providers_table.php

### server_settings

- Columns: company_id, description, setting_key, setting_type, setting_value
- Migrations: 2025_07_28_000004_create_server_settings_table.php

### server_types

- Columns: company_id, created_by, description, name, slug, status, updated_by
- Migrations: 2025_07_28_000009_create_server_types_table.php

## Entities (table + casts)

- Modules/ServerManager/Entities/ServerDomain.php
- Modules/ServerManager/Entities/ServerHosting.php
- Modules/ServerManager/Entities/ServerLog.php
- Modules/ServerManager/Entities/ServerManagerGlobalSetting.php (table=server_manager_global_settings)
- Modules/ServerManager/Entities/ServerProvider.php
- Modules/ServerManager/Entities/ServerSetting.php
- Modules/ServerManager/Entities/ServerType.php
