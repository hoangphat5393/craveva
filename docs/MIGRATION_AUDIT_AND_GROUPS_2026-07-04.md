# Migration Audit and Related File Groups

**Date:** 2026-07-04  
**Scope:** Local source and local MySQL only. No staging/hub environment.  
**Mode:** Read-only audit. No migration file or database registry was changed.

## 1. Executive conclusion

The restored migration set fixes the immediate local runtime problem:

- `database/migrations`: **387** PHP files after the 2026 cleanup.
- 2026 migrations: **100 -> 79** files.
- Module migration files: **0**.
- PHP syntax: **387/387 passed**.
- Local migration status: **0 Pending**.
- Migration basenames: no duplicates.
- The local database contains historical migration records whose source
  files are no longer present. Runtime is unaffected, but those migrations
  cannot be rolled back from this repository snapshot.
- One orphan registry row is
  `2000_01_01_000000_prepare_fresh_schema` (batch 95), written before the failed
  consolidated migration reached `accept_estimates`. It is documented here and
  was not manually deleted during this audit.

The source is not yet safe for a new/fresh database because the schema dump and
the restored migration files describe different migration histories:

- `database/schema/mysql-schema.dump` records **506 consolidated `2000_*`
  migrations**.
- The active directory contains **408 historical migrations** and no `2000_*`
  files.
- The dump contains no records for the restored historical migration names.

Do not run `migrate:fresh`, rebuild a customer database, or regenerate the dump
until this contract is resolved and tested on an isolated database.

## 2. Inventory by domain

Every active migration is assigned to one primary group by filename. A file can
still touch tables from another group; table-level chains below take precedence.

| Group | Files | Main scope |
| --- | ---: | --- |
| Sales / Finance | 69 | Estimates, proposals, orders, invoices, payments, contracts |
| Platform / Security | 73 | Company, global settings, modules, permissions, auth, SaaS |
| Other / cross-domain | 62 | Bootstrap, cleanup, shared and mixed migrations |
| HR / Payroll | 55 | Employee, attendance, leave, shift, payroll, recruitment |
| Purchase / Warehouse | 21 | PO, vendor, inventory, warehouse, DO, GRN, stock |
| CRM | 34 | Client, lead, deal and customer data |
| Project / Task | 33 | Projects, tasks, milestones and time logs |
| Product / Pricing | 26 | Products, UOM, price and promotion rules |
| Ticket / Support | 11 | Ticket and support flows |
| Production | 3 | BOM/production-named migrations; see missing module history warning |
| **Total** | **387** | |

## 3. Bootstrap and migration-history group

These files create or repair many tables and are not ordinary one-table
migrations:

- `2018_01_01_000000_create_craveva_new_table.php`
- `2018_01_01_000000_create_craveva_saas_upgrade_new_table.php`
- `2018_02_01_000000_create_craveva_saas_upgrade_fix_table.php`
- `2022_08_12_000000_create_other_migration_till_date_table.php`

The first bootstrap migration creates `accept_estimates` behind a
`companies/organisation_settings` existence guard. It is the historical source
for the table involved in the reported duplicate-table error.

## 4. Main related migration chains

### Estimates and quotation flow

The remaining active custom chain is:

1. `2026_05_06_150500_add_internal_review_columns_to_estimates_table.php`
2. `2026_05_06_151500_add_estimate_id_to_orders_table.php`
3. `2026_05_15_183608_create_estimate_bom_lines_table.php`
4. `2026_05_18_155301_add_estimate_phase1_approver_permissions.php`
5. `2026_05_18_170455_add_estimate_recipe_fields_to_estimates_table.php`
6. `2026_05_18_170926_add_revision_required_to_estimates_status_enum.php`
7. `2026_05_18_171305_create_estimate_approval_events_table.php`
8. `2026_05_20_150947_add_phase1_estimate_tenant_settings.php`

