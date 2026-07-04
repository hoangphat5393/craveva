<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  `client_id` int unsigned DEFAULT NULL,
  `project_summary` mediumtext COLLATE utf8mb4_unicode_ci,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `feedback` mediumtext COLLATE utf8mb4_unicode_ci,
  `client_view_task` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `allow_client_notification` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `manual_timelog` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `added_by` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `project_templates_company_id_foreign` (`company_id`),
  KEY `project_templates_category_id_foreign` (`category_id`),
  KEY `project_templates_client_id_foreign` (`client_id`),
  KEY `project_templates_sub_category_id_foreign` (`sub_category_id`),
  CONSTRAINT `project_templates_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `project_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_templates_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_templates_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_templates_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `project_sub_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};
