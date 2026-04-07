<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client import — rows per chunk job (ImportClientChunkJob)
    |--------------------------------------------------------------------------
    |
    | Larger values reduce the number of queued jobs (faster HTTP dispatch) but
    | make each job heavier. Override per request with chunk_size when supported.
    |
    */

    'client_chunk_size' => max(1, min(500, (int) env('CLIENT_IMPORT_CHUNK_SIZE', 150))),

];
