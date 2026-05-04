# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### biometric_commands

- Columns: command, command_id, company_id, device_serial_number, employee_id, executed_at, failed_at, sent_at, status, type, user_id
- Migrations: 2025_05_15_092248_biometric_commands.php

### biometric_device_attendances

- Columns: company_id, device_name, device_serial_number, employee_id, stamp, status1, status2, status3, status4, status5, table, timestamp, user_id
- Migrations: 2025_05_01_022209_create_biometric_attendances_table.php

### biometric_devices

- Columns: company_id, device_ip, device_name, last_online, serial_number, status
- Migrations: 2024_11_12_113048_create_biometric_devices_table.php

### biometric_employees

- Columns: biometric_employee_id, company_id, fingerprint_id, fingerprint_template, has_fingerprint, user_id
- Migrations: 2024_11_13_113406_create_biometric_employees_table.php

### biometric_global_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_08_28_090058_create_biometric_global_settings_table.php

### biometric_settings

- Columns: company_id, last_transaction_id
- Migrations: 2024_08_28_090058_create_biometric_settings_table.php

## Entities (table + casts)

- Modules/Biometric/Entities/BiometricAttendance.php (table=biometric_device_attendances)
- Modules/Biometric/Entities/BiometricCommands.php (table=biometric_commands)
- Modules/Biometric/Entities/BiometricDevice.php
- Modules/Biometric/Entities/BiometricEmployee.php
- Modules/Biometric/Entities/BiometricGlobalSetting.php
- Modules/Biometric/Entities/BiometricSetting.php
