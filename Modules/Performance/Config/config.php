<?php

$addOnOf = 'craveva';
$product = $addOnOf.'-performance-module';

return [
    'name' => 'Performance',
    'verification_required' => true,
    'parent_min_version' => '5.4.7',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Performance\Entities\PerformanceGlobalSetting::class,
];
