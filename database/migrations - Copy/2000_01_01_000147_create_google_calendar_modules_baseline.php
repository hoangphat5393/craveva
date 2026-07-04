<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `google_calendar_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `lead_status` tinyint(1) NOT NULL DEFAULT '0',
  `leave_status` tinyint(1) NOT NULL DEFAULT '0',
  `invoice_status` tinyint(1) NOT NULL DEFAULT '0',
  `contract_status` tinyint(1) NOT NULL DEFAULT '0',
  `task_status` tinyint(1) NOT NULL DEFAULT '0',
  `event_status` tinyint(1) NOT NULL DEFAULT '0',
  `holiday_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `google_calendar_modules_company_id_foreign` (`company_id`),
  CONSTRAINT `google_calendar_modules_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_modules');
    }
};
