<?php

namespace Modules\Warehouse\Services;

use App\Models\StockMovement;
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
            ->map(fn($row) => [
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

        if (class_exists(WarehouseSyncReconciliationLog::class) && \Illuminate\Support\Facades\Schema::hasTable('warehouse_sync_reconciliation_logs')) {
            WarehouseSyncReconciliationLog::query()->create([
                'company_id' => $companyId,
                'report_date' => $reportDate,
                'report_type' => 'daily',
                'summary_json' => json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        return $summary;
    }
}
