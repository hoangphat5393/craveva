<?php

namespace Modules\Warehouse\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\WarehouseSyncReconciliationLog;

class WarehouseReconciliationService
{
    /**
     * @return array<string, mixed>
     */
    public function generateDailySummary(string $reportDate, ?int $companyId = null): array
    {
        $query = StockMovement::query()->whereDate('created_at', $reportDate);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $totalMovements = (clone $query)->count();
        $totalInbound = (clone $query)->where('movement_type', 'inbound')->count();
        $totalOutbound = (clone $query)->where('movement_type', 'outbound')->count();
        $duplicateGroups = (clone $query)
            ->selectRaw('movement_type, reference_type, reference_id, product_id, warehouse_from_id, warehouse_to_id, COUNT(*) as duplicate_count')
            ->groupBy('movement_type', 'reference_type', 'reference_id', 'product_id', 'warehouse_from_id', 'warehouse_to_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(fn ($row) => [
                'movement_type' => $row->movement_type,
                'reference_type' => $row->reference_type,
                'reference_id' => $row->reference_id,
                'product_id' => $row->product_id,
                'warehouse_from_id' => $row->warehouse_from_id,
                'warehouse_to_id' => $row->warehouse_to_id,
                'duplicate_count' => (int) $row->duplicate_count,
            ])->values()->all();

        $summary = [
            'report_date' => $reportDate,
            'company_id' => $companyId,
            'totals' => [
                'movements' => $totalMovements,
                'inbound' => $totalInbound,
                'outbound' => $totalOutbound,
                'duplicate_groups' => count($duplicateGroups),
            ],
            'duplicates' => $duplicateGroups,
        ];

        if (class_exists(WarehouseSyncReconciliationLog::class) && Schema::hasTable('warehouse_sync_reconciliation_logs')) {
            WarehouseSyncReconciliationLog::query()->create([
                'company_id' => $companyId,
                'report_date' => $reportDate,
                'report_type' => 'daily',
                'summary_json' => json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        return $summary;
    }

    /**
     * Compares legacy snapshot rows (`warehouse_product_stock`) to summed batch rows per warehouse/product.
     *
     * Uses `config('warehouse.inventory_reconciliation')`: `equality_epsilon` for mismatch detection (same as legacy 0.0001 default),
     * and `warning_absolute_delta` (floored to at least epsilon) to flag materially large deltas.
     *
     * @return array{
     *     mismatch_count: int,
     *     significant_mismatch_count: int,
     *     equality_epsilon: float,
     *     warning_absolute_delta: float,
     *     samples: list<array<string, mixed>>
     * }
     */
    public function inventorySnapshotVsBatchTotals(int $companyId, int $maxSamples = 15): array
    {
        /** @var array{equality_epsilon?: float|int|string, warning_absolute_delta?: float|int|string} $reconciliation */
        $reconciliation = config('warehouse.inventory_reconciliation', []);
        $epsilon = max(0.0, (float) ($reconciliation['equality_epsilon'] ?? 0.0001));
        $warningAbsolute = max($epsilon, (float) ($reconciliation['warning_absolute_delta'] ?? 0.01));
        $productTable = (new Product)->getTable();
        $hasProducts = Schema::hasTable($productTable);

        $batchSums = DB::table('warehouse_product_batches')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_product_batches.warehouse_id')
            ->where('warehouses.company_id', $companyId)
            ->groupBy('warehouse_product_batches.warehouse_id', 'warehouse_product_batches.product_id')
            ->selectRaw('warehouse_product_batches.warehouse_id as warehouse_id')
            ->selectRaw('warehouse_product_batches.product_id as product_id')
            ->selectRaw('SUM(warehouse_product_batches.quantity) as batches_quantity')
            ->get()
            ->keyBy(fn ($row) => $row->warehouse_id.'-'.$row->product_id);

        $snapshots = DB::table('warehouse_product_stock')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_product_stock.warehouse_id')
            ->where('warehouses.company_id', $companyId)
            ->select([
                'warehouse_product_stock.warehouse_id as warehouse_id',
                'warehouse_product_stock.product_id as product_id',
                'warehouse_product_stock.quantity as snapshot_quantity',
            ])
            ->get()
            ->keyBy(fn ($row) => $row->warehouse_id.'-'.$row->product_id);

        $productNameById = [];
        if ($hasProducts) {
            $productIds = collect($snapshots->keys()->merge($batchSums->keys()))
                ->map(fn ($k) => (int) explode('-', (string) $k)[1])
                ->unique()
                ->filter()
                ->values()
                ->all();
            if ($productIds !== []) {
                $productNameById = Product::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->whereIn('id', $productIds)
                    ->pluck('name', 'id')
                    ->all();
            }
        }

        $mismatches = [];
        foreach ($snapshots as $key => $row) {
            $batchRow = $batchSums[$key] ?? null;
            $batchesQty = $batchRow ? (float) $batchRow->batches_quantity : 0.0;
            $snapQty = (float) $row->snapshot_quantity;
            if (abs($batchesQty - $snapQty) > $epsilon) {
                $pid = (int) $row->product_id;
                $delta = $snapQty - $batchesQty;
                $mismatches[] = [
                    'warehouse_id' => (int) $row->warehouse_id,
                    'product_id' => $pid,
                    'product_name' => $productNameById[$pid] ?? null,
                    'snapshot_quantity' => $snapQty,
                    'batches_quantity' => $batchesQty,
                    'delta' => $delta,
                    'is_significant' => abs($delta) >= $warningAbsolute,
                ];
            }
        }

        foreach ($batchSums as $key => $row) {
            if ($snapshots->has($key)) {
                continue;
            }
            $batchesQty = (float) $row->batches_quantity;
            if (abs($batchesQty) <= $epsilon) {
                continue;
            }
            $wid = (int) $row->warehouse_id;
            $pid = (int) $row->product_id;
            $delta = 0.0 - $batchesQty;
            $mismatches[] = [
                'warehouse_id' => $wid,
                'product_id' => $pid,
                'product_name' => $productNameById[$pid] ?? null,
                'snapshot_quantity' => 0.0,
                'batches_quantity' => $batchesQty,
                'delta' => $delta,
                'is_significant' => abs($delta) >= $warningAbsolute,
            ];
        }

        usort($mismatches, static fn ($a, $b) => abs($b['delta']) <=> abs($a['delta']));

        $significantMismatchCount = 0;
        foreach ($mismatches as $row) {
            if (($row['is_significant'] ?? false) === true) {
                $significantMismatchCount++;
            }
        }

        return [
            'mismatch_count' => count($mismatches),
            'significant_mismatch_count' => $significantMismatchCount,
            'equality_epsilon' => $epsilon,
            'warning_absolute_delta' => $warningAbsolute,
            'samples' => array_slice($mismatches, 0, $maxSamples),
        ];
    }
}
