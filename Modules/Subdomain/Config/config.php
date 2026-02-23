<?php

$addOnOf = 'craveva';

return [
    'name' => 'Subdomain',
    'verification_required' => true,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf . '-subdomain-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Subdomain\Entities\SubdomainSetting::class,
];
