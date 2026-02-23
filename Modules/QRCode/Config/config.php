<?php

$addOnOf = 'craveva';

return [
    'name' => 'QRCode',
    'verification_required' => true,
    'parent_min_version' => '5.3.6',
    'script_name' => $addOnOf . '-qrcode-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\QRCode\Entities\QRCodeSetting::class,
];
