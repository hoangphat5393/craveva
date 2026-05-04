# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### asset_lending_history

- Columns: asset_id, date_given, date_of_return, notes, return_date, user_id
- Migrations: 2020_01_12_084528_create_asset_lending_history_table.php

### asset_settings

- Columns: purchase_code, supported_until
- Migrations: 2020_02_21_181854_create_asset_settings_table.php

### asset_types

- Columns: name
- Migrations: 2020_01_12_070130_create_asset_types_table.php

### assets

- Columns: asset_type_id, description, name, serial_number, status
- Migrations: 2020_01_12_070306_create_assets_table.php

## Entities (table + casts)

- Modules/Asset/Entities/Asset.php (table=assets)
- Modules/Asset/Entities/AssetHistory.php (table=asset_lending_history)
- Modules/Asset/Entities/AssetSetting.php (table=asset_settings)
- Modules/Asset/Entities/AssetType.php (table=asset_types)
