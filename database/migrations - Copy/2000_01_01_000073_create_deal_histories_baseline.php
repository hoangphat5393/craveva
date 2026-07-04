<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `deal_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `deal_id` bigint unsigned NOT NULL,
  `event_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `deal_stage_from_id` bigint unsigned DEFAULT NULL,
  `file_id` bigint unsigned DEFAULT NULL,
  `task_id` bigint unsigned DEFAULT NULL,
  `follow_up_id` bigint unsigned DEFAULT NULL,
  `note_id` bigint unsigned DEFAULT NULL,
  `proposal_id` bigint unsigned DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deal_stage_to_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deal_histories_deal_id_foreign` (`deal_id`),
  KEY `deal_histories_created_by_foreign` (`created_by`),
  KEY `deal_histories_deal_stage_to_id_foreign` (`deal_stage_to_id`),
  CONSTRAINT `deal_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deal_histories_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deal_histories_deal_stage_to_id_foreign` FOREIGN KEY (`deal_stage_to_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_histories');
    }
};
