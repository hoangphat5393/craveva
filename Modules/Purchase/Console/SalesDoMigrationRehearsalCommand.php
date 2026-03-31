<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesDoMigrationRehearsalCommand extends Command
{
    protected $signature = 'purchase:sales-do-migration-rehearsal
        {--company_id= : Limit rehearsal to one company}
        {--output= : Optional absolute/relative JSON file path}
        {--sample=20 : Number of sample records in report}
        {--execute : Reserved for future (currently dry-run only)}';

    protected $description = 'Phase 3 rehearsal for sales shipment -> sales DO migration (dry-run only)';

    public function handle(): int
    {
        if ($this->option('execute')) {
            $this->warn('Execute mode is not implemented yet. Running dry-run rehearsal only.');
        }

        if (! Schema::hasTable('sales_shipments') || ! Schema::hasTable('sales_shipment_items')) {
            $this->error('Required tables not found: sales_shipments and/or sales_shipment_items.');

            return self::FAILURE;
        }

        $companyId = $this->option('company_id');
        $sampleSize = max(1, (int) $this->option('sample'));

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

        $report = [
            'command' => 'purchase:sales-do-migration-rehearsal',
            'mode' => 'dry-run',
            'generated_at' => now()->toIso8601String(),
            'scope' => [
                'company_id' => ($companyId === null || $companyId === '') ? null : (int) $companyId,
            ],
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
            'mapping_preview' => [
                'target_entity' => 'sales_do (future)',
                'rules' => [
                    'shipment_number -> do_number',
                    'shipment_date -> do_date',
                    'status mapped 1:1 initially (draft/confirmed/shipped/delivered/cancelled)',
                    'sales_shipment_items -> sales_do_items',
                ],
                'samples' => (clone $shipmentQuery)
                    ->orderBy('id')
                    ->limit($sampleSize)
                    ->get(['id', 'company_id', 'order_id', 'warehouse_id', 'shipment_number', 'shipment_date', 'status', 'outbound_stock_applied'])
                    ->toArray(),
            ],
            'notes' => [
                'No data was modified.',
                'This command is rehearsal only for Phase 3 preparation.',
            ],
        ];

        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            $this->error('Failed to encode rehearsal report.');

            return self::FAILURE;
        }

        if ($output = $this->option('output')) {
            $path = $output;
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
                $path = base_path($path);
            }

            $dir = dirname($path);
            if (! is_dir($dir) && ! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
                $this->error('Cannot create output directory: ' . $dir);

                return self::FAILURE;
            }

            file_put_contents($path, $json . PHP_EOL);
            $this->info('Dry-run report written to: ' . $path);
        } else {
            $this->line($json);
        }

        $this->info('Rehearsal completed (dry-run).');

        return self::SUCCESS;
    }
}
