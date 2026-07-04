<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `smtp_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mail_driver` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp',
  `mail_host` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp.gmail.com',
  `mail_port` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '587',
  `mail_username` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'youremail@gmail.com',
  `mail_password` text COLLATE utf8mb4_unicode_ci,
  `mail_from_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'your name',
  `mail_from_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'from@email.com',
  `mail_encryption` enum('ssl','tls','starttls') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `mail_connection` enum('sync','database') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sync',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
