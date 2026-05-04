# DB_STRUCTURE

- Generated at: 2026-05-04T05:35:06+00:00

## Tables (from module migrations)

### biolink_blocks

- Columns: address, animation, avatar_size, background_color, biolink_id, border_color, border_radius, border_shadow_blur, border_shadow_color, border_shadow_spread, border_shadow_x, border_shadow_y, border_style, border_width, currency_code, discord, email, facebook, heading_type, icon_size, image, image_alt, instagram, linkedin, name, object_fit, open_in_new_tab, paragraph, paypal_type, phone, pinterest, position, price, product_title, reddit, snapchat, spotify, status, telegram, text_alignment
- Migrations: 2024_03_12_063833_create_biolink_blocks_table.php

### biolink_settings

- Columns: biolink_id, block_hover_animation, block_space, branding_name, branding_text_color, branding_url, company_id, custom_color_one, custom_color_two, custom_css, custom_js, display_branding, favicon, font, is_sensitive, meta_description, meta_keywords, page_link, page_title, protection_password, status, theme, theme_color, total_page_views, verified_badge
- Migrations: 2024_03_01_062913_create_biolink_table.php

### biolinks

- Columns: biolink_id, block_hover_animation, block_space, branding_name, branding_text_color, branding_url, company_id, custom_color_one, custom_color_two, custom_css, custom_js, display_branding, favicon, font, is_sensitive, meta_description, meta_keywords, page_link, page_title, protection_password, status, theme, theme_color, total_page_views, verified_badge
- Migrations: 2024_03_01_062913_create_biolink_table.php

### biolinks_global_settings

- Columns: license_type, notify_update, purchase_code, purchased_on, supported_until
- Migrations: 2024_02_17_090058_create_biolinks_global_settings_table.php

## Entities (table + casts)

- Modules/Biolinks/Entities/Biolink.php
- Modules/Biolinks/Entities/BiolinkBlocks.php
- Modules/Biolinks/Entities/BiolinkSetting.php
- Modules/Biolinks/Entities/BiolinksGlobalSetting.php
