<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrnMigrateDataCommand extends Command
{
    protected $signature = 'purchase:grn-migrate-data
        {--company_id= : Limit migration to one company}
        {--chunk=200 : Batch size when execute}
        {--sample=20 : Number of sample records in report}
        {--output= : Optional JSON report path}
        {--execute : Execute data migration (default is dry-run)}
        {--force : Required with --execute to avoid accidental run}';

    protected $description = 'Phase 4 data migration from delivery_orders to grns (dry-run by default)';

    public function handle(): int
    {
        if (! $this->validateTables()) {
            return self::FAILURE;
        }

        $companyId = $this->option('company_id');
        $sampleSize = max(1, (int) $this->option('sample'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $execute = (bool) $this->option('execute');

        $scopeQuery = DB::table('delivery_orders');
        if ($companyId !== null && $companyId !== '') {
            $scopeQuery->where('company_id', (int) $companyId);
        }

        $allIds = (clone $scopeQuery)->pluck('id')->all();
        $pendingIds = (clone $scopeQuery)
            ->whereNotIn('id', function ($q) {
                $q->from('grns')
                    ->select('legacy_delivery_order_id')
                    ->whereNotNull('legacy_delivery_order_id');
            })
            ->pluck('id')
            ->all();

        $report = [
            'command' => 'purchase:grn-migrate-data',
            'generated_at' => now()->toIso8601String(),
            'mode' => $execute ? 'execute' : 'dry-run',
            'scope' => [
                'company_id' => ($companyId === null || $companyId === '') ? null : (int) $companyId,
                'chunk' => $chunkSize,
            ],
            'source' => [
                'headers_count' => count($allIds),
                'items_count' => (int) DB::table('delivery_order_items')->whereIn('delivery_order_id', $allIds ?: [0])->count(),
            ],
            'target' => [
                'headers_migrated_count' => (int) DB::table('grns')
                    ->whereNotNull('legacy_delivery_order_id')
                    ->when($companyId !== null && $companyId !== '', fn($q) => $q->where('company_id', (int) $companyId))
                    ->count(),
                'items_migrated_count' => (int) DB::table('grn_items')
                    ->whereNotNull('legacy_delivery_order_item_id')
                    ->whereIn('grn_id', function ($q) use ($companyId) {
                        $q->from('grns')
                            ->select('id')
                            ->whereNotNull('legacy_delivery_order_id');
                        if ($companyId !== null && $companyId !== '') {
                            $q->where('company_id', (int) $companyId);
                        }
                    })
                    ->count(),
            ],
            'pending' => [
                'headers_count' => count($pendingIds),
                'items_count' => (int) DB::table('delivery_order_items')->whereIn('delivery_order_id', $pendingIds ?: [0])->count(),
            ],
            'samples' => DB::table('delivery_orders')
                ->whereIn('id', $pendingIds ?: [0])
                ->orderBy('id')
                ->limit($sampleSize)
                ->get(['id', 'company_id', 'delivery_number', 'delivery_date', 'status'])
                ->toArray(),
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
            DB::transaction(function () use ($pendingIds, $chunkSize, &$createdHeaderIds, &$createdItemIds): void {
                collect($pendingIds)->chunk($chunkSize)->each(function ($idChunk) use (&$createdHeaderIds, &$createdItemIds): void {
                    $headers = DB::table('delivery_orders')
                        ->whereIn('id', $idChunk->all())
                        ->orderBy('id')
                        ->get();

                    foreach ($headers as $header) {
                        $targetHeaderId = $this->findOrCreateTargetHeader((array) $header, $createdHeaderIds);
                        $items = DB::table('delivery_order_items')
                            ->where('delivery_order_id', (int) $header->id)
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
        $manifestRelative = 'storage/app/reports/grn-migrate-manifest-' . $runId . '.json';
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
            'command' => 'purchase:grn-migrate-data',
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
        $required = ['delivery_orders', 'delivery_order_items', 'grns', 'grn_items'];
        $missing = collect($required)->filter(fn($table) => ! Schema::hasTable($table))->values()->all();
        if ($missing !== []) {
            $this->error('Required tables not found: ' . implode(', ', $missing) . '.');
            $legacyMissing = array_values(array_intersect($missing, ['delivery_orders', 'delivery_order_items']));
            if ($legacyMissing !== [] && Schema::hasTable('grns')) {
                $this->line('Nothing to migrate: this command only copies from legacy Delivery Order tables.');
                $this->line('If those tables were dropped (phase 5 / fresh DB), create GRNs in the app or restore a backup that still has delivery_orders, then run this command.');
            }

            return false;
        }

        return true;
    }

    private function findOrCreateTargetHeader(array $header, array &$createdHeaderIds): int
    {
        $existingId = DB::table('grns')
            ->where('legacy_delivery_order_id', (int) $header['id'])
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        $id = DB::table('grns')->insertGetId([
            'legacy_delivery_order_id' => (int) $header['id'],
            'company_id' => $header['company_id'] ?? null,
            'purchase_order_id' => $header['purchase_order_id'] ?? null,
            'type' => $header['type'] ?? null,
            'grn_number' => (string) ($header['delivery_number'] ?? ''),
            'grn_date' => $header['delivery_date'] ?? null,
            'warehouse_id' => $header['warehouse_id'] ?? null,
            'status' => (string) ($header['status'] ?? 'draft'),
            'inbound_stock_applied' => (int) ($header['inbound_stock_applied'] ?? 0),
            'erp_shipment_reference' => $header['erp_shipment_reference'] ?? null,
            'wms_shipment_reference' => $header['wms_shipment_reference'] ?? null,
            'delivery_fee' => $header['delivery_fee'] ?? null,
            'created_by' => $header['created_by'] ?? null,
            'updated_by' => $header['updated_by'] ?? null,
            'created_at' => $header['created_at'] ?? now(),
            'updated_at' => $header['updated_at'] ?? now(),
        ]);

        $createdHeaderIds[] = (int) $id;

        return (int) $id;
    }

    private function findOrCreateTargetItem(int $targetHeaderId, array $item, array &$createdItemIds): void
    {
        $existingId = DB::table('grn_items')
            ->where('legacy_delivery_order_item_id', (int) $item['id'])
            ->value('id');

        if ($existingId) {
            return;
        }

        $id = DB::table('grn_items')->insertGetId([
            'grn_id' => $targetHeaderId,
            'legacy_delivery_order_item_id' => (int) $item['id'],
            'purchase_item_id' => $item['purchase_item_id'] ?? null,
            'product_id' => $item['product_id'] ?? null,
            'batch_number' => $item['batch_number'] ?? null,
            'expiry_date' => $item['expiry_date'] ?? null,
            'picking_rule_applied' => $item['picking_rule_applied'] ?? null,
            'quantity_ordered' => $item['quantity_ordered'] ?? 0,
            'quantity_received' => $item['quantity_received'] ?? 0,
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
