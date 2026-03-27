<?php

namespace Modules\Warehouse\Jobs;

use App\Traits\StoresImportBatchMetrics;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Warehouse\Entities\Warehouse;

class ImportWarehouseChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use StoresImportBatchMetrics;

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
        if (! $this->company?->id) {
            $this->fail(__('messages.invalidData') . ': Company context is required for warehouse import.');

            return;
        }

        $failures = [];
        $companyId = (int) $this->company->id;
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $skippedMissingRequiredCount = 0;
        $invalidStatusCount = 0;

        foreach ($this->rows as $index => $row) {
            $fileRow = $this->chunkStartIndex + $index + 2;

            try {
                $normalized = $this->normalizeRow($row);

                if ($this->isRowEmpty($normalized)) {
                    continue;
                }

                $code = $this->stringValue($normalized, 'warehouse_code');
                $name = $this->stringValue($normalized, 'warehouse_name');

                // Skip rows thiếu code hoặc name theo yêu cầu nghiệp vụ import.
                if ($code === '' || $name === '') {
                    $skippedCount++;
                    $skippedMissingRequiredCount++;
                    continue;
                }

                $status = $this->stringValue($normalized, 'status');
                $address = $this->nullableStringValue($normalized, 'address');
                $description = $this->nullableStringValue($normalized, 'description');
                $isDefault = $this->parseBoolean($this->nullableStringValue($normalized, 'is_default'));

                if ($status !== '' && ! in_array($status, ['active', 'inactive'], true)) {
                    $invalidStatusCount++;
                    throw new Exception('status must be active or inactive');
                }

                // Upsert theo company + warehouse code. Duplicate trong file sẽ update bản ghi trước đó.
                $warehouse = Warehouse::where('company_id', $companyId)->where('code', $code)->first();

                $payload = [
                    'name' => $name,
                    'code' => $code,
                    'status' => $status !== '' ? $status : 'active',
                    'address' => $address,
                    'description' => $description,
                ];

                if ($warehouse) {
                    $warehouse->fill($payload);
                    $warehouse->save();
                    $updatedCount++;
                } else {
                    $warehouse = Warehouse::create(array_merge($payload, ['company_id' => $companyId]));
                    $createdCount++;
                }

                if ($isDefault) {
                    Warehouse::where('company_id', $companyId)->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
                    $warehouse->is_default = true;
                    $warehouse->save();
                }
            } catch (Exception $e) {
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $this->storeBatchMetrics([
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'skipped_missing_required' => $skippedMissingRequiredCount,
            'invalid_status' => $invalidStatusCount,
        ]);

        if ($failures !== []) {
            $this->fail(implode("\n", array_slice($failures, 0, 50)) . (count($failures) > 50 ? "\n… and " . (count($failures) - 50) . ' more' : ''));
        }
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalized[$key] = is_scalar($value) || $value === null ? $value : (string) $value;
        }

        return $normalized;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function stringValue(array $row, string $fieldId): string
    {
        $keys = array_keys($this->columns, $fieldId, true);
        if ($keys === []) {
            return '';
        }

        return trim((string) ($row[$keys[0]] ?? ''));
    }

    private function nullableStringValue(array $row, string $fieldId): ?string
    {
        $value = $this->stringValue($row, $fieldId);

        return $value === '' ? null : $value;
    }

    private function parseBoolean(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $v = mb_strtolower(trim($value));

        return in_array($v, ['1', 'true', 'yes', 'y'], true);
    }

    /**
     * Store lightweight import metrics by batch id for progress UI.
     */
    private function storeBatchMetrics(array $delta): void
    {
        $this->mergeImportBatchMetrics($this->batchId, $delta);
    }
}
