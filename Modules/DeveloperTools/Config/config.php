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
                'label' => 'Delivery (Giao hàng/Vận đơn)',
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
                'label' => 'Invoice (Hóa đơn)',
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
        ],
    ],
];
