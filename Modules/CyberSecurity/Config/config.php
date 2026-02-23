<?php

$addOnOf = 'craveva';
$product = $addOnOf . '-cybersecurity-module';

return [
    'name' => 'CyberSecurity',
    'verification_required' => true,
    'parent_min_version' => '5.3.6',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\CyberSecurity\Entities\CyberSecuritySetting::class,
];
