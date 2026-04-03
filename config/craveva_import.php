<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sales history Excel import (stream jobs)
    |--------------------------------------------------------------------------
    |
    | Rows per PhpSpreadsheet slice / queue job. Lower = more jobs, shorter
    | per-job runtime (better progress polling). Batched DB lookups run inside
    | each job regardless of this value.
    |
    */
    'sales_history_rows_per_job' => (int) env('SALES_HISTORY_IMPORT_ROWS_PER_JOB', 500),

];
