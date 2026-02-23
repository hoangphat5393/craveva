<?php

$addOnOf = 'craveva';

return [
    'name' => 'Sms',
    'verification_required' => true,
    'parent_min_version' => '5.2.3',
    'script_name' => $addOnOf . '-sms-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Sms\Entities\SmsSetting::class,
];
