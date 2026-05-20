<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minimum gross margin (VP pricing approval)
    |--------------------------------------------------------------------------
    |
    | When OEM Phase 1 review is enabled, VP cannot approve if indicative gross
    | margin (from BOM vs commercial subtotal) is below this percent. Set null
    | to disable the check. Override per environment via .env.
    |
    */
    'minimum_gross_margin_percent' => env('ESTIMATE_PHASE1_MIN_GROSS_MARGIN_PERCENT') !== null
        ? (float) env('ESTIMATE_PHASE1_MIN_GROSS_MARGIN_PERCENT', 15)
        : 15.0,

];
