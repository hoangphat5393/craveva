# Active consolidated migrations

This directory contains the 2026-07-01 consolidated baseline:

- 503 final table-schema migrations.
- 1 prepare migration, 1 finalize migration, and 1 corrective migration.
- 506 active PHP migration files in total.

Use only with a new empty MySQL database:

```bash
php artisan migrate --force
php database/scripts/import_fresh_seed_data.php --input=database/seeders/data/full_20260701
php artisan fresh-install:create-superadmin your-admin@example.com
```

Legacy migrations were removed after consolidation and remain available through
Git history. Module migration directories are intentionally empty so historical
module migrations are not auto-loaded.

`database/schema/mysql-schema.dump` is the canonical Laravel schema dump and
contains all 506 migration records. Regenerate it explicitly with:

```bash
php artisan schema:dump --path=database/schema/mysql-schema.dump
```

See `docs/MIGRATION_CONSOLIDATION_PLAN.md` for verification evidence and limits.
