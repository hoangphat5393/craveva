<?php

$addOnOf = 'craveva';

return [
    'name' => 'Affiliate',
    'verification_required' => true,
    'parent_min_version' => '5.3.9',
    'script_name' => $addOnOf.'-affiliate-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Affiliate\Entities\AffiliateGlobalSetting::class,
];
