<?php

namespace Modules\Purchase\Jobs;

use App\Traits\ExcelImportable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Purchase\Services\InventoryImportRowProcessor;
use RuntimeException;

class ImportInventoryJob implements ShouldQueue
{
    use Batchable, Dispatchable, ExcelImportable, InteractsWithQueue, Queueable, SerializesModels;

    private $row;

    private $columns;

    private $company;

    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle(): void
    {
        if (! $this->company) {
            $this->failJobWithMessage(__('messages.invalidData') . ': Company context is required for import.');

            return;
        }

        company($this->company);

        $normalized = is_array($this->row)
            ? InventoryImportRowProcessor::normalizeRowForJob($this->row)
            : $this->row;

        try {
            (new InventoryImportRowProcessor($normalized, $this->columns, $this->company))->run();
        } catch (RuntimeException $e) {
            $this->failJob($e->getMessage());
        }
    }
}
