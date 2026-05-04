# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### db_user_mapping

- Columns: company_id, db_username
- Migrations: 2026_02_24_113210_create_db_user_mapping_table.php

### developer_tools_credentials

- Columns: company_id, created_by, db_database, db_host, db_port, db_username
- Migrations: 2026_02_24_113216_create_developer_tools_credentials_table.php

### developer_tools_db_access_logs

- Columns: allowed_tables_count, company_id, created_by, created_views_count, db_database, db_username, duration_ms, error_message, requested_modules, status, warnings
- Migrations: 2026_03_06_000002_create_developer_tools_db_access_logs_table.php

### developer_tools_dependencies

- Columns: depends_on_file_id, file_id, relation_type
- Migrations: 2026_02_28_103207_create_developer_tools_dependencies_table.php

### developer_tools_files

- Columns: extra, framework, hash, language, last_modified_at, module, name, path, role, version
- Migrations: 2026_02_28_103201_create_developer_tools_files_table.php

## Entities (table + casts)

- Modules/DeveloperTools/Entities/DbAccessLog.php (table=developer_tools_db_access_logs)
- Modules/DeveloperTools/Entities/DbUserMapping.php (table=db_user_mapping)
- Modules/DeveloperTools/Entities/DeveloperToolsCredential.php (table=developer_tools_credentials)
- Modules/DeveloperTools/Entities/FileDependency.php (table=developer_tools_dependencies)
- Modules/DeveloperTools/Entities/FileRecord.php (table=developer_tools_files)
