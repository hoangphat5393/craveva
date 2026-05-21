<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allow destructive purge (--execute)
    |--------------------------------------------------------------------------
    |
    | Default false. Set COMPANY_PURGE_ALLOW_EXECUTE=true in .env only when you
    | intend to delete data. Dry-run (default) does not need this flag.
    |
    */
    'allow_execute' => filter_var(env('COMPANY_PURGE_ALLOW_EXECUTE', false), FILTER_VALIDATE_BOOL),

];
