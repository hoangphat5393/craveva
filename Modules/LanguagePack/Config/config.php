<?php

$addOnOf = 'craveva';

return [
    'name' => 'LanguagePack',
    'verification_required' => true,
    'parent_min_version' => '5.3.3',
    'script_name' => $addOnOf.'-languagepack-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\LanguagePack\Entities\LanguagePackSetting::class,
];
