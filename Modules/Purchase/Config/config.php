<?php

$addOnOf = 'craveva';

return [
    'name' => 'Purchase',
    // Giới hạn số LEFT JOIN custom fields trong Inventory DataTable để tránh query chậm.
    // Đặt 0 để tắt hoàn toàn; tăng lên nếu cần hiển thị custom fields.
    'inventory_max_custom_field_joins' => env('PURCHASE_INVENTORY_MAX_CUSTOM_FIELD_JOINS', 0),
    'verification_required' => true,
    'parent_min_version' => '5.3.3',
    'script_name' => $addOnOf.'-purchase-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Purchase\Entities\PurchaseManagementSetting::class,
];
