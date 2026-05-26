<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Production\Entities\ProductionOrder;

class ProductionMaterialSummaryService
{
    private const STATUS_SCOPE_ACTIVE = 'active';

    private const FLOAT_TOLERANCE = 0.0000001;

    public function __construct(
        private readonly ProductionOrderMaterialRequirementsSummary $materialRequirementsSummary,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     component_product_id: int,
     *     component_name: string,
     *     rm_warehouse_id: int,
     *     rm_warehouse_name: string,
     *     total_required: float,
     *     available_stock: float,
     *     shortage_to_procure: float,
     *     affected_orders_count: int,
     *     affected_order_ids: list<int>,
     *     unit_label_base: string|null,
     * }>
     */
    public function summaries(int $companyId, array $filters = []): array
    {
        $materialId = $this->normalizeNullableInt($filters['material_id'] ?? null);
        $onlyShortage = $this->normalizeOnlyShortage($filters);

        $orders = $this->filteredOrdersQuery($companyId, $filters)
            ->with([
                'rmWarehouse:id,name',
                'bom.items.componentProduct.unit',
                'bom.items.unit',
                'bomSnapshotItems.componentProduct.unit',
                'bomSnapshotItems.unit',
            ])
            ->get();

        $aggregates = [];
        $productIdsByWarehouse = [];

        foreach ($orders as $order) {
            $warehouseId = (int) ($order->rm_warehouse_id ?? 0);

            if ($warehouseId <= 0) {
                continue;
            }

            foreach ($this->materialRequirementsSummary->demandRowsForOrder($order) as $row) {
                $componentProductId = (int) $row['component_product_id'];

                if ($materialId !== null && $componentProductId !== $materialId) {
                    continue;
                }

                $aggregateKey = $componentProductId.':'.$warehouseId;

                if (! isset($aggregates[$aggregateKey])) {
                    $aggregates[$aggregateKey] = [
                        'component_product_id' => $componentProductId,
                        'component_name' => (string) $row['component_name'],
                        'rm_warehouse_id' => $warehouseId,
                        'rm_warehouse_name' => (string) ($order->rmWarehouse?->name ?? __('app.notSet')),
                        'total_required' => 0.0,
                        'available_stock' => 0.0,
                        'shortage_to_procure' => 0.0,
                        'affected_orders_count' => 0,
                        'affected_order_ids' => [],
                        'unit_label_base' => $row['unit_label_base'],
                    ];
                }

                $aggregates[$aggregateKey]['total_required'] = round(
                    (float) $aggregates[$aggregateKey]['total_required'] + (float) $row['total_required'],
                    6,
                );
                $aggregates[$aggregateKey]['affected_order_ids'][$order->id] = (int) $order->id;
                $productIdsByWarehouse[$warehouseId][$componentProductId] = $componentProductId;
            }
        }

        foreach ($productIdsByWarehouse as $warehouseId => $productIds) {
            $availableByProduct = $this->materialRequirementsSummary->availableQuantityMapForWarehouse(
                (int) $warehouseId,
                array_values($productIds),
                $companyId,
            );

            foreach ($availableByProduct as $productId => $availableStock) {
                $aggregateKey = (int) $productId.':'.(int) $warehouseId;

                if (! isset($aggregates[$aggregateKey])) {
                    continue;
                }

                $totalRequired = (float) $aggregates[$aggregateKey]['total_required'];
                $available = round((float) $availableStock, 6);
                $shortage = max(round($totalRequired - $available, 6), 0.0);

                $aggregates[$aggregateKey]['available_stock'] = $available;
                $aggregates[$aggregateKey]['shortage_to_procure'] = $shortage;
            }
        }

        $rows = [];

        foreach ($aggregates as $aggregate) {
            $aggregate['affected_order_ids'] = array_values($aggregate['affected_order_ids']);
            $aggregate['affected_orders_count'] = count($aggregate['affected_order_ids']);

            if ($onlyShortage && (float) $aggregate['shortage_to_procure'] <= self::FLOAT_TOLERANCE) {
                continue;
            }

            $rows[] = $aggregate;
        }

        usort($rows, function (array $left, array $right): int {
            $shortageComparison = $right['shortage_to_procure'] <=> $left['shortage_to_procure'];

            if ($shortageComparison !== 0) {
                return $shortageComparison;
            }

            $warehouseComparison = strcmp((string) $left['rm_warehouse_name'], (string) $right['rm_warehouse_name']);

            if ($warehouseComparison !== 0) {
                return $warehouseComparison;
            }

            return strcmp((string) $left['component_name'], (string) $right['component_name']);
        });

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     component_product_id: int,
     *     component_name: string,
     *     rm_warehouse_id: int,
     *     rm_warehouse_name: string,
     *     total_required: float,
     *     available_stock: float,
     *     shortage_to_procure: float,
     *     affected_orders_count: int,
     *     affected_order_ids: list<int>,
     *     unit_label_base: string|null,
     * }|null
     */
    public function summaryForMaterial(int $companyId, int $materialId, int $warehouseId, array $filters = []): ?array
    {
        $summaryFilters = array_merge($filters, [
            'material_id' => $materialId,
            'warehouse_id' => $warehouseId,
            'only_shortage' => false,
        ]);

        return $this->summaries($companyId, $summaryFilters)[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     order_id: int,
     *     order_status: string,
     *     order_status_label: string,
     *     manufactured_product_name: string,
     *     planned_quantity: float,
     *     required_quantity: float,
     *     unit_label_base: string|null,
     *     source_label: string,
     *     order_url: string,
     * }>
     */
    public function detailForMaterial(int $companyId, int $materialId, int $warehouseId, array $filters = []): array
    {
        $orders = $this->filteredOrdersQuery($companyId, array_merge($filters, [
            'warehouse_id' => $warehouseId,
        ]))
            ->with([
                'outputProduct:id,name',
                'bom.items.componentProduct.unit',
                'bom.items.unit',
                'bomSnapshotItems.componentProduct.unit',
                'bomSnapshotItems.unit',
            ])
            ->get();

        $rows = [];

        foreach ($orders as $order) {
            foreach ($this->materialRequirementsSummary->demandRowsForOrder($order) as $row) {
                if ((int) $row['component_product_id'] !== $materialId) {
                    continue;
                }

                $rows[] = [
                    'order_id' => (int) $order->id,
                    'order_status' => (string) $order->status,
                    'order_status_label' => (string) __('production::app.statusLabels.'.(string) $order->status),
                    'manufactured_product_name' => (string) ($order->outputProduct?->name ?? __('app.notSet')),
                    'planned_quantity' => (float) $order->planned_quantity,
                    'required_quantity' => (float) $row['total_required'],
                    'unit_label_base' => $row['unit_label_base'],
                    'source_label' => $order->bom_snapshot_at !== null
                        ? (string) __('production::app.materialRequirementSources.snapshot')
                        : (string) __('production::app.materialRequirementSources.current_bom'),
                    'order_url' => route('production.orders.show', $order),
                ];
            }
        }

        usort($rows, static function (array $left, array $right): int {
            return $left['order_id'] <=> $right['order_id'];
        });

        return $rows;
    }

    public function normalizeStatusScope(?string $statusScope): string
    {
        return match ($statusScope) {
            'all',
            ProductionOrder::STATUS_DRAFT,
            ProductionOrder::STATUS_RELEASED,
            ProductionOrder::STATUS_IN_PROGRESS,
            ProductionOrder::STATUS_COMPLETED,
            ProductionOrder::STATUS_CANCELLED => (string) $statusScope,
            default => self::STATUS_SCOPE_ACTIVE,
        };
    }

    /**
     * @return list<string>|null
     */
    public function statusesForScope(?string $statusScope): ?array
    {
        return match ($this->normalizeStatusScope($statusScope)) {
            'all' => null,
            self::STATUS_SCOPE_ACTIVE => [
                ProductionOrder::STATUS_RELEASED,
                ProductionOrder::STATUS_IN_PROGRESS,
            ],
            default => [$this->normalizeStatusScope($statusScope)],
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function normalizeOnlyShortage(array $filters): bool
    {
        if (! array_key_exists('only_shortage', $filters)) {
            return true;
        }

        return filter_var($filters['only_shortage'], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<ProductionOrder>
     */
    protected function filteredOrdersQuery(int $companyId, array $filters): Builder
    {
        $query = ProductionOrder::query()
            ->where('company_id', $companyId)
            ->whereNotNull('rm_warehouse_id');

        $statuses = $this->statusesForScope(isset($filters['status_scope']) ? (string) $filters['status_scope'] : null);

        if ($statuses !== null) {
            $query->whereIn('status', $statuses);
        }

        $warehouseId = $this->normalizeNullableInt($filters['warehouse_id'] ?? null);
        if ($warehouseId !== null) {
            $query->where('rm_warehouse_id', $warehouseId);
        }

        return $query->orderBy('id');
    }

    protected function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = (int) $value;

        return $normalized > 0 ? $normalized : null;
    }
}
