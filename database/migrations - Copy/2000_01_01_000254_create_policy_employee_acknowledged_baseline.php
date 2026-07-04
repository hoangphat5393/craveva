<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `policy_employee_acknowledged` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `policy_id` int unsigned DEFAULT NULL,
  `signature_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acknowledged_on` datetime DEFAULT NULL,
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `policy_employee_acknowledged_company_id_foreign` (`company_id`),
  KEY `policy_employee_acknowledged_user_id_foreign` (`user_id`),
  KEY `policy_employee_acknowledged_policy_id_foreign` (`policy_id`),
  CONSTRAINT `policy_employee_acknowledged_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `policy_employee_acknowledged_policy_id_foreign` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `policy_employee_acknowledged_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_employee_acknowledged');
    }
};
