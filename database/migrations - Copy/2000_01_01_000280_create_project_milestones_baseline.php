<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `project_milestones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `project_id` int unsigned DEFAULT NULL,
  `currency_id` int unsigned DEFAULT NULL,
  `milestone_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(30,2) NOT NULL,
  `status` enum('complete','incomplete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `add_to_budget` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `invoice_created` tinyint(1) NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_milestones_project_id_foreign` (`project_id`),
  KEY `project_milestones_currency_id_foreign` (`currency_id`),
  KEY `project_milestones_added_by_foreign` (`added_by`),
  KEY `project_milestones_last_updated_by_foreign` (`last_updated_by`),
  KEY `project_milestones_company_id_index` (`company_id`),
  CONSTRAINT `project_milestones_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_milestones_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_milestones_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `project_milestones_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
