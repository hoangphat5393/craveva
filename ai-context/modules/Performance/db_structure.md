# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### check_ins

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### goal_types

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### key_results

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### key_results_metrics

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### objective_owners

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### objective_progress_statuses

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### objectives

- Columns: barriers, check_in_by, check_in_date, check_in_frequency, color, company_id, condition, confidence_level, created_by, current_value, department_id, description, end_date, key_percentage, last_check_in, manage_by_manager, manage_by_owner, manage_by_roles, metrics_id, name, objective_percentage, objective_progress, original_current_value, owner_id, priority, progress_update, rotation_date, schedule_on, send_check_in_reminder, start_date, status, target_value, time_left_percentage, title, type, view_by_manager, view_by_owner, view_by_roles
- Migrations: 2024_09_22_061728_create_goal_types_table.php

### performance_global_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_09_19_082743_create_performance_global_settings_table.php

### performance_meeting_actions

- Columns: action_point, added_by, company_id, discussion_point, is_actioned, is_discussed, meeting_by, meeting_for, meeting_id, parent_id, repeat, repeat_cycles, repeat_every, repeat_type, status, until_date
- Migrations: 2025_01_03_085054_create_performance_meeting_table.php

### performance_meeting_agenda

- Columns: action_point, added_by, company_id, discussion_point, is_actioned, is_discussed, meeting_by, meeting_for, meeting_id, parent_id, repeat, repeat_cycles, repeat_every, repeat_type, status, until_date
- Migrations: 2025_01_03_085054_create_performance_meeting_table.php

### performance_meetings

- Columns: action_point, added_by, company_id, discussion_point, is_actioned, is_discussed, meeting_by, meeting_for, meeting_id, parent_id, repeat, repeat_cycles, repeat_every, repeat_type, status, until_date
- Migrations: 2025_01_03_085054_create_performance_meeting_table.php

### performance_settings

- Columns: company_id, send_notification
- Migrations: 2024_09_19_082743_create_performance_settings_table.php

## Entities (table + casts)

- Modules/Performance/Entities/Action.php (table=performance_meeting_actions)
- Modules/Performance/Entities/Agenda.php (table=performance_meeting_agenda)
- Modules/Performance/Entities/CheckIn.php
- Modules/Performance/Entities/Dashboard.php
- Modules/Performance/Entities/GoalType.php
- Modules/Performance/Entities/KeyResults.php (table=key_results)
- Modules/Performance/Entities/KeyResultsMetrics.php (table=key_results_metrics)
- Modules/Performance/Entities/Meeting.php (table=performance_meetings)
- Modules/Performance/Entities/Objective.php
- Modules/Performance/Entities/ObjectiveOwner.php (table=objective_owners)
- Modules/Performance/Entities/ObjectiveProgressStatus.php
- Modules/Performance/Entities/OkrScoring.php
- Modules/Performance/Entities/PerformanceGlobalSetting.php
- Modules/Performance/Entities/PerformanceSetting.php
