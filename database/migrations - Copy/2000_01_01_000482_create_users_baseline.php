<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `user_auth_id` bigint unsigned DEFAULT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_phonecode` int DEFAULT NULL,
  `mobile` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','others') COLLATE utf8mb4_unicode_ci DEFAULT 'male',
  `salutation` enum('mr','mrs','miss','dr','sir','madam') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `status` enum('active','deactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `login` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enable',
  `onesignal_player_id` text COLLATE utf8mb4_unicode_ci,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `country_id` int unsigned DEFAULT NULL,
  `dark_theme` tinyint(1) NOT NULL,
  `rtl` tinyint(1) NOT NULL,
  `admin_approval` tinyint(1) NOT NULL DEFAULT '1',
  `permission_sync` tinyint(1) NOT NULL DEFAULT '1',
  `google_calendar_status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `telegram_user_id` bigint DEFAULT NULL,
  `customised_permissions` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `headers` text COLLATE utf8mb4_unicode_ci,
  `register_ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_details` text COLLATE utf8mb4_unicode_ci,
  `inactive_date` date DEFAULT NULL,
  `is_client_contact` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_company_id_unique` (`email`,`company_id`),
  KEY `users_country_id_foreign` (`country_id`),
  KEY `users_company_id_foreign` (`company_id`),
  KEY `users_user_auth_id_foreign` (`user_auth_id`),
  KEY `users_stripe_id_index` (`stripe_id`),
  KEY `users_is_client_contact_index` (`is_client_contact`),
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_is_client_contact_foreign` FOREIGN KEY (`is_client_contact`) REFERENCES `client_contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_user_auth_id_foreign` FOREIGN KEY (`user_auth_id`) REFERENCES `user_auths` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
