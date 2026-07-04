<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `user_auths` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_email_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `two_fa_verify_via` enum('email','google_authenticator','both') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'when authenticator is email',
  `two_factor_expires_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_code_expires_at` datetime DEFAULT NULL,
  `twitter_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_auths_email_unique` (`email`),
  KEY `user_auths_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_auths');
    }
};
