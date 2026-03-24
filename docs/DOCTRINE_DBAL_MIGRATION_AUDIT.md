# Doctrine DBAL Migration Audit

_Generated on 2026-03-24 and updated after Phase 1 + DBAL removal._

## Baseline Counts (before Phase 1)

- `->change(...)`: **43** files
- `->renameColumn(...)`: **13** files
- Unique migration files using at least one of the above: **52**

## Current Counts (after Phase 3 complete - all migration batches)

- `->change(...)`: **0** files
- `->renameColumn(...)`: **0** files
- Phase 2 rename hotspots: **completed**
- Phase 3 (2026 group): **3 files refactored**
- Phase 3 (2025 group): **5 files refactored**
- Phase 3 (2024 group): **12 files refactored**
- Phase 3 (2023 group): **14 files refactored (complete)**
- Phase 3 (2022 group): **8 files refactored (complete)**
- Legacy 2018 hotspot: **1 file refactored (complete)**

## Files

| File                                                                                                                | Pattern                  |
| ------------------------------------------------------------------------------------------------------------------- | ------------------------ |
| `database/migrations/2018_02_01_000000_create_craveva_saas_upgrade_fix_table.php`                                   | `change`, `renameColumn` |
| `database/migrations/2022_08_23_065943_change_status_type_projects_table.php`                                       | `change`                 |
| `database/migrations/2022_08_25_085025_add_other_location_to_attendances_table.php`                                 | `change`                 |
| `database/migrations/2022_09_01_000000_add_company_id_in_all_table.php`                                             | `change`                 |
| `database/migrations/2022_09_01_083053_create_global_settings_table.php`                                            | `renameColumn`           |
| `database/migrations/2022_09_23_053942_update_type_of_hsn_sac_code_to_proposal_template_items.php`                  | `change`                 |
| `database/migrations/2022_10_03_080325_create_super_admin_tables_table.php`                                         | `change`, `renameColumn` |
| `database/migrations/2022_10_31_130459_order_with_order_number.php`                                                 | `change`                 |
| `database/migrations/2022_12_01_070705_create_leave_files_table.php`                                                | `change`                 |
| `database/migrations/2022_12_29_084526_create_subscriptions_table.php`                                              | `renameColumn`           |
| `database/migrations/2022_12_30_090615_move_google_map_key.php`                                                     | `change`                 |
| `database/migrations/2023_01_05_084453_add_column_in_log_time_table.php`                                            | `change`                 |
| `database/migrations/2023_01_09_162235_create_estimate_templates_table.php`                                         | `change`                 |
| `database/migrations/2023_01_11_080325_create_super_admin_paystack_subscription_tables_table.php`                   | `change`                 |
| `database/migrations/2023_03_05_065728_missing_data_fix.php`                                                        | `change`, `renameColumn` |
| `database/migrations/2023_03_21_120725_fix_global_invoices_saas_fix.php`                                            | `change`                 |
| `database/migrations/2023_05_02_100907_fix_bug.php`                                                                 | `change`                 |
| `database/migrations/2023_06_22_070900_fix_module_is_superadmin.php`                                                | `change`                 |
| `database/migrations/2023_06_28_120547_alter_description_column_in_expenses_table.php`                              | `change`                 |
| `database/migrations/2023_09_25_055948_add_column_for_number_with_prefix.php`                                       | `change`                 |
| `database/migrations/2023_10_12_043341_fix_estimate_item_images_column.php`                                         | `change`                 |
| `database/migrations/2023_11_21_112750_add_permissions_for_leave_reports.php`                                       | `change`                 |
| `database/migrations/2023_11_23_065925_alter_sign_date_contracts.php`                                               | `change`                 |
| `database/migrations/2023_12_19_091940_change_column_type_in_order_items_table.php`                                 | `change`                 |
| `database/migrations/2024_01_04_114740_description_type_on_tr_front_details_table.php`                              | `change`                 |
| `database/migrations/2024_01_11_113519_update_marital_status_enum.php`                                              | `change`                 |
| `database/migrations/2024_01_29_052114_lead_changes.php`                                                            | `renameColumn`           |
| `database/migrations/2024_02_02_114946_lead-files_changes_for_deals.php`                                            | `renameColumn`           |
| `database/migrations/2024_03_29_054907_add_webhook_secret_razorpay_superadmin.php`                                  | `renameColumn`           |
| `database/migrations/2024_04_15_112542_password_encrypt.php`                                                        | `change`                 |
| `database/migrations/2024_04_17_064540_add_add_to_budget_column_in_proj_table.php`                                  | `change`                 |
| `database/migrations/2024_05_06_050805_modify_lead_notes_table.php`                                                 | `change`                 |
| `database/migrations/2024_07_12_053537_change_datatype_of_approvalsend_column.php`                                  | `change`                 |
| `database/migrations/2024_07_31_12573412_insert_ticket_setting.php`                                                 | `change`                 |
| `database/migrations/2024_09_05_051501_change_title_field_nullabe_to_deal_notes_table.php`                          | `change`                 |
| `database/migrations/2024_09_11_060652_fix_flexible_shift_columns.php`                                              | `change`                 |
| `database/migrations/2024_09_19_122038_update_expense_exchange_rate_column_value.php`                               | `change`                 |
| `database/migrations/2024_09_23_081839_alter_data_type_of_task_table_columns.php`                                   | `change`                 |
| `database/migrations/2024_10_19_092843_recalculate_leaves.php`                                                      | `change`                 |
| `database/migrations/2024_11_04_121543_add_deal_stage_from_id_to_deal_histories.php`                                | `renameColumn`           |
| `database/migrations/2025_10_01_084513_add_alias_column_to_project_status_settings_table.php`                       | `change`                 |
| `database/migrations/2026_02_09_000001_ensure_product_prices_nullable.php`                                          | `change`                 |
| `database/migrations/2026_03_11_000002_rename_specification_supplement_to_specification_in_products_table.php`      | `renameColumn`           |
| `Modules/Performance/Database/Migrations/2025_02_21_173930_add_columns_in_performance_settings_table.php`           | `renameColumn`           |
| `Modules/Performance/Database/Migrations/2025_04_01_070938_add_next_check_in_date.php`                              | `change`                 |
| `Modules/Policy/Database/Migrations/2024_06_18_065451_change_file_column_name_in_policies_table.php`                | `renameColumn`           |
| `Modules/Pricing/Database/Migrations/2026_01_30_160749_update_company_pricing_to_use_clients.php`                   | `change`, `renameColumn` |
| `Modules/Pricing/Database/Migrations/2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php`  | `change`                 |
| `Modules/Recruit/Database/Migrations/2023_01_10_100953_create_custom_questions_table.php`                           | `change`                 |
| `Modules/Recruit/Database/Migrations/2025_02_11_084438_add_foreign_key_to_recruit_jobs.php`                         | `change`                 |
| `Modules/ServerManager/Database/Migrations/2025_09_05_105835_update_server_hosting_domain_created_by_nullable.php`  | `change`                 |
| `Modules/ServerManager/Database/Migrations/2025_09_05_111942_modify_created_by_column_in_server_hostings_table.php` | `change`                 |

