<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `leave_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `type_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_of_leaves` double DEFAULT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '1',
  `monthly_limit` decimal(10,2) DEFAULT NULL,
  `effective_after` int DEFAULT NULL,
  `effective_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unused_leave` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `encashed` tinyint(1) NOT NULL,
  `allowed_probation` tinyint(1) NOT NULL,
  `allowed_notice` tinyint(1) NOT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` longtext COLLATE utf8mb4_unicode_ci,
  `designation` longtext COLLATE utf8mb4_unicode_ci,
  `role` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `leavetype` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `over_utilization` enum('not_allowed','allow_paid','allow_unpaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_allowed',
  PRIMARY KEY (`id`),
  KEY `leave_types_company_id_foreign` (`company_id`),
  CONSTRAINT `leave_types_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
