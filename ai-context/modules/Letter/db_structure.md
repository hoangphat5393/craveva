# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### letter_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2023_11_15_041005_create_letter_global_settings_table.php

### letter_templates

- Columns: company_id, description, title
- Migrations: 2023_11_15_050543_create_letter_templates_table.php

### letters

- Columns: bottom, company_id, creator_id, description, left, name, right, template_id, top, user_id
- Migrations: 2023_11_15_081011_create_letters_table.php

## Entities (table + casts)

- Modules/Letter/Entities/Letter.php
- Modules/Letter/Entities/LetterSetting.php
- Modules/Letter/Entities/Template.php (table=letter_templates)
