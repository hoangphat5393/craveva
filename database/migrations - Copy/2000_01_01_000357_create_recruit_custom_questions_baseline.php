<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `recruit_custom_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `category` enum('job_application','job_offer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'job_application',
  `question` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `type` enum('text','number','password','textarea','select','radio','date','checkbox','file') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `required` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `values` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `column_priority` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recruit_custom_questions_company_id_foreign` (`company_id`),
  CONSTRAINT `recruit_custom_questions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('recruit_custom_questions');
    }
};
