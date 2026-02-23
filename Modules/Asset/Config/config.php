<?php

$addOnOf = 'craveva';
$product = $addOnOf . '-asset-module';

return [
    'name' => 'Asset',
    'verification_required' => true,
    'parent_min_version' => '5.2.5',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Asset\Entities\AssetSetting::class,
];
