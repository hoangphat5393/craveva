<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesDoReconciliationReportCommand extends Command
{
    protected $signature = 'purchase:sales-do-reconcile-report
        {--baseline= : Baseline report JSON path from purchase:sales-do-migration-rehearsal}
        {--company_id= : Limit reconciliation to one company}
        {--sample=20 : Number of sample records in current snapshot}
        {--output= : Optional absolute/relative JSON file path}';

    protected $description = 'Phase 3 reconciliation report (baseline vs current) for sales shipment data';

    public function handle(): int
    {
        $baselineOption = (string) $this->option('baseline');
        if ($baselineOption === '') {
            $this->error('Missing required option: --baseline=<path-to-baseline-json>.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('sales_shipments') || ! Schema::hasTable('sales_shipment_items')) {
            $this->error('Required tables not found: sales_shipments and/or sales_shipment_items.');

            return self::FAILURE;
        }

        $baselinePath = $this->resolvePath($baselineOption);
        if (! is_file($baselinePath)) {
            $this->error('Baseline file not found: ' . $baselinePath);

            return self::FAILURE;
        }

        $baselineRaw = file_get_contents($baselinePath);
        $baseline = json_decode((string) $baselineRaw, true);
        if (! is_array($baseline)) {
            $this->error('Baseline file is not valid JSON: ' . $baselinePath);

            return self::FAILURE;
        }

        $companyId = $this->option('company_id');
        $sampleSize = max(1, (int) $this->option('sample'));
        $current = $this->buildSnapshot($companyId, $sampleSize);
        $baselineSource = (array) data_get($baseline, 'source', []);

        $statusDiff = $this->diffAssocNumeric(
            (array) ($baselineSource['status_distribution'] ?? []),
            (array) ($current['source']['status_distribution'] ?? [])
        );

        $comparison = [
            'shipments_count_delta' => (int) ($current['source']['shipments_count'] ?? 0) - (int) ($baselineSource['shipments_count'] ?? 0),
            'items_count_delta' => (int) ($current['source']['items_count'] ?? 0) - (int) ($baselineSource['items_count'] ?? 0),
            'outbound_stock_applied_count_delta' => (int) ($current['source']['outbound_stock_applied_count'] ?? 0) - (int) ($baselineSource['outbound_stock_applied_count'] ?? 0),
            'total_quantity_shipped_delta' => (float) ($current['source']['total_quantity_shipped'] ?? 0) - (float) ($baselineSource['total_quantity_shipped'] ?? 0),
            'status_distribution_delta' => $statusDiff,
            'quality_checks_current' => $current['quality_checks'],
            'quality_checks_ok' => [
                'orphan_item_count_is_zero' => ((int) ($current['quality_checks']['orphan_item_count'] ?? 0)) === 0,
                'duplicate_shipment_number_count_is_zero' => ((int) ($current['quality_checks']['duplicate_shipment_number_count'] ?? 0)) === 0,
            ],
        ];

        $report = [
            'command' => 'purchase:sales-do-reconcile-report',
            'generated_at' => now()->toIso8601String(),
            'scope' => [
                'company_id' => ($companyId === null || $companyId === '') ? null : (int) $companyId,
                'baseline_path' => $baselinePath,
            ],
            'baseline_source' => $baselineSource,
            'current' => $current,
            'comparison' => $comparison,
            'notes' => [
                'No data was modified.',
                'Use this report to verify before/after migration rehearsal consistency.',
            ],
        ];

        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            $this->error('Failed to encode reconciliation report.');

            return self::FAILURE;
        }

        if ($output = $this->option('output')) {
            $outputPath = $this->resolvePath((string) $output);
            $dir = dirname($outputPath);
            if (! is_dir($dir) && ! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
                $this->error('Cannot create output directory: ' . $dir);

                return self::FAILURE;
            }

            file_put_contents($outputPath, $json . PHP_EOL);
            $this->info('Reconciliation report written to: ' . $outputPath);
        } else {
            $this->line($json);
        }

        $this->info('Reconciliation completed.');

        return self::SUCCESS;
    }

    private function buildSnapshot($companyId, int $sampleSize): array
    {
        $shipmentQuery = DB::table('sales_shipments');
        if ($companyId !== null && $companyId !== '') {
            $shipmentQuery->where('company_id', (int) $companyId);
        }

        $shipmentIds = $shipmentQuery->pluck('id')->all();
        $itemQuery = DB::table('sales_shipment_items')->whereIn('sales_shipment_id', $shipmentIds ?: [0]);

        $statusCounts = (clone $shipmentQuery)
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return [
            'source' => [
                'shipments_count' => count($shipmentIds),
                'items_count' => (int) $itemQuery->count(),
                'status_distribution' => $statusCounts,
                'outbound_stock_applied_count' => (int) (clone $shipmentQuery)->where('outbound_stock_applied', 1)->count(),
                'total_quantity_shipped' => (float) $itemQuery->sum('quantity_shipped'),
            ],
            'quality_checks' => [
                'orphan_item_count' => (int) DB::table('sales_shipment_items as i')
                    ->leftJoin('sales_shipments as s', 's.id', '=', 'i.sales_shipment_id')
                    ->whereNull('s.id')
                    ->count(),
                'duplicate_shipment_number_count' => (int) (clone $shipmentQuery)
                    ->select('shipment_number')
                    ->whereNotNull('shipment_number')
                    ->groupBy('shipment_number')
                    ->havingRaw('COUNT(*) > 1')
                    ->get()
                    ->count(),
            ],
            'samples' => (clone $shipmentQuery)
                ->orderBy('id')
                ->limit($sampleSize)
                ->get(['id', 'company_id', 'order_id', 'warehouse_id', 'shipment_number', 'shipment_date', 'status', 'outbound_stock_applied'])
                ->toArray(),
        ];
    }

    private function diffAssocNumeric(array $baseline, array $current): array
    {
        $keys = array_unique(array_merge(array_keys($baseline), array_keys($current)));
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = ((int) ($current[$key] ?? 0)) - ((int) ($baseline[$key] ?? 0));
        }

        return $result;
    }

    private function resolvePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
