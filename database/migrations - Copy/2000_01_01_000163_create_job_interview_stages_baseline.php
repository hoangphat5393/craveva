<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `job_interview_stages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_id` bigint unsigned NOT NULL,
  `recruit_interview_stage_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_interview_stages_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `jis_recruit_interview_stage_id_foreign` (`recruit_interview_stage_id`),
  CONSTRAINT `jis_recruit_interview_stage_id_foreign` FOREIGN KEY (`recruit_interview_stage_id`) REFERENCES `recruit_interview_stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `job_interview_stages_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('job_interview_stages');
    }
};