Related tables: `accept_estimates`, `estimate_items`, `estimate_requests`,
`estimate_bom_lines`, `estimate_approval_events`, `estimate_templates`, and
`orders.estimate_id`. The temporary quotation-extra columns and
`estimates.production_bom_id` are no longer part of the current schema.

### Product, UOM and pricing

The `products` table is touched by 13 directly detected migrations. Important
chains:

- Product expansion: `2026_01_21_000000` through `2026_01_21_000002`.
- Product classification: `2026_03_09_000001`, the consolidated
  `2026_03_11_000001` specification migration, `2026_03_11_000003`, and
  `2026_04_01_120000_add_expiry_date_*`.
- Pricing bootstrap: `2026_02_02_120000_force_enable_pricing_module.php` and
  `2026_02_02_140000_setup_pricing_module_core_merged.php`.
- UOM/cost: `2026_05_21_131632_create_product_sku_sequences_table.php`,
  `2026_06_04_124602_add_cost_price_to_product_unit_conversions_table.php`, and
  `2026_06_04_231024_add_cost_from_bom_and_drop_purchase_information_on_products_table.php`.

### Purchase order and warehouse assignment

The intended PO warehouse change is implemented by:

- `2026_03_30_120000_add_warehouse_id_to_purchase_orders_table.php`

Related stock chain:

- `2026_01_19_121416_add_warehouse_id_to_purchase_stock_adjustments_table.php`
- `2026_01_19_125000_update_indices_on_purchase_stock_adjustments_table.php`
- `2026_03_23_120200_add_multi_warehouse_indexes_to_stock_movements_table.php`
- `2026_04_05_120000_drop_psa_product_warehouse_unique_from_purchase_stock_adjustments.php`

### Invoice warehouse assignment

The three empty 2026 placeholders were removed. The current `invoices` table has
`warehouse_id`, but no remaining active migration creates that column. Its source
is therefore historical/module schema or the schema dump. This remains part of
the missing fresh-install contract.

### Delivery Order, GRN and Sales DO

The competing legacy `delivery_orders` and `delivery_order_items` migration chain
was removed because those tables do not exist in the current authoritative local
schema. Canonical runtime tables are `sales_dos`, `sales_do_items`, `grns`, and
`grn_items`, but their original module migration sources are absent from
`Modules/*/Database/Migrations`.

### Production and BOM

Only three active filenames are classified directly as Production/BOM, while the
database contains a wider Production schema. Key current files are:

- `2026_05_15_183608_create_estimate_bom_lines_table.php`
- `2026_06_04_231024_add_cost_from_bom_and_drop_purchase_information_on_products_table.php`

The original Production table migrations exist only in database history/schema
dump, not as module migration files. This is a fresh-install and rollback risk.

## 5. Completed 2026 consolidation

The following eight empty `up()` migrations were removed:

- `2026_01_12_191155_add_fb_fields_to_invoices_and_items.php`
- `2026_01_12_192329_add_fb_fields_to_invoice_items_table.php`
- `2026_01_19_090839_add_warehouse_id_to_purchase_orders_table.php`
- `2026_01_19_091207_add_warehouse_id_to_purchase_inventory_and_stock_adjustments_table.php`
- `2026_01_19_093329_add_warehouse_id_to_invoices_table.php`
- `2026_01_19_094305_add_warehouse_id_to_invoices_table.php`
- `2026_01_19_111432_add_warehouse_id_to_invoices_table.php`
- `2026_01_20_101735_add_warehouse_id_to_purchase_orders_v2.php`

Additional redundant chains removed:

- Legacy `delivery_orders` / `delivery_order_items` create-and-alter chain: 6
  files. These tables do not exist in the authoritative current local schema;
  canonical runtime tables are `grns/grn_items` and `sales_dos/sales_do_items`.
- Product `specification_supplement -> specification`: merged into the original
  add migration and removed the rename migration.
