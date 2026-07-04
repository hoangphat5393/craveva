<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `onboarding_completed_task` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `onboarding_task_id` int unsigned NOT NULL,
  `type` enum('onboard','offboard') COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` int unsigned NOT NULL,
  `completed_on` date DEFAULT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submission_status` enum('pending','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submitted_on` timestamp NULL DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `approved_on` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `rejected_by` int unsigned DEFAULT NULL,
  `rejected_on` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_completed_task_onboarding_task_id_foreign` (`onboarding_task_id`),
  KEY `onboarding_completed_task_employee_id_foreign` (`employee_id`),
  KEY `onboarding_completed_task_user_id_foreign` (`user_id`),
  KEY `onboarding_completed_task_approved_by_foreign` (`approved_by`),
  KEY `onboarding_completed_task_rejected_by_foreign` (`rejected_by`),
  CONSTRAINT `onboarding_completed_task_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `onboarding_completed_task_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `onboarding_completed_task_onboarding_task_id_foreign` FOREIGN KEY (`onboarding_task_id`) REFERENCES `onboarding_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `onboarding_completed_task_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `onboarding_completed_task_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_completed_task');
    }
};
