# SECURITY_REPORT

- Generated at: 2026-05-04T05:35:06+00:00

## Risk signals (heuristics)

### mass_assignment

- Modules/Affiliate/Database/Migrations/2024_05_07_045046_create_affiliate_global_settings_table.php
- Modules/Affiliate/Database/Migrations/2024_05_07_063115_create_affiliate_settings_table.php
- Modules/Affiliate/Database/Migrations/2024_05_08_070003_create_affiliates_table.php
- Modules/Affiliate/Database/Migrations/2024_05_08_080837_create_referrals_table.php
- Modules/Affiliate/Database/Migrations/2024_05_09_090347_create_payouts_table.php
- Modules/Asset/Database/Migrations/2020_01_12_070130_create_asset_types_table.php
- Modules/Asset/Database/Migrations/2020_01_12_070306_create_assets_table.php
- Modules/Asset/Database/Migrations/2020_01_12_084528_create_asset_lending_history_table.php
- Modules/Asset/Database/Migrations/2020_02_21_181854_create_asset_settings_table.php
- Modules/Asset/Http/Controllers/AssetTypeController.php
- Modules/Biolinks/Database/Migrations/2024_02_17_090058_create_biolinks_global_settings_table.php
- Modules/Biolinks/Database/Migrations/2024_03_01_062913_create_biolink_table.php
- Modules/Biolinks/Database/Migrations/2024_03_12_063833_create_biolink_blocks_table.php
- Modules/Biolinks/Database/Seeders/BiolinksDatabaseSeeder.php
- Modules/Biolinks/Http/Controllers/BiolinkBlocksController.php
- Modules/Biometric/Database/Migrations/2024_08_28_090058_create_biometric_global_settings_table.php
- Modules/Biometric/Database/Migrations/2024_08_28_090058_create_biometric_settings_table.php
- Modules/Biometric/Database/Migrations/2024_11_12_113048_create_biometric_devices_table.php
- Modules/Biometric/Database/Migrations/2024_11_13_113406_create_biometric_employees_table.php
- Modules/Biometric/Database/Migrations/2025_05_01_022209_create_biometric_attendances_table.php
- Modules/Biometric/Database/Migrations/2025_05_15_092248_biometric_commands.php
- Modules/Biometric/Entities/BiometricCommands.php
- Modules/Biometric/Entities/BiometricEmployee.php
- Modules/Biometric/Http/Controllers/BiometricDeviceController.php
- Modules/Biometric/Http/Controllers/BiometricEmployeeController.php
- Modules/CyberSecurity/Database/Migrations/2023_11_11_090216_create_cyber_security_settings_table.php
- Modules/CyberSecurity/Database/Migrations/2023_11_22_082732_create_cyber_securities_table.php
- Modules/CyberSecurity/Database/Migrations/2023_11_23_044655_create_blacklist_ips_table.php
- Modules/CyberSecurity/Database/Migrations/2023_11_23_110035_create_blacklist_emails_table.php
- Modules/CyberSecurity/Database/Migrations/2023_11_23_164003_create_login_expiries_table.php
- Modules/CyberSecurity/Http/Controllers/BlacklistEmailController.php
- Modules/CyberSecurity/Http/Controllers/BlacklistIpController.php
- Modules/DeveloperTools/Database/Migrations/2026_02_24_113210_create_db_user_mapping_table.php
- Modules/DeveloperTools/Database/Migrations/2026_02_24_113216_create_developer_tools_credentials_table.php
- Modules/DeveloperTools/Database/Migrations/2026_02_28_103201_create_developer_tools_files_table.php
- Modules/DeveloperTools/Database/Migrations/2026_02_28_103207_create_developer_tools_dependencies_table.php
- Modules/DeveloperTools/Database/Migrations/2026_03_06_000002_create_developer_tools_db_access_logs_table.php
- Modules/DeveloperTools/Http/Controllers/DeveloperToolsController.php
- Modules/DeveloperTools/Services/FileScanner.php
- Modules/EInvoice/Database/Migrations/2023_11_03_071005_create_e_invoice_settings_table.php

