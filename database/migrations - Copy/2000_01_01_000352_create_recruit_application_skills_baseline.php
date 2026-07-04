<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_application_skills` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_application_id` int unsigned NOT NULL,
  `recruit_skill_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_application_skills_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  KEY `recruit_application_skills_recruit_skill_id_foreign` (`recruit_skill_id`),
  CONSTRAINT `recruit_application_skills_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_application_skills_recruit_skill_id_foreign` FOREIGN KEY (`recruit_skill_id`) REFERENCES `recruit_skills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_application_skills');
    }
};
