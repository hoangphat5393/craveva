<?php

$addOnOf = 'craveva';

return [
    'name' => 'Zoom',
    'verification_required' => true,
    'parent_min_version' => '5.2.5',
    'script_name' => $addOnOf . '-zoom-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Zoom\Entities\ZoomGlobalSetting::class
];
