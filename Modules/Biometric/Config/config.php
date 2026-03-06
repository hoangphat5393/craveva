<?php

$addOnOf = 'craveva';
$product = $addOnOf.'-biometric-module';

return [
    'name' => 'Biometric',
    'verification_required' => true,
    'parent_min_version' => '5.2.3',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Biometric\Entities\BiometricGlobalSetting::class,
];
