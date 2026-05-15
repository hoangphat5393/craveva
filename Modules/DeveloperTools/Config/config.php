<?php

return [
    'name' => 'DeveloperTools',
    'scan_paths' => [
        base_path('Modules/DeveloperTools'),
        base_path('app'),
        base_path('database'),
        base_path('routes'),
    ],
    'allowed_extensions' => [
        'php',
        'blade.php',
        'json',
        'md',
        'css',
        'js',
    ],
    'db_access' => [
        'default_modules' => ['core', 'pricing', 'warehouse'],
        /*
         * Always merged into gateway views on credential create (not shown as checkboxes).
         * Ensures custom field definitions and per-company values are available to AI/tools.
         */
        'implicit_modules_on_credential' => [
            'custom_fields',
        ],
        'modules' => [
            'core' => [
                'label' => 'Core (Products/Orders/Customers)',
                'depends_on' => [],
                'table_patterns' => [
                    'products',
                    'product_%',
                    'orders',
                    'order_%',
                    'companies',
                    'company_%',
                    'users',
                    'client_details',
                    'client_%',
                    'unit_types',
                    'currencies',
                    'countries',
                    'languages',
                ],
            ],
            'pricing' => [
                'label' => 'Pricing',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'pricing_%',
                    'volume_discount_rules',
                    'client_product_pricing',
                    'company_customer_pricing',
                    'company_customer_product_pricing',
                    'deal_proposal_pricing',
                ],
            ],
            'warehouse' => [
                'label' => 'Warehouse',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'warehouses',
                    'warehouse_%',
                    'stock_%',
                ],
            ],
            'production' => [
                'label' => 'Production (BOM / orders / batches)',
                'depends_on' => ['core', 'warehouse'],
                'table_patterns' => [
                    'production_%',
                ],
            ],
            'inventory' => [
                'label' => 'Inventory (Purchase inventory / adjustments)',
                'depends_on' => ['core', 'warehouse'],
                'table_patterns' => [
                    'purchase_inventory_adjustment',
                    'purchase_inventory_files',
                    'purchase_inventory_histories',
                    'purchase_stock_adjustments',
                    'purchase_stock_adjustment_reasons',
                ],
            ],
            'purchase' => [
                'label' => 'Purchase',
                'depends_on' => ['core', 'warehouse'],
                'table_patterns' => [
                    'purchase_%',
                    'purchase_orders',
                    'purchase_order_%',
                ],
            ],
            'webhooks' => [
                'label' => 'Webhooks',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'webhooks_%',
                ],
            ],
            'einvoice' => [
                'label' => 'EInvoice',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'e_invoice_%',
                ],
            ],
            'onboarding' => [
                'label' => 'Onboarding',
                'depends_on' => [],
                'table_patterns' => [
                    'onboarding_%',
                ],
            ],
            'sms' => [
                'label' => 'Sms',
                'depends_on' => [],
                'table_patterns' => [
                    'sms_%',
                ],
            ],
            'lineintegration' => [
                'label' => 'LineIntegration',
                'depends_on' => ['core', 'pricing', 'warehouse'],
                'table_patterns' => [],
            ],
            'delivery' => [
                'label' => 'Delivery',
                'depends_on' => ['core', 'warehouse'],
                'table_patterns' => [
                    'delivery_orders',
                    'delivery_order_items',
                    'stock_movements',
                ],
            ],
            'customer_request' => [
                'label' => 'Customer Request (Estimate Request)',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'estimate_requests',
                    'estimates',
                    'estimate_items',
                    'estimate_item_images',
                    'estimate_templates',
                    'estimate_template_items',
                    'estimate_template_item_images',
                    'accept_estimates',
                    'projects',
                ],
            ],
            'invoice' => [
                'label' => 'Invoice',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'invoices',
                    'invoice_items',
                    'invoice_item_images',
                    'invoice_recurring',
                    'invoice_recurring_items',
                    'invoice_recurring_item_images',
                    'invoice_settings',
                    'invoice_files',
                    'invoice_payment_details',
                ],
            ],
            'recruit' => [
                'label' => 'Recruit',
                'depends_on' => ['core'],
                'table_patterns' => [
                    'recruit_%',
                    'application_sources',
                    'recruiters',
                    'offer_letter_histories',
                    'job_interview_stages',
                ],
            ],
            'custom_fields' => [
                'label' => 'Custom fields (definitions & values)',
                'internal_only' => true,
                'depends_on' => [],
                'table_patterns' => [
                    'custom_field_groups',
                    'custom_fields',
                    'custom_fields_data',
                ],
            ],
        ],
        'deny_tables' => [
            'migrations',
            'jobs',
            'failed_jobs',
            'job_batches',
            'sessions',
            'cache',
            'cache_locks',
            'password_resets',
            'personal_access_tokens',
            'oauth_access_tokens',
            'oauth_clients',
            'oauth_personal_access_clients',
            'oauth_refresh_tokens',
        ],
        /*
         * Optional global exclusions after pattern matching (defense in depth).
         * exclude_table_patterns use the same % wildcard rules as table_patterns.
         */
        'exclude_tables' => [],
        'exclude_table_patterns' => [],
        'global_tables' => [
            'countries',
            'currencies',
            'languages',
            'unit_types',
        ],
        'sensitive_tables' => [
            'users' => [
                // country_phonecode needed for AI client verification (normalize phone: +84 + mobile)
                'allow_columns' => ['id', 'company_id', 'name', 'email', 'mobile', 'country_phonecode', 'image', 'status', 'login', 'created_at', 'updated_at'],
            ],
            'companies' => [
                'allow_columns' => ['id', 'company_name', 'company_email', 'company_phone', 'website', 'address', 'city', 'state', 'country', 'postal_code', 'created_at', 'updated_at'],
            ],
            'sms_settings' => [
                'deny' => true,
            ],
            'payment_gateway_credentials' => [
                'deny' => true,
            ],
            'recruit_global_settings' => [
                'deny' => true,
            ],
        ],
        'join_views' => [
            'order_items' => [
                'select' => 'oi.*',
                'from' => '{mainDb}.order_items oi JOIN {mainDb}.orders o ON o.id = oi.order_id',
                'where' => 'o.company_id = {companyId}',
            ],
            'warehouse_product_stock' => [
                'select' => 'wps.*',
                'from' => '{mainDb}.warehouse_product_stock wps JOIN {mainDb}.warehouses w ON w.id = wps.warehouse_id',
                'where' => 'w.company_id = {companyId}',
            ],
            'pricing_tier_items' => [
                'select' => 'pti.*',
                'from' => '{mainDb}.pricing_tier_items pti JOIN {mainDb}.pricing_tiers pt ON pt.id = pti.pricing_tier_id',
                'where' => 'pt.company_id = {companyId}',
            ],
            'company_customer_product_pricing' => [
                'select' => 'ccpp.*',
                'from' => '{mainDb}.company_customer_product_pricing ccpp JOIN {mainDb}.company_customer_pricing ccp ON ccp.id = ccpp.company_customer_pricing_id',
                'where' => 'ccp.company_id = {companyId}',
            ],
            'delivery_order_items' => [
                'select' => 'doi.*',
                'from' => '{mainDb}.delivery_order_items doi JOIN {mainDb}.delivery_orders do ON do.id = doi.delivery_order_id',
                'where' => 'do.company_id = {companyId}',
            ],
            'estimate_items' => [
                'select' => 'ei.*',
                'from' => '{mainDb}.estimate_items ei JOIN {mainDb}.estimates e ON e.id = ei.estimate_id',
                'where' => 'e.company_id = {companyId}',
            ],
            'estimate_item_images' => [
                'select' => 'eii.*',
                'from' => '{mainDb}.estimate_item_images eii JOIN {mainDb}.estimate_items ei ON ei.id = eii.estimate_item_id JOIN {mainDb}.estimates e ON e.id = ei.estimate_id',
                'where' => 'e.company_id = {companyId}',
            ],
            'invoice_items' => [
                'select' => 'ii.*',
                'from' => '{mainDb}.invoice_items ii JOIN {mainDb}.invoices i ON i.id = ii.invoice_id',
                'where' => 'i.company_id = {companyId}',
            ],
            'invoice_item_images' => [
                'select' => 'iii.*',
                'from' => '{mainDb}.invoice_item_images iii JOIN {mainDb}.invoice_items ii ON ii.id = iii.invoice_item_id JOIN {mainDb}.invoices i ON i.id = ii.invoice_id',
                'where' => 'i.company_id = {companyId}',
            ],
            'custom_fields_data' => [
                'select' => 'cfd.*',
                'from' => '{mainDb}.custom_fields_data cfd JOIN {mainDb}.custom_fields cf ON cf.id = cfd.custom_field_id',
                'where' => 'cf.company_id = {companyId}',
            ],
            'recruit_job_questions' => [
                'select' => 'rjq.*',
                'from' => '{mainDb}.recruit_job_questions rjq JOIN {mainDb}.recruit_jobs rj ON rj.id = rjq.recruit_job_id',
                'where' => 'rj.company_id = {companyId}',
            ],
            'recruit_job_custom_answers' => [
                'select' => 'rjca.*',
                'from' => '{mainDb}.recruit_job_custom_answers rjca JOIN {mainDb}.recruit_jobs rj ON rj.id = rjca.recruit_job_id',
                'where' => 'rj.company_id = {companyId}',
            ],
            'recruit_job_offer_questions' => [
                'select' => 'rjoq.*',
                'from' => '{mainDb}.recruit_job_offer_questions rjoq JOIN {mainDb}.recruit_job_offer_letter rjol ON rjol.id = rjoq.recruit_job_offer_letter_id',
                'where' => 'rjol.company_id = {companyId}',
            ],
            'job_interview_stages' => [
                'select' => 'jis.*',
                'from' => '{mainDb}.job_interview_stages jis JOIN {mainDb}.recruit_jobs rj ON rj.id = jis.recruit_job_id',
                'where' => 'rj.company_id = {companyId}',
            ],
            'offer_letter_histories' => [
                'select' => 'olh.*',
                'from' => '{mainDb}.offer_letter_histories olh JOIN {mainDb}.recruit_job_offer_letter rjol ON rjol.id = olh.recruit_job_offer_letter_id',
                'where' => 'rjol.company_id = {companyId}',
            ],
            'recruit_candidate_follow_ups' => [
                'select' => 'rcfu.*',
                'from' => '{mainDb}.recruit_candidate_follow_ups rcfu JOIN {mainDb}.recruit_job_applications rja ON rja.id = rcfu.recruit_job_application_id',
                'where' => 'rja.company_id = {companyId}',
            ],
            'recruit_jobboard_settings' => [
                'select' => 'rjbs.*',
                'from' => '{mainDb}.recruit_jobboard_settings rjbs JOIN {mainDb}.users u ON u.id = rjbs.user_id',
                'where' => 'u.company_id = {companyId}',
            ],
        ],
    ],

    /*
    | Host shown when copying MySQL credentials (Developer Tools UI).
    | Set DEVELOPER_TOOLS_CREDENTIAL_DISPLAY_HOST in .env to your public DB IP or Cloud SQL
    | hostname for external clients (HeidiSQL, DBeaver). If unset, defaults to the app MySQL
    | host (database.connections.mysql.host), not the HTTP request hostname.
    */
    'credential_display_host' => env('DEVELOPER_TOOLS_CREDENTIAL_DISPLAY_HOST'),
];
