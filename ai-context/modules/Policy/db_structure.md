# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### policies

- Columns: acknowledged_on, added_by, company_id, date, department_id_json, description, designation_id_json, employment_type_json, file, ip, policy_id, signature_file, signature_required, title, updated_by, user_id
- Migrations: 2024_05_02_090106_create_policies_table.php

### policy_employee_acknowledged

- Columns: acknowledged_on, added_by, company_id, date, department_id_json, description, designation_id_json, employment_type_json, file, ip, policy_id, signature_file, signature_required, title, updated_by, user_id
- Migrations: 2024_05_02_090106_create_policies_table.php

### policy_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_05_01_090106_create_policies_global_settings_table.php

## Entities (table + casts)

- Modules/Policy/Entities/Policy.php
- Modules/Policy/Entities/PolicyEmployeeAcknowledged.php (table=policy_employee_acknowledged)
- Modules/Policy/Entities/PolicyFile.php
- Modules/Policy/Entities/PolicySetting.php (table=policy_settings)
