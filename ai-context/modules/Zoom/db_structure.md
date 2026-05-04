# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### user_zoom_meeting

- Columns: user_id, zoom_meeting_id
- Migrations: 2020_09_01_074717_create_user_zoom_meeting_table.php

### zoom_categories

- Columns: category_name
- Migrations: 2020_12_17_101924_create_zoom_category_table.php

### zoom_global_settings

- Columns: license_type, purchase_code, supported_until
- Migrations: 2022_09_01_000000_create_zoom_global_settings.php

### zoom_meeting_notes

- Columns: added_by, company_id, last_updated_by, note, user_id, zoom_meeting_id
- Migrations: 2023_01_06_072523_create_zoom_meeting_notes_table.php

### zoom_meetings

- Columns: created_by, end_date_time, host_video, join_link, label_color, meeting_id, meeting_name, participant_video, project_id, remind_time, remind_type, repeat, repeat_cycles, repeat_every, repeat_type, send_reminder, start_date_time, start_link, status
- Migrations: 2020_09_01_051350_create_zoom_meetings_table.php

### zoom_notification_settings

- Columns: company_id, send_email, send_slack, setting_name, slug
- Migrations: 2023_02_16_064145_create_zoom_notification_settings_table.php

### zoom_setting

- Columns: api_key, secret_key
- Migrations: 2020_09_07_100311_create_zoomsetting_table.php

## Entities (table + casts)

- Modules/Zoom/Entities/ZoomCategory.php (table=zoom_categories)
- Modules/Zoom/Entities/ZoomGlobalSetting.php
- Modules/Zoom/Entities/ZoomMeeting.php (table=zoom_meetings)
- Modules/Zoom/Entities/ZoomMeetingNote.php (table=zoom_meeting_notes)
- Modules/Zoom/Entities/ZoomNotificationSetting.php (table=zoom_notification_settings)
- Modules/Zoom/Entities/ZoomSetting.php (table=zoom_setting)
