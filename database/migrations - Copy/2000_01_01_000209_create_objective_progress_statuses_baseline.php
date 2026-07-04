<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `objective_progress_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `objective_id` bigint unsigned NOT NULL,
  `objective_progress` decimal(5,2) NOT NULL,
  `time_left_percentage` decimal(5,2) NOT NULL,
  `status` enum('onTrack','atRisk','offTrack','completed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` enum('success','warning','danger','primary') COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objective_progress_statuses_objective_id_foreign` (`objective_id`),
  CONSTRAINT `objective_progress_statuses_objective_id_foreign` FOREIGN KEY (`objective_id`) REFERENCES `objectives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('objective_progress_statuses');
    }
};
