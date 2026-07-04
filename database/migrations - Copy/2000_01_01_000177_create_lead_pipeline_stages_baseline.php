<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `lead_pipeline_stages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lead_pipeline_id` bigint unsigned DEFAULT NULL,
  `pipeline_stages_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_pipeline_stages_lead_pipeline_id_foreign` (`lead_pipeline_id`),
  KEY `lead_pipeline_stages_pipeline_stages_id_foreign` (`pipeline_stages_id`),
  CONSTRAINT `lead_pipeline_stages_lead_pipeline_id_foreign` FOREIGN KEY (`lead_pipeline_id`) REFERENCES `lead_pipelines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_pipeline_stages_pipeline_stages_id_foreign` FOREIGN KEY (`pipeline_stages_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_pipeline_stages');
    }
};
