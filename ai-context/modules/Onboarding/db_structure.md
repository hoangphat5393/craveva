# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### onboarding_completed_task

- Columns: added_by, column_priority, company_id, completed_on, employee_can_see, employee_id, file, offboard_completed, onboard_completed, onboarding_status, onboarding_task_id, status, task_for, title, type, user_id
- Migrations: 2024_04_26_115952_create_onboarding_tasks_table.php

### onboarding_notification_settings

- Columns: company_id, send_email, setting_name, slug
- Migrations: 2024_11_22_115952_onboard_email_notification.php

### onboarding_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_01_01_00001_create_onboarding_settings_table.php

### onboarding_tasks

- Columns: added_by, column_priority, company_id, completed_on, employee_can_see, employee_id, file, offboard_completed, onboard_completed, onboarding_status, onboarding_task_id, status, task_for, title, type, user_id
- Migrations: 2024_04_26_115952_create_onboarding_tasks_table.php

## Entities (table + casts)

- Modules/Onboarding/Entities/OnboardingCompletedTask.php (table=onboarding_completed_task)
- Modules/Onboarding/Entities/OnboardingNotificationSetting.php
- Modules/Onboarding/Entities/OnboardingSetting.php
- Modules/Onboarding/Entities/OnboardingTask.php (table=onboarding_tasks)
