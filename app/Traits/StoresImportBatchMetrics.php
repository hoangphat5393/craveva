<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Atomic merge of import progress counters into cache (avoids lost updates when chunk jobs run in parallel).
 */
trait StoresImportBatchMetrics
{
    protected function mergeImportBatchMetrics(?string $batchId, array $delta): void
    {
        if (! $batchId) {
            return;
        }

        $cacheKey = 'import_metrics_' . $batchId;
        $defaults = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ];

        $merge = function () use ($cacheKey, $delta, $defaults) {
            $current = Cache::get($cacheKey, $defaults);
            foreach (array_keys($defaults) as $k) {
                $current[$k] = (int) ($current[$k] ?? 0) + (int) ($delta[$k] ?? 0);
            }
            Cache::put($cacheKey, $current, now()->addHours(12));
        };

        try {
            Cache::lock('lock_' . $cacheKey, 15)->block(8, $merge);
        } catch (\Throwable $e) {
            $merge();
        }
    }
}
