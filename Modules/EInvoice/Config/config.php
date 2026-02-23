<?php

$addOnOf = 'craveva';

return [
    'name' => 'EInvoice',
    'verification_required' => true,
    'parent_min_version' => '5.3.54',
    'script_name' => $addOnOf . '-einvoice-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\EInvoice\Entities\EInvoiceSetting::class,
];
