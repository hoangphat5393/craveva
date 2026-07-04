<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `biometric_employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `force_biometric_clockin` tinyint(1) NOT NULL DEFAULT '1',
  `biometric_employee_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned NOT NULL,
  `has_photo` tinyint(1) NOT NULL DEFAULT '0',
  `photo` text COLLATE utf8mb4_unicode_ci,
  `card_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_fingerprint` tinyint(1) NOT NULL DEFAULT '0',
  `fingerprint_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint_template` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `has_card` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `biometric_employees_company_id_foreign` (`company_id`),
  KEY `biometric_employees_user_id_foreign` (`user_id`),
  CONSTRAINT `biometric_employees_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `biometric_employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_employees');
    }
};
