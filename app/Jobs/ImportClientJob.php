<?php

namespace App\Jobs;

use App\Services\ClientImportProcessor;
use App\Traits\ExcelImportable;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportClientJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ExcelImportable;

    private $row;

    private $columns;

    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (! $this->isColumnExists('name')) {
            $this->failJob(__('messages.invalidData'));

            return;
        }

        try {
            DB::transaction(fn () => ClientImportProcessor::processRow($this->row, $this->columns, $this->company));
        } catch (Exception $e) {
            $this->failJobWithMessage($e->getMessage());
        }
    }
}
