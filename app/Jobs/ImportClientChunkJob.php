<?php

namespace App\Jobs;

use App\Models\ClientDetails;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Services\ClientImportProcessor;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
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
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        // Load once per chunk: custom field map, role (client), role permissions (for bulk insert)
        $fieldMap = ClientImportProcessor::getClientCustomFieldMap($companyId);
        $bulkCustomRows = [];

        $role = $companyId ? Role::where('name', 'client')->where('company_id', $companyId)->select('id')->first() : null;
        $roleId = $role?->id;
        $rolePermissions = $roleId ? PermissionRole::where('role_id', $roleId)->get() : collect();
        $options = ['skip_custom_fields' => true, 'skip_role_and_search' => true, 'role_id' => $roleId];

        $roleUserRows = [];
        $userPermissionRows = [];
        $universalSearchRows = [];
        $now = now();

        $maxDeadlockRetries = 3;

        foreach ($this->rows as $index => $row) {
            try {
                $normalizedRow = self::normalizeRow($row);
                $user = null;
                $attempt = 0;
                while ($attempt < $maxDeadlockRetries) {
                    try {
                        $user = DB::transaction(fn() => ClientImportProcessor::processRow(
                            $normalizedRow,
                            $this->columns,
                            $this->company,
                            $options
                        ));
                        break;
                    } catch (Exception $e) {
                        if (self::isDeadlockException($e) && $attempt < $maxDeadlockRetries - 1) {
                            $attempt++;
                            usleep(100000 * $attempt); // 100ms, 200ms, 300ms
                            continue;
                        }
                        throw $e;
                    }
                }

                if ($user === null) {
                    $skippedCount++;
                    continue;
                }

                if ($user->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                $user->load('clientDetails');
                $clientDetails = $user->clientDetails;

                // New client: collect role_user, user_permissions, universal_search for bulk insert
                if ($user->wasRecentlyCreated && $roleId) {
                    $roleUserRows[] = ['user_id' => $user->id, 'role_id' => $roleId];
                    foreach ($rolePermissions as $pr) {
                        $userPermissionRows[] = [
                            'user_id' => $user->id,
                            'permission_id' => $pr->permission_id,
                            'permission_type_id' => $pr->permission_type_id,
                        ];
                    }
                    $route = 'clients.show';
                    $moduleType = 'client';
                    if ($user->email) {
                        $universalSearchRows[] = [
                            'company_id' => $companyId,
                            'searchable_id' => $user->id,
                            'module_type' => $moduleType,
                            'title' => $user->email,
                            'route_name' => $route,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    if ($clientDetails && $clientDetails->company_name) {
                        $universalSearchRows[] = [
                            'company_id' => $companyId,
                            'searchable_id' => $user->id,
                            'module_type' => $moduleType,
                            'title' => $clientDetails->company_name,
                            'route_name' => $route,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($clientDetails && $fieldMap !== []) {
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
                $fileRow = $this->chunkStartIndex + $index + 2;
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        // Bulk insert role_user
        if ($roleUserRows !== []) {
            foreach (array_chunk($roleUserRows, 100) as $batch) {
                DB::table('role_user')->insert($batch);
            }
        }
        // Bulk delete then insert user_permissions for new users in this chunk
        if ($userPermissionRows !== []) {
            $newUserIds = array_unique(array_column($roleUserRows, 'user_id'));
            DB::table('user_permissions')->whereIn('user_id', $newUserIds)->delete();
            foreach (array_chunk($userPermissionRows, 500) as $batch) {
                DB::table('user_permissions')->insert($batch);
            }
        }
        // Bulk insert universal_search
        if ($universalSearchRows !== []) {
            foreach (array_chunk($universalSearchRows, 200) as $batch) {
                DB::table('universal_search')->insert($batch);
            }
        }

        if ($bulkCustomRows !== []) {
            foreach (array_chunk($bulkCustomRows, 500) as $batch) {
                DB::table('custom_fields_data')->insert($batch);
            }
        }

        $this->storeBatchMetrics([
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ]);

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

    /**
     * Detect MySQL/InnoDB deadlock (1213) or serialization failure (40001).
     * Used to retry the row transaction on staging when many chunk jobs run in parallel.
     */
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

    private function storeBatchMetrics(array $delta): void
    {
        if (! $this->batchId) {
            return;
        }

        $cacheKey = 'import_metrics_' . $this->batchId;
        $current = Cache::get($cacheKey, [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ]);

        $current['created'] += (int) ($delta['created'] ?? 0);
        $current['updated'] += (int) ($delta['updated'] ?? 0);
        $current['skipped'] += (int) ($delta['skipped'] ?? 0);
        $current['skipped_missing_required'] += (int) ($delta['skipped_missing_required'] ?? 0);
        $current['invalid_status'] += (int) ($delta['invalid_status'] ?? 0);

        Cache::put($cacheKey, $current, now()->addHours(12));
    }
}