## Conclusion

`doctrine/dbal` has now been removed from the project dependencies. Remaining migration patterns still need phased refactoring and validation on staging-like databases to avoid cross-driver issues.

## Next Execution Plan (Safe Order)

### Phase 1 - Lowest risk first (`rename_only`)

Goal: verify each migration can run without DBAL-specific behavior before touching high-risk `change`.

- `database/migrations/2026_03_11_000002_rename_specification_supplement_to_specification_in_products_table.php`
- `database/migrations/2024_11_04_121543_add_deal_stage_from_id_to_deal_histories.php`
- `Modules/Performance/Database/Migrations/2025_02_21_173930_add_columns_in_performance_settings_table.php`
- `Modules/Policy/Database/Migrations/2024_06_18_065451_change_file_column_name_in_policies_table.php`
- `database/migrations/2024_03_29_054907_add_webhook_secret_razorpay_superadmin.php`
- `database/migrations/2024_02_02_114946_lead-files_changes_for_deals.php`
- `database/migrations/2024_01_29_052114_lead_changes.php`
- `database/migrations/2022_12_29_084526_create_subscriptions_table.php`
- `database/migrations/2022_09_01_083053_create_global_settings_table.php`

### Phase 2 - `both` files (hardest hotspots)

- `database/migrations/2018_02_01_000000_create_craveva_saas_upgrade_fix_table.php`
- `database/migrations/2022_10_03_080325_create_super_admin_tables_table.php`
- `database/migrations/2023_03_05_065728_missing_data_fix.php`
- `Modules/Pricing/Database/Migrations/2026_01_30_160749_update_company_pricing_to_use_clients.php`

### Phase 3 - Remaining `change_only`

Run by newest-to-oldest first for better rollback confidence:

1. 2026 group
2. 2025 group
3. 2024 group
4. 2023 group
5. 2022 group

### Validation Gate after each phase

1. Run migration checks on staging-like DB engine (no data reset).
2. Smoke test core flows (login, order, invoice, warehouse transfer).
3. If green, continue next phase.
4. After each phase, re-validate migration behavior and app smoke flows.

## Phase 1 Status

- Status: **implemented in code** and validated on staging run.
- Updated migrations now use a driver-aware raw SQL rename path (`mysql`/`mariadb`/`pgsql`/`sqlsrv`) instead of direct `$table->renameColumn(...)`.
- `renameColumn(...)` references were removed from all 9 Phase 1 files.
- Next action: start Phase 2 hotspot refactor.

## Current Residual Risk

- Remaining direct `renameColumn(...)` usage: **0 files**.
- Remaining `change(...)` usage: **0 files**.
- On MySQL staging this is currently operating, but fresh environment compatibility should still be verified as phases continue.
