<?php

$addOnOf = 'craveva';

return [
    'name' => 'ProjectRoadmap',
    'verification_required' => true,
    'parent_min_version' => '5.3.54',
    'script_name' => $addOnOf . '-projectroadmap-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\ProjectRoadmap\Entities\ProjectRoadmapSetting::class,
];
