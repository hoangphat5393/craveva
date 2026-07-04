<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `overtime_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `overtime_policy_id` bigint unsigned NOT NULL,
  `date` date DEFAULT NULL,
  `hours` double NOT NULL DEFAULT '0',
  `minutes` double NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `overtime_reason` text COLLATE utf8mb4_unicode_ci,
  `type` enum('working','holiday','dayoff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'working',
  `status` enum('accept','reject','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `save_type` enum('draft','save') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `action_by` int unsigned DEFAULT NULL,
  `batch_key` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtime_requests_company_id_foreign` (`company_id`),
  KEY `overtime_requests_user_id_foreign` (`user_id`),
  KEY `overtime_requests_overtime_policy_id_foreign` (`overtime_policy_id`),
  KEY `overtime_requests_action_by_foreign` (`action_by`),
  CONSTRAINT `overtime_requests_action_by_foreign` FOREIGN KEY (`action_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `overtime_requests_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `overtime_requests_overtime_policy_id_foreign` FOREIGN KEY (`overtime_policy_id`) REFERENCES `overtime_policies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `overtime_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