### raw_sql

- Modules/Affiliate/Http/Controllers/DashBoardController.php
- Modules/Asset/Database/Migrations/2020_03_08_065037_add_lender_column.php
- Modules/Asset/Database/Migrations/2023_05_19_070306_add_lost_status_table.php
- Modules/DeveloperTools/Console/SetupDatabase.php
- Modules/DeveloperTools/Http/Controllers/DeveloperToolsController.php
- Modules/Onboarding/Database/Migrations/2025_08_20_115952_improve_onboarding_structure.php
- Modules/Payroll/DataTables/PayrollDataTable.php
- Modules/Payroll/Database/Migrations/2022_08_18_071222_alter_in_salary_components_table.php
- Modules/Payroll/Database/Migrations/2022_11_24_071222_alter_currency_id_payroll_setting_table.php
- Modules/Payroll/Exports/SalaryCumulativeReport.php
- Modules/Payroll/Exports/SalaryMonthlyReport.php
- Modules/Payroll/Http/Controllers/PayrollController.php
- Modules/Performance/Database/Migrations/2025_02_21_173930_add_columns_in_performance_settings_table.php
- Modules/Performance/Database/Migrations/2025_04_01_070938_add_next_check_in_date.php
- Modules/Policy/DataTables/ArchivePolicyDataTable.php
- Modules/Policy/DataTables/PolicyDataTable.php
- Modules/Policy/Database/Migrations/2024_06_18_065451_change_file_column_name_in_policies_table.php
- Modules/Policy/Http/Controllers/PolicyFileController.php
- Modules/Pricing/Database/Migrations/2026_01_30_160749_update_company_pricing_to_use_clients.php
- Modules/Pricing/Database/Migrations/2026_02_11_121332_add_start_and_end_date_to_client_product_pricing_table.php
- Modules/ProjectRoadmap/DataTables/ProjectRoadmapDataTable.php
- Modules/ProjectRoadmap/DataTables/ProjectTasksDataTable.php
- Modules/Purchase/DataTables/DeliveryOrderDataTable.php
- Modules/Purchase/DataTables/PurchaseInventoryDataTable.php
- Modules/Purchase/DataTables/SalesShipmentDataTable.php
- Modules/Purchase/Database/Migrations/2024_04_29_122517_create_purchase_orders_table.php
- Modules/Purchase/Database/Migrations/2026_02_02_150000_setup_purchase_custom_fields_merged.php
- Modules/Purchase/Database/Migrations/2026_03_07_100000_add_inventory_datatable_performance_indexes.php
- Modules/Purchase/Http/Controllers/PurchaseBillController.php
- Modules/Purchase/Http/Controllers/PurchaseOrderController.php
- Modules/Purchase/Http/Controllers/PurchaseOrderFileController.php
- Modules/Purchase/Http/Controllers/PurchaseVendorController.php
- Modules/Purchase/Http/Controllers/SalesShipmentController.php
- Modules/Recruit/Database/Migrations/2022_07_18_082515_add_column_in_recruit_job_applications_table.php
- Modules/Recruit/Database/Migrations/2022_09_15_061628_create_recruit_job_category.php
- Modules/Recruit/Database/Migrations/2023_01_23_082501_add_column_in_recruit_settings_table.php
- Modules/Recruit/Database/Migrations/2023_02_03_121345_create_recruit_salary_structure_table.php
- Modules/Recruit/Database/Migrations/2024_01_12_121345_change_recruit_job_experience_table.php
- Modules/Recruit/Database/Migrations/2024_10_23_121345_change_source_id_table.php
- Modules/Recruit/Database/Migrations/2025_02_11_084438_add_foreign_key_to_recruit_jobs.php

### file_ops

