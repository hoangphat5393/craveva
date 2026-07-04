# Active historical migrations

The historical migration set was restored on 2026-07-04 after the consolidated
baseline caused an existing database to attempt to recreate tables.

Current local status:

- 387 active PHP migration files in `database/migrations`.
- 79 active files are dated 2026 after duplicate/no-op consolidation.
- 0 PHP migration files under module migration directories.
- Local database: active files are recorded, with 0 `Pending`.

## Fresh-install warning

`database/schema/mysql-schema.dump` still records the retired 506-file
consolidated baseline, not these 408 historical migration names. Do not use the
current source to run `migrate:fresh` or provision a customer database until the
schema dump and active migration history are aligned and verified on an isolated
MySQL database.

Do not manually edit the migration registry and do not rewrite already-applied
migrations to upgrade an existing installation.

See `../../docs/MIGRATION_AUDIT_AND_GROUPS_2026-07-04.md` for the complete audit,
related migration groups and remediation order.