- Purchase terms `delivery_order_terms -> grn_terms -> purchase_terms`:
  consolidated into `2026_05_24_172635_*` while preserving the final
  `grn_terms` and `purchase_terms` columns and the data copy.
- Estimate quotation extras: removed the add-then-drop chain. The recipe
  migration now creates only the three surviving columns: `recipe_moq`,
  `recipe_packaging`, and `recipe_oem_sku`.
- Removed the obsolete `production_bom_id` estimate migration because the
  current `estimates` table does not contain that column.

Total removed: **21** migration files. Modified/merged: **3** files.
No exact duplicate hashes or empty `up()` migrations remain among 2026 files.

## 6. Risk findings

### Critical: schema dump and active migration history disagree

The dump records consolidated migrations that no longer exist, while all active
historical files are absent from the dump registry. Fresh-install behavior is
therefore unverified after the restore.

### High: hard-coded superadmin password migration

`2026_03_04_094500_update_superadmin_password_12345678.php` writes a known
password for `superadmin@example.com`. It has already run locally and has no
rollback. A fresh-install contract must replace this with the interactive
superadmin creation command; do not copy this behavior into new migrations.

### High: weak rollback coverage

- Missing `down()`: **16** files.
- Empty/no-op `down()`: **119** files.
- Files containing `dropColumn`: **137**.
- Files containing table drops: **63**.
- Files containing delete operations: **18**.
- Files containing updates/backfills: **87**.
- Files containing raw SQL: **63**.

Rollback cannot be treated as reliable for this migration history.

### Medium: duplicate timestamps

There are 15 timestamp groups containing more than one migration. Laravel sorts
same-timestamp files lexicographically, but the order is implicit and fragile.
The most important groups are `2018_01_01_000000`, `2026_01_16_000001`,
`2026_01_16_000002`, `2026_01_21_000001`, `2026_01_21_000002`,
`2026_04_01_120000`, and `2026_04_08_120000`.

### Medium: module migration source is absent

No PHP migrations remain under `Modules/*/Database/Migrations`. The local DB can
run because historical module migrations are recorded and their tables exist,
but source-level rollback/replay is incomplete.

Isolated MySQL replay confirmed this limitation. Running the restored historical
migrations without the schema dump stops when
`2026_01_19_121416_add_warehouse_id_to_purchase_stock_adjustments_table.php`
tries to alter the missing `purchase_stock_adjustments` table. There are also no
active create migrations for `purchase_orders`, `purchase_items`,
`purchase_settings`, `grns`, `grn_items`, `sales_dos`, `sales_do_items`,
`production_boms`, or `production_orders`.

## 7. Safe decision and next steps

1. Keep the restored 408 migration files unchanged for the current local DB.
2. Do not run baseline adoption or manually edit the `migrations` table.
   The orphan `2000_01_01_000000_prepare_fresh_schema` row should be handled only
   as part of the final migration-history decision.
3. Choose one fresh-install source of truth:
   - historical migrations plus a matching regenerated dump; or
   - a verified consolidated baseline plus matching dump and transition command.
4. Build an isolated MySQL database and test the exact documented install command.
5. Compare tables, columns, indexes, foreign keys and seed/reference row counts
   against the current local schema.
6. Resolve the two competing `delivery_order_items` definitions and replace the
   hard-coded password migration in the future install path.
7. Only after fresh-install parity passes should obsolete/no-op migration files
   be archived or consolidated. Never rewrite already-applied migrations as an
   upgrade mechanism for an existing customer database.

## 8. Verification evidence

```text
php database/scripts/audit_migrations_registry.php
  387 migration files after consolidation
  0 duplicate basenames
  all active files recorded in DB
  934 historical DB rows without source files

php artisan migrate:check --no-ansi
  0 Pending

php -l database/migrations/*.php
  387 passed
  0 failures

Isolated MySQL replay (schema dump disabled)
  stopped at missing purchase_stock_adjustments create migration
  verification database removed after evidence was captured
```
