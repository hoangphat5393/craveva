<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_job_addresses` (
  `recruit_job_id` bigint unsigned NOT NULL,
  `company_address_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `recruit_job_addresses_recruit_job_id_foreign` (`recruit_job_id`),
  KEY `recruit_job_addresses_company_address_id_foreign` (`company_address_id`),
  CONSTRAINT `recruit_job_addresses_company_address_id_foreign` FOREIGN KEY (`company_address_id`) REFERENCES `company_addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `recruit_job_addresses_recruit_job_id_foreign` FOREIGN KEY (`recruit_job_id`) REFERENCES `recruit_jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_job_addresses');
    }
};
