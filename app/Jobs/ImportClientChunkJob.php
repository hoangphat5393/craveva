<?php

namespace App\Jobs;

use App\Models\ClientDetails;
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

    /**
     * 0-based index of first row in this chunk (within data rows, excludes header).
     * Used to compute file row number: fileRow = chunkStartIndex + index + 2 (row 1 = header).
     */
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
        $failures = [];
        $companyId = $this->company?->id;

        // Load Client custom field metadata once per chunk (cache for bulk insert)
        $fieldMap = ClientImportProcessor::getClientCustomFieldMap($companyId);
        $bulkCustomRows = [];

        foreach ($this->rows as $index => $row) {
            try {
                $normalizedRow = self::normalizeRow($row);
                $user = DB::transaction(fn () => ClientImportProcessor::processRow(
                    $normalizedRow,
                    $this->columns,
                    $this->company,
                    ['skip_custom_fields' => true]
                ));
                if ($user === null) {
                    continue;
                }

                $user->load('clientDetails');
                $clientDetails = $user->clientDetails;
                if ($clientDetails && $fieldMap !== []) {
                    // Remove existing custom_fields_data for this client so bulk insert does not duplicate (update path)
                    DB::table('custom_fields_data')
                        ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
                        ->where('model_id', $clientDetails->id)
                        ->delete();
                    $rows = ClientImportProcessor::buildCustomFieldRowsForBulk(
                        $normalizedRow,
                        $this->columns,
                        $clientDetails->id,
                        $companyId,
                        $fieldMap
                    );
                    foreach ($rows as $r) {
                        $bulkCustomRows[] = $r;
                    }
                }
            } catch (Exception $e) {
                // File row number: row 1 = header, row 2 = first data
                $fileRow = $this->chunkStartIndex + $index + 2;
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        if ($bulkCustomRows !== []) {
            foreach (array_chunk($bulkCustomRows, 500) as $batch) {
                DB::table('custom_fields_data')->insert($batch);
            }
        }

        if ($failures !== []) {
            $this->fail(implode("\n", array_slice($failures, 0, 50)) . (count($failures) > 50 ? "\n… and " . (count($failures) - 50) . ' more' : ''));
        }
    }

    /**
     * Convert row values to scalars (string, int, float, null).
     * PhpSpreadsheet Cell/RichText can throw "The separation symbol could not be found"
     * when getFormattedValue() triggers number/date formatting. We catch and fallback to safe value.
     */
    private static function normalizeRow(array $row): array
    {
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
