<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SalesDoMigrateDataCommand extends Command
{
    protected $signature = 'purchase:sales-do-migrate-data
        {--company_id= : Limit migration to one company}
        {--chunk=200 : Batch size when execute}
        {--sample=20 : Number of sample records in report}
        {--output= : Optional JSON report path}
        {--execute : Execute data migration (default is dry-run)}
        {--force : Required with --execute to avoid accidental run}';

    protected $description = 'Phase 3 data migration from sales_shipments to sales_dos (dry-run by default)';

    public function handle(): int
    {
        if (! $this->validateTables()) {
            return self::FAILURE;
        }

        $companyId = $this->option('company_id');
        $sampleSize = max(1, (int) $this->option('sample'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $execute = (bool) $this->option('execute');

        $scopeQuery = DB::table('sales_shipments');
        if ($companyId !== null && $companyId !== '') {
            $scopeQuery->where('company_id', (int) $companyId);
        }

        $allShipmentIds = (clone $scopeQuery)->pluck('id')->all();
        $pendingShipmentIds = (clone $scopeQuery)
            ->whereNotIn('id', function ($q) {
                $q->from('sales_dos')
                    ->select('legacy_sales_shipment_id')
                    ->whereNotNull('legacy_sales_shipment_id');
            })
            ->pluck('id')
            ->all();

        $report = [
            'command' => 'purchase:sales-do-migrate-data',
            'generated_at' => now()->toIso8601String(),
            'mode' => $execute ? 'execute' : 'dry-run',
            'scope' => [
                'company_id' => ($companyId === null || $companyId === '') ? null : (int) $companyId,
                'chunk' => $chunkSize,
            ],
            'source' => [
                'shipments_count' => count($allShipmentIds),
                'items_count' => (int) DB::table('sales_shipment_items')->whereIn('sales_shipment_id', $allShipmentIds ?: [0])->count(),
            ],
            'target' => [
                'headers_migrated_count' => (int) DB::table('sales_dos')
                    ->whereNotNull('legacy_sales_shipment_id')
                    ->when($companyId !== null && $companyId !== '', fn($q) => $q->where('company_id', (int) $companyId))
                    ->count(),
                'items_migrated_count' => (int) DB::table('sales_do_items')
                    ->whereNotNull('legacy_sales_shipment_item_id')
                    ->whereIn('sales_do_id', function ($q) use ($companyId) {
                        $q->from('sales_dos')
                            ->select('id')
                            ->whereNotNull('legacy_sales_shipment_id');
                        if ($companyId !== null && $companyId !== '') {
                            $q->where('company_id', (int) $companyId);
                        }
                    })
                    ->count(),
            ],
            'pending' => [
                'shipments_count' => count($pendingShipmentIds),
                'items_count' => (int) DB::table('sales_shipment_items')->whereIn('sales_shipment_id', $pendingShipmentIds ?: [0])->count(),
            ],
            'samples' => DB::table('sales_shipments')
                ->whereIn('id', $pendingShipmentIds ?: [0])
                ->orderBy('id')
                ->limit($sampleSize)
                ->get(['id', 'company_id', 'shipment_number', 'shipment_date', 'status'])
                ->toArray(),
            'notes' => [
                'Default mode is dry-run.',
                'Execute mode is idempotent by legacy ID mapping.',
            ],
        ];

        if (! $execute) {
            $this->writeReport($report, (string) $this->option('output'));
            $this->info('Dry-run completed. No data was modified.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->error('Execute mode requires --force.');

            return self::FAILURE;
        }

        $createdHeaderIds = [];
        $createdItemIds = [];

        try {
            DB::transaction(function () use ($pendingShipmentIds, $chunkSize, &$createdHeaderIds, &$createdItemIds): void {
                collect($pendingShipmentIds)->chunk($chunkSize)->each(function ($idChunk) use (&$createdHeaderIds, &$createdItemIds): void {
                    $shipments = DB::table('sales_shipments')
                        ->whereIn('id', $idChunk->all())
                        ->orderBy('id')
                        ->get();

                    foreach ($shipments as $shipment) {
                        $targetHeaderId = $this->findOrCreateTargetHeader((array) $shipment, $createdHeaderIds);
                        $items = DB::table('sales_shipment_items')
                            ->where('sales_shipment_id', (int) $shipment->id)
                            ->orderBy('id')
                            ->get();

                        foreach ($items as $item) {
                            $this->findOrCreateTargetItem($targetHeaderId, (array) $item, $createdItemIds);
                        }
                    }
                });
            });
        } catch (Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $runId = now()->format('Ymd-His') . '-' . substr(md5((string) microtime(true)), 0, 8);
        $manifestRelative = 'storage/app/reports/sales-do-migrate-manifest-' . $runId . '.json';
        $manifestPath = base_path($manifestRelative);

        $report['execute_result'] = [
            'created_headers_count' => count($createdHeaderIds),
            'created_items_count' => count($createdItemIds),
            'created_header_ids' => $createdHeaderIds,
            'created_item_ids' => $createdItemIds,
            'rollback_manifest' => $manifestRelative,
        ];

        $this->writeReport($report, (string) $this->option('output'));
        $this->writeManifest($manifestPath, [
            'command' => 'purchase:sales-do-migrate-data',
            'run_id' => $runId,
            'generated_at' => now()->toIso8601String(),
            'scope' => $report['scope'],
            'created_header_ids' => $createdHeaderIds,
            'created_item_ids' => $createdItemIds,
        ]);

        $this->info('Execute migration completed.');
        $this->info('Rollback manifest: ' . $manifestPath);

        return self::SUCCESS;
    }

    private function validateTables(): bool
    {
        $required = ['sales_shipments', 'sales_shipment_items', 'sales_dos', 'sales_do_items'];
        $missing = collect($required)->filter(fn($table) => ! Schema::hasTable($table))->values()->all();
        if ($missing !== []) {
            $this->error('Required tables not found: ' . implode(', ', $missing) . '.');
            $legacyMissing = array_values(array_intersect($missing, ['sales_shipments', 'sales_shipment_items']));
            if ($legacyMissing !== [] && Schema::hasTable('sales_dos')) {
                $this->line('Nothing to migrate: this command only copies from legacy sales_shipments tables.');
                $this->line('If those tables were dropped (phase 5 / fresh DB), use Sales DO in the app or restore a backup that still has sales_shipments.');
            }

            return false;
        }

        return true;
    }

    private function findOrCreateTargetHeader(array $shipment, array &$createdHeaderIds): int
    {
        $existingId = DB::table('sales_dos')
            ->where('legacy_sales_shipment_id', (int) $shipment['id'])
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        $id = DB::table('sales_dos')->insertGetId([
            'legacy_sales_shipment_id' => (int) $shipment['id'],
            'company_id' => (int) $shipment['company_id'],
            'order_id' => (int) $shipment['order_id'],
            'warehouse_id' => (int) $shipment['warehouse_id'],
            'do_number' => (string) $shipment['shipment_number'],
            'do_date' => (string) $shipment['shipment_date'],
            'status' => (string) $shipment['status'],
            'outbound_stock_applied' => (int) ($shipment['outbound_stock_applied'] ?? 0),
            'notes' => $shipment['notes'] ?? null,
            'created_by' => $shipment['created_by'] ?? null,
            'updated_by' => $shipment['updated_by'] ?? null,
            'created_at' => $shipment['created_at'] ?? now(),
            'updated_at' => $shipment['updated_at'] ?? now(),
        ]);

        $createdHeaderIds[] = (int) $id;

        return (int) $id;
    }

    private function findOrCreateTargetItem(int $targetHeaderId, array $item, array &$createdItemIds): void
    {
        $existingId = DB::table('sales_do_items')
            ->where('legacy_sales_shipment_item_id', (int) $item['id'])
            ->value('id');

        if ($existingId) {
            return;
        }

        $id = DB::table('sales_do_items')->insertGetId([
            'sales_do_id' => $targetHeaderId,
            'legacy_sales_shipment_item_id' => (int) $item['id'],
            'order_item_id' => (int) $item['order_item_id'],
            'product_id' => $item['product_id'] ?? null,
            'quantity_ordered' => $item['quantity_ordered'] ?? 0,
            'quantity_shipped' => $item['quantity_shipped'] ?? 0,
            'unit_id' => $item['unit_id'] ?? null,
            'batch_number' => $item['batch_number'] ?? null,
            'created_at' => $item['created_at'] ?? now(),
            'updated_at' => $item['updated_at'] ?? now(),
        ]);

        $createdItemIds[] = (int) $id;
    }

    private function writeReport(array $report, string $output): void
    {
        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (! is_string($json)) {
            $this->warn('Failed to encode report.');

            return;
        }

        if ($output === '') {
            $this->line($json);

            return;
        }

        $path = $this->resolvePath($output);
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($path, $json . PHP_EOL);
        $this->info('Report written to: ' . $path);
    }

    private function writeManifest(string $path, array $manifest): void
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
