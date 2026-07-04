# Consolidated reference data: full

Generated from a clean migration replay followed by the production installer
seeders. User accounts and authentication credentials are excluded.

Import only after the matching consolidated schema has been migrated:

```bash
php database/scripts/import_fresh_seed_data.php --input=database/seeders/data/full_20260701
```

The importer refuses non-empty target tables and verifies the manifest checksum
and imported rows. Do not import this data into an existing installation.
