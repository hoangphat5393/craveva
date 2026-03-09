<?php

namespace App\Jobs;

use App\Services\ClientImportProcessor;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Processes a chunk of client import rows in one job to reduce queue overhead.
 * Use with ImportExcel::importJobProcessChunked() for faster bulk import.
 */
class ImportClientChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<int, array>
     */
    private array $rows;

    private array $columns;

    private $company;

    public function __construct(array $rows, array $columns, $company = null)
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle(): void
    {
        $failures = [];

        foreach ($this->rows as $index => $row) {
            try {
                DB::transaction(fn () => ClientImportProcessor::processRow($row, $this->columns, $this->company));
            } catch (Exception $e) {
                $failures[] = 'Row ' . ($index + 1) . ': ' . $e->getMessage();
            }
        }

        if ($failures !== []) {
            $this->fail(implode("\n", array_slice($failures, 0, 50)) . (count($failures) > 50 ? "\n… and " . (count($failures) - 50) . ' more' : ''));
        }
    }
}
