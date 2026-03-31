<?php

$addOnOf = 'craveva';

return [
    'name' => 'Purchase',
    // Giới hạn số LEFT JOIN custom fields trong Inventory DataTable để tránh query chậm.
    // Đặt 0 để tắt hoàn toàn; tăng lên nếu cần hiển thị custom fields.
    'inventory_max_custom_field_joins' => env('PURCHASE_INVENTORY_MAX_CUSTOM_FIELD_JOINS', 0),
    // Phase-1 compatibility naming for business UI:
    // - legacy: keep old labels (Sales Shipments / Delivery Orders)
    // - compat_v2: show business labels (Sales DO / GRN) while technical routes/tables stay unchanged
    'flow_naming_mode' => env('PURCHASE_FLOW_NAMING_MODE', 'compat_v2'),

    // Phase-2+ cutover switch framework (reserved):
    // false: keep technical flow on current artifacts
    // true: allow new DO/GRN canonical runtime switches when implemented
    'do_grn_cutover_enabled' => env('PURCHASE_DO_GRN_CUTOVER_ENABLED', false),
    // Permission aliases for bridge migration:
    // - Before cutover: allow either new permission or legacy permission.
    // - After cutover: enforce new permission only.
    'permission_aliases' => [
        'sales_do' => [
            'view' => ['new' => 'view_sales_do', 'legacy' => 'view_sales_shipment'],
            'create' => ['new' => 'create_sales_do', 'legacy' => 'create_sales_shipment'],
            'update' => ['new' => 'update_sales_do', 'legacy' => 'update_sales_shipment'],
            'ship' => ['new' => 'ship_sales_do', 'legacy' => 'ship_sales_shipment'],
            'cancel' => ['new' => 'cancel_sales_do', 'legacy' => 'cancel_sales_shipment'],
        ],
        'grn' => [
            'view' => ['new' => 'view_grn', 'legacy' => 'view_purchase_order'],
            'create' => ['new' => 'create_grn', 'legacy' => 'add_purchase_order'],
            'update' => ['new' => 'update_grn', 'legacy' => 'edit_purchase_order'],
            'change_status' => ['new' => 'change_status_grn', 'legacy' => 'edit_purchase_order'],
            'delete' => ['new' => 'delete_grn', 'legacy' => 'delete_purchase_order'],
        ],
    ],
    'verification_required' => true,
    'parent_min_version' => '5.3.3',
    'script_name' => $addOnOf . '-purchase-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Purchase\Entities\PurchaseManagementSetting::class,
];
