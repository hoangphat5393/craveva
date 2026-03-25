<?php

namespace Modules\Warehouse\Services;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseQueryService
{
    /**
     * Paginated stock movements ledger for the current company.
     *
     * @param  array{warehouse_id?: int|string|null, movement_type?: string|null, search?: string|null}  $filters
     */
    public function paginateStockMovements(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $companyId = auth()->user()->company_id ?? null;

        $query = StockMovement::query()
            ->with([
                'product:id,name,sku',
                'warehouseFrom:id,name',
                'warehouseTo:id,name',
            ])
            ->when($companyId !== null, fn($q) => $q->where('company_id', $companyId))
            ->when(! empty($filters['warehouse_id']), function ($q) use ($filters) {
                $wid = (int) $filters['warehouse_id'];

                return $q->where(function ($inner) use ($wid) {
                    $inner->where('warehouse_from_id', $wid)
                        ->orWhere('warehouse_to_id', $wid);
                });
            })
            ->when(! empty($filters['movement_type']), fn($q) => $q->where('movement_type', $filters['movement_type']))
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';

                return $q->whereHas('product', function ($pq) use ($term) {
                    $pq->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        return $query->paginate($perPage)->withQueryString();
    }
}