- Modules/Policy/Http/Controllers/PolicyController.php
- Modules/Purchase/Console/GrnMigrateDataCommand.php
- Modules/Purchase/Console/SalesDoMigrateDataCommand.php
- Modules/Purchase/Console/SalesDoMigrationRehearsalCommand.php
- Modules/Purchase/Console/SalesDoReconciliationReportCommand.php
- app/Console/Commands/ConvertTaskBase64Images.php
- app/Exceptions/Handler.php
- app/Helper/Files.php
- app/Http/Controllers/CreditNoteController.php
- app/Http/Controllers/GdprController.php
- app/Http/Controllers/InvoiceController.php
- app/Http/Controllers/StorageSettingController.php
- app/Notifications/InvoiceUpdated.php
- app/Notifications/NewInvoice.php

### api_keys

- Modules/Biolinks/Database/Migrations/2024_03_28_113432_add_columns_to_biolink_blocks_table.php
- Modules/Biolinks/Http/Controllers/BiolinkBlocksController.php
- Modules/Biolinks/Http/Controllers/BiolinkPageController.php
- Modules/Biolinks/Http/Requests/UpdateBiolinkBlocks.php
- Modules/Biolinks/Resources/views/biolink-blocks/edit/email-collector-form.blade.php
- Modules/Biolinks/Resources/views/biolink-page/index.blade.php
- Modules/Recruit/Traits/ZoomSettings.php
- Modules/ServerManager/Config/servermanager.php
- Modules/ServerManager/Services/DnsLookupService.php
- Modules/Sms/Database/Migrations/2020_07_07_085510_create_twilio_settings_table.php
- Modules/Sms/Http/Controllers/SmsSettingsController.php
- Modules/Sms/Http/Requests/StoreSmsSetting.php
- Modules/Sms/Http/Traits/SmsSettingTrait.php
- Modules/Sms/Resources/views/sms/index.blade.php
- Modules/Zoom/Console/SendMeetingReminder.php
- Modules/Zoom/Database/Migrations/2020_09_07_100311_create_zoomsetting_table.php
- Modules/Zoom/Database/Migrations/2023_05_29_102526_add_account_id_coloumn_in_zoom_setting_table.php
- Modules/Zoom/Entities/ZoomSetting.php
- Modules/Zoom/Http/Requests/ZoomMeeting/UpdateSetting.php
- Modules/Zoom/Resources/views/meeting-calendar/start_meeting.blade.php
- Modules/Zoom/Resources/views/meeting/ajax/create.blade.php
- Modules/Zoom/Resources/views/meeting/start_meeting.blade.php
- Modules/Zoom/Resources/views/notification-settings/ajax/zoom-setting.blade.php
- Modules/Zoom/Traits/ZoomSettingsTrait.php
- app/Console/Commands/VerifyStripePaymentEnvironmentCommand.php
- app/Helper/Common.php
- app/Helper/start.php
- app/Http/Controllers/AppSettingController.php
- app/Http/Controllers/GoogleCalendarSettingController.php
- app/Http/Controllers/PaymentGatewayCredentialController.php
- app/Http/Controllers/PushNotificationController.php
- app/Http/Controllers/QuickbookController.php
- app/Http/Controllers/QuickbookSettingsController.php
- app/Http/Controllers/SecuritySettingController.php
- app/Http/Controllers/SuperAdmin/PaymentGatewayCredentialController.php
- app/Http/Requests/Admin/App/UpdateAiWorkspaceSetting.php
- app/Http/Requests/GoogleCalenderSetting/StoreGoogleCalender.php
- app/Http/Requests/GoogleCaptcha/UpdateGoogleCaptchaSetting.php
- app/Http/Requests/PaymentGateway/UpdateGatewayCredentials.php
- app/Http/Requests/PushSetting/UpdateRequest.php

### command_exec

- Modules/Biolinks/Resources/assets/js/ace/ace.js
- app/Http/Controllers/DatabaseBackupSettingController.php
- app/Http/Controllers/SuperAdmin/PayFastWebhookController.php
- resources/js/jquery.min.js

### crypto_decrypt

- Modules/ServerManager/Entities/ServerDomain.php
- Modules/ServerManager/Entities/ServerHosting.php
- app/Http/Controllers/TwoFASettingController.php
- app/Models/UserAuth.php

