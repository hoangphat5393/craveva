<?php

namespace Modules\Purchase\Jobs;

use App\Traits\StoresImportBatchMetrics;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Purchase\Services\InventoryImportRowProcessor;

/**
 * Processes inventory import rows in chunks (same pattern as ImportClientChunkJob / ImportProductChunkJob).
 */
class ImportInventoryChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use StoresImportBatchMetrics;

    /** @var array<int, array<int|string, mixed>> */
    private array $rows;

    private array $columns;

    private $company;

    private int $chunkStartIndex;

    public function __construct(array $rows, array $columns, $company = null, int $chunkStartIndex = 0, array $options = [])
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
        $this->chunkStartIndex = $chunkStartIndex;
    }

    public function handle(): void
    {
        if (! $this->company) {
            $this->fail(__('messages.invalidData') . ': Company context is required for import.');

            return;
        }

        company($this->company);

        $failures = [];
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($this->rows as $index => $row) {
            try {
                $normalizedRow = InventoryImportRowProcessor::normalizeRowForJob($row);
                $imported = (new InventoryImportRowProcessor($normalizedRow, $this->columns, $this->company))->run();
                if ($imported) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            } catch (Exception $e) {
                $fileRow = $this->chunkStartIndex + $index + 2;
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $this->storeBatchMetrics([
            'created' => $createdCount,
            'updated' => 0,
            'skipped' => $skippedCount,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ]);

        if ($failures !== []) {
            $this->fail(implode("\n", array_slice($failures, 0, 50)) . (count($failures) > 50 ? "\n… and " . (count($failures) - 50) . ' more' : ''));
        }
    }

    private function storeBatchMetrics(array $delta): void
    {
        $this->mergeImportBatchMetrics($this->batchId, $delta);
    }
}
