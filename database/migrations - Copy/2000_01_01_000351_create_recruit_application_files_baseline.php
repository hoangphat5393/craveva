<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_application_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_application_id` int unsigned NOT NULL,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashname` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `added_by` int unsigned DEFAULT NULL,
  `last_updated_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_application_files_added_by_foreign` (`added_by`),
  KEY `recruit_application_files_last_updated_by_foreign` (`last_updated_by`),
  KEY `recruit_application_files_recruit_job_application_id_foreign` (`recruit_job_application_id`),
  CONSTRAINT `recruit_application_files_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_application_files_last_updated_by_foreign` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `recruit_application_files_recruit_job_application_id_foreign` FOREIGN KEY (`recruit_job_application_id`) REFERENCES `recruit_job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_application_files');
    }
};
