<?php

$addOnOf = 'craveva';

return [
    'name' => 'Letter',
    'verification_required' => true,
    'parent_min_version' => '5.3.61',
    'script_name' => $addOnOf . '-letter-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Letter\Entities\LetterSetting::class,
];
