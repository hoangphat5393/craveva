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
        'php', 'blade.php', 'json', 'md', 'css', 'js',
    ],
    'db_access' => [
        'default_modules' => ['core', 'pricing', 'warehouse'],
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
        'global_tables' => [
            'countries',
            'currencies',
            'languages',
            'unit_types',
        ],
        'sensitive_tables' => [
            'users' => [
                'allow_columns' => ['id', 'company_id', 'name', 'email', 'mobile', 'image', 'status', 'login', 'created_at', 'updated_at'],
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
        ],
    ],
];
