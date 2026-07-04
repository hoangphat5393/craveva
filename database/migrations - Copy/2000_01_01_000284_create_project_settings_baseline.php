<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `send_reminder` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remind_time` int NOT NULL,
  `remind_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remind_to` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '["admins","members"]',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `project_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_settings');
    }
};
