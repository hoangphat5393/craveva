<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `onboarding_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int unsigned NOT NULL,
  `task_for` enum('company','employee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `employee_can_see` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('onboard','offboard') COLLATE utf8mb4_unicode_ci NOT NULL,
  `column_priority` int NOT NULL,
  `added_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_tasks_company_id_foreign` (`company_id`),
  KEY `onboarding_tasks_added_by_foreign` (`added_by`),
  CONSTRAINT `onboarding_tasks_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `onboarding_tasks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_tasks');
    }
};
