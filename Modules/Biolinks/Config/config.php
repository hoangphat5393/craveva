<?php

$addOnOf = 'craveva';
$product = $addOnOf . '-biolinks-module';

return [
    'name' => 'Biolinks',
    'verification_required' => true,
    'parent_min_version' => '5.4.11',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Biolinks\Entities\BiolinksGlobalSetting::class,
];
