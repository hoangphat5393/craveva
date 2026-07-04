<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `offer_letter_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recruit_job_offer_letter_id` int unsigned DEFAULT NULL,
  `user_id` int unsigned NOT NULL,
  `recruit_job_offer_file_id` int unsigned DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `offer_letter_histories_user_id_foreign` (`user_id`),
  KEY `offer_letter_histories_recruit_job_offer_letter_id_foreign` (`recruit_job_offer_letter_id`),
  KEY `offer_letter_histories_recruit_job_offer_file_id_foreign` (`recruit_job_offer_file_id`),
  CONSTRAINT `offer_letter_histories_recruit_job_offer_file_id_foreign` FOREIGN KEY (`recruit_job_offer_file_id`) REFERENCES `recruit_job_offer_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `offer_letter_histories_recruit_job_offer_letter_id_foreign` FOREIGN KEY (`recruit_job_offer_letter_id`) REFERENCES `recruit_job_offer_letter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `offer_letter_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letter_histories');
    }
};
