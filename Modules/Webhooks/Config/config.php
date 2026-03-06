<?php

$addOnOf = 'craveva';

return [
    'name' => 'Webhooks',
    'verification_required' => true,
    'parent_min_version' => '5.3.54',
    'script_name' => $addOnOf.'-webhooks-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Webhooks\Entities\WebhooksGlobalSetting::class,
];
