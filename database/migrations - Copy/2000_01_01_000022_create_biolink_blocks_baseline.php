<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `biolink_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `biolink_id` int unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_unicode_ci,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT '0',
  `text_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#000000',
  `text_alignment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT 'center',
  `background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#FFFFFF',
  `animation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heading_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paragraph` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_alt` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_size` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_fit` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cover',
  `border_radius` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'straight',
  `border_width` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `border_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#000000',
  `border_style` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solid',
  `border_shadow_x` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `border_shadow_y` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `border_shadow_blur` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '20',
  `border_shadow_spread` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `border_shadow_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '#00000010',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `position` int DEFAULT NULL,
  `icon_size` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telegram` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discord` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snapchat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pinterest` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reddit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spotify` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `threads` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitch` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `placeholder` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_placeholder` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thank_you_message` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thank_you_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_agreement` tinyint(1) DEFAULT '0',
  `agreement_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agreement_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mailchimp_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_payment_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `biolink_blocks_biolink_id_foreign` (`biolink_id`),
  CONSTRAINT `biolink_blocks_biolink_id_foreign` FOREIGN KEY (`biolink_id`) REFERENCES `biolinks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('biolink_blocks');
    }
};
