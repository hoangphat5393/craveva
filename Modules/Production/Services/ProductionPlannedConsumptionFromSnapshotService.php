<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchConsumption;
use Modules\Production\Entities\ProductionOrder;
use Modules\Warehouse\Services\WarehouseUnitConversionService;

class ProductionPlannedConsumptionFromSnapshotService
{
    public function __construct(
        protected WarehouseUnitConversionService $unitConversionService
    ) {}

    /**
     * Create planned RM lines from the order's BOM snapshot (no warehouse batches).
     *
     * Planned quantity per snapshot line = quantity_per_fg_unit × **effective planned FG units for this batch**.
     * With **multiple** production batches on the same order, the snapshot planned FG is **split equally**
     * across all batches (each batch receives {@see self::effectivePlannedFgUnitsForBatch}).
     *
     * @throws InvalidArgumentException
     */
    public function applySnapshotToBatch(ProductionBatch $batch): void
    {
        $batch->loadMissing('order');

        $order = $batch->order;
        if ($order === null) {
            throw new InvalidArgumentException(__('production::app.missingOrderOnBatch'));
        }

        if (! in_array($order->status, [ProductionOrder::STATUS_RELEASED, ProductionOrder::STATUS_IN_PROGRESS], true)) {
            throw new InvalidArgumentException(__('production::app.snapshotApplyRequiresReleasedOrder'));
        }

        if ($order->bom_snapshot_at === null) {
            throw new InvalidArgumentException(__('production::app.noBomSnapshot'));
        }

        if ($batch->consumptions()->exists()) {
            throw new InvalidArgumentException(__('production::app.snapshotApplyRequiresEmptyConsumptions'));
        }

        $snapshotPlannedQty = $order->bom_snapshot_planned_quantity;
        if ($snapshotPlannedQty === null || (float) $snapshotPlannedQty <= 0) {
            throw new InvalidArgumentException(__('production::app.noBomSnapshot'));
        }

        $items = $order->bomSnapshotItems()->orderBy('sort_order')->orderBy('id')->get();

        if ($items->isEmpty()) {
            throw new InvalidArgumentException(__('production::app.noBomSnapshot'));
        }

        $effectivePlannedFg = $this->effectivePlannedFgUnitsForBatch($order);

        DB::transaction(function () use ($batch, $items, $order, $effectivePlannedFg): void {
            $lineOrder = -1;

            foreach ($items as $line) {
                $lineOrder++;
                $plannedQty = (float) $line->quantity_per_fg_unit * $effectivePlannedFg;
                $plannedQtyShadow = $this->resolvePlannedQuantityShadow($order, $line, $effectivePlannedFg, $plannedQty);

                ProductionBatchConsumption::query()->create([
                    'company_id' => $order->company_id,
                    'production_batch_id' => $batch->id,
                    'component_product_id' => (int) $line->component_product_id,
                    'warehouse_product_batch_id' => null,
                    'planned_quantity' => $plannedQty,
                    'planned_quantity_shadow' => $plannedQtyShadow['quantity'],
                    'shadow_basis' => $plannedQtyShadow['basis'],
                    'actual_quantity' => null,
                    'unit_id' => $line->unit_id !== null ? (int) $line->unit_id : null,
                    'line_order' => $lineOrder,
                ]);
            }
        });
    }

    /**
     * Equal split of {@see ProductionOrder::$bom_snapshot_planned_quantity} across all batches on the order.
     */
    protected function effectivePlannedFgUnitsForBatch(ProductionOrder $order): float
    {
        $snapshotPlannedQty = $order->bom_snapshot_planned_quantity;
        if ($snapshotPlannedQty === null || (float) $snapshotPlannedQty <= 0) {
            throw new InvalidArgumentException(__('production::app.noBomSnapshot'));
        }

        $batchCount = $order->batches()->count();
        if ($batchCount < 1) {
            throw new InvalidArgumentException(__('production::app.missingOrderOnBatch'));
        }

        return (float) $snapshotPlannedQty / $batchCount;
    }

    /**
     * @return array{quantity:float|null,basis:array<string,mixed>|null}
     */
    protected function resolvePlannedQuantityShadow(ProductionOrder $order, mixed $line, float $effectivePlannedFg, float $fallbackQty): array
    {
        if (! (bool) config('production.phase2.yield_uom_shadow_enabled', false)) {
            return ['quantity' => null, 'basis' => null];
        }

        $yieldFactor = $this->normalizeYieldFactor($line->yield_factor ?? null);
        $unitId = $line->unit_id !== null ? (int) $line->unit_id : null;
        $quantityPerFgUnitBase = $line->quantity_per_fg_unit_base_shadow;

        if ($quantityPerFgUnitBase === null) {
            $quantityPerFgUnitBase = $this->unitConversionService->convertToBase(
                (int) $order->company_id,
                (int) $line->component_product_id,
                (float) $line->quantity_per_fg_unit,
                $unitId,
            ) / $yieldFactor;
        }

        return [
            'quantity' => (float) $quantityPerFgUnitBase * $effectivePlannedFg,
            'basis' => [
                'quantity_per_fg_unit_raw' => (float) $line->quantity_per_fg_unit,
                'quantity_per_fg_unit_base' => (float) $quantityPerFgUnitBase,
                'unit_id' => $unitId,
                'yield_factor' => $yieldFactor,
                'fallback_qty' => $fallbackQty,
            ],
        ];
    }

    protected function normalizeYieldFactor(mixed $value): float
    {
        $yieldFactor = (float) ($value ?? 1.0);

        if ($yieldFactor <= 0) {
            return 1.0;
        }

        return $yieldFactor;
    }
}
