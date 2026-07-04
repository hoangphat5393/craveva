<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `performance_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `create_meeting_roles` text COLLATE utf8mb4_unicode_ci,
  `create_meeting_manager` tinyint(1) NOT NULL DEFAULT '0',
  `create_meeting_participant` tinyint(1) NOT NULL DEFAULT '0',
  `view_meeting_roles` text COLLATE utf8mb4_unicode_ci,
  `view_meeting_manager` tinyint(1) NOT NULL DEFAULT '0',
  `view_meeting_participant` tinyint(1) NOT NULL DEFAULT '0',
  `objective_slack_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `objective_push_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `objective_email_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `meeting_slack_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `meeting_push_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `meeting_email_notification` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_settings_company_id_foreign` (`company_id`),
  CONSTRAINT `performance_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_settings');
    }
};
