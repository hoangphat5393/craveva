<?php

$addOnOf = 'craveva';

return [
    'name' => 'Purchase',
    'verification_required' => true,
    'parent_min_version' => '5.3.3',
    'script_name' => $addOnOf . '-purchase-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Purchase\Entities\PurchaseManagementSetting::class,
];
