<?php

$PRODUCT = 'craveva';

return [

    /*
     * Model name of where purchase code is stored
     */
    'setting' => \App\Models\GlobalSetting::class,

    /*
     * Add redirect route here route('login') will be used
     */
    'redirectRoute' => 'login',

    /*
    * Temp folder to store update before to install it.
    */
    'tmp_path' => storage_path().'/app',
];
