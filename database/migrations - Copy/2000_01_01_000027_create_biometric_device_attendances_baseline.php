<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `biometric_device_attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `company_id` int unsigned NOT NULL,
  `device_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_serial_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stamp` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `status1` tinyint(1) DEFAULT NULL,
  `status2` tinyint(1) DEFAULT NULL,
  `status3` tinyint(1) DEFAULT NULL,
  `status4` tinyint(1) DEFAULT NULL,
  `status5` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biometric_device_attendances_user_id_foreign` (`user_id`),
  KEY `biometric_device_attendances_company_id_foreign` (`company_id`),
  CONSTRAINT `biometric_device_attendances_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `biometric_device_attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_device_attendances');
    }
};
