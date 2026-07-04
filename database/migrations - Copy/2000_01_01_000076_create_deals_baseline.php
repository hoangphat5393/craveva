<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `deals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `column_priority` int NOT NULL DEFAULT '0',
  `lead_pipeline_id` bigint unsigned DEFAULT NULL,
  `pipeline_stage_id` int unsigned DEFAULT NULL,
  `lead_id` int unsigned DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `next_follow_up` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `value` decimal(30,2) DEFAULT '0.00',
  `note` longtext COLLATE utf8mb4_unicode_ci,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `currency_id` int unsigned DEFAULT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deal_watcher` int DEFAULT NULL,
  `create_client` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `deals_company_id_foreign` (`company_id`),
  KEY `deals_lead_pipeline_id_foreign` (`lead_pipeline_id`),
  KEY `deals_pipeline_stage_id_foreign` (`pipeline_stage_id`),
  KEY `deals_lead_id_foreign` (`lead_id`),
  KEY `deals_added_by_foreign` (`added_by`),
  KEY `deals_last_updated_by_foreign` (`last_updated_by`),
  KEY `leads_agent_id_foreign` (`agent_id`),
  KEY `leads_currency_id_foreign` (`currency_id`),
  KEY `deals_category_id_foreign` (`category_id`),
  CONSTRAINT `deals_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deals_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `lead_agents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deals_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `lead_category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deals_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deals_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deals_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deals_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deals_lead_pipeline_id_foreign` FOREIGN KEY (`lead_pipeline_id`) REFERENCES `lead_pipelines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `deals_pipeline_stage_id_foreign` FOREIGN KEY (`pipeline_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
