<?php

namespace App\Jobs;

use App\Services\EstimateImportProcessor;
use App\Traits\StoresImportBatchMetrics;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Chunked quotation (Estimate) import; mirrors ImportClientChunkJob pattern.
 */
class ImportEstimateChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use StoresImportBatchMetrics;

    /** @var array<int, array> */
    private array $rows;

    private array $columns;

    private $company;

    private int $chunkStartIndex;

    /** @var array<string, mixed> */
    private array $options;

    public function __construct(array $rows, array $columns, $company = null, int $chunkStartIndex = 0, array $options = [])
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
        $this->chunkStartIndex = $chunkStartIndex;
        $this->options = $options;
    }

    public function handle(): void
    {
        $failures = [];
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $maxDeadlockRetries = 3;

        foreach ($this->rows as $index => $row) {
            $normalizedRow = self::normalizeRow($row);
            if (self::isEffectivelyEmptyRow($normalizedRow)) {
                $skippedCount++;

                continue;
            }

            try {
                $attempt = 0;
                $result = null;
                while ($attempt < $maxDeadlockRetries) {
                    try {
                        $result = EstimateImportProcessor::processRow(
                            $normalizedRow,
                            $this->company,
                            $this->columns,
                            $this->options
                        );
                        break;
                    } catch (Exception $e) {
                        if (self::isDeadlockException($e) && $attempt < $maxDeadlockRetries - 1) {
                            $attempt++;
                            usleep(100000 * $attempt);

                            continue;
                        }
                        throw $e;
                    }
                }

                if ($result === 'created') {
                    $createdCount++;
                } elseif ($result === 'updated') {
                    $updatedCount++;
                }
            } catch (Exception $e) {
                $fileRow = $this->chunkStartIndex + $index + 2;
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $this->mergeImportBatchMetrics($this->batch()?->id, [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ]);

        if ($failures !== []) {
            $this->mergeImportBatchRowErrors($this->batch()?->id, $failures);
            $this->fail(implode("\n", array_slice($failures, 0, 50)) . (count($failures) > 50 ? "\n… and " . (count($failures) - 50) . ' more' : ''));
        }
    }

    private static function isEffectivelyEmptyRow(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int|string, mixed>  $row
     * @return array<int, mixed>
     */
    private static function normalizeRow(array $row): array
    {
        $row = array_values($row);
        $result = [];
        foreach ($row as $key => $value) {
            if ($value === null || is_scalar($value)) {
                $result[$key] = $value;
            } else {
                $result[$key] = self::cellToScalar($value);
            }
        }

        return $result;
    }

    private static function isDeadlockException(Exception $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        if ($code === '40001' || $code === '1213') {
            return true;
        }
        if (strpos($message, '1213') !== false || strpos($message, 'Deadlock') !== false) {
            return true;
        }
        $previous = $e->getPrevious();
        if ($previous && ($previous->getCode() === '40001' || $previous->getCode() === '1213')) {
            return true;
        }

        return false;
    }

    private static function cellToScalar($value): ?string
    {
        try {
            if (is_object($value) && method_exists($value, 'getFormattedValue')) {
                $v = $value->getFormattedValue();

                return $v === null ? null : (string) $v;
            }
            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }

            return $value === null ? null : (string) $value;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
