<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Entities\ProductionBatchConsumption;
use Modules\Production\Entities\ProductionOrder;

class ProductionPlannedConsumptionFromSnapshotService
{
    /**
     * Create planned RM lines from the order's BOM snapshot (no warehouse batches).
     *
     * Planned quantity per snapshot line = quantity_per_fg_unit × bom_snapshot_planned_quantity
     * (MVP: only when the order has exactly one production batch — see {@see self::assertSingleBatchOrder}.)
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

        $this->assertSingleBatchOrder($order);

        $effectivePlannedFg = (float) $snapshotPlannedQty;

        DB::transaction(function () use ($batch, $items, $order, $effectivePlannedFg): void {
            $lineOrder = -1;

            foreach ($items as $line) {
                $lineOrder++;
                $plannedQty = (float) $line->quantity_per_fg_unit * $effectivePlannedFg;

                ProductionBatchConsumption::query()->create([
                    'company_id' => $order->company_id,
                    'production_batch_id' => $batch->id,
                    'component_product_id' => (int) $line->component_product_id,
                    'warehouse_product_batch_id' => null,
                    'planned_quantity' => $plannedQty,
                    'actual_quantity' => null,
                    'unit_id' => null,
                    'line_order' => $lineOrder,
                ]);
            }
        });
    }

    protected function assertSingleBatchOrder(ProductionOrder $order): void
    {
        if ($order->batches()->count() !== 1) {
            throw new InvalidArgumentException(__('production::app.snapshotApplyRequiresSingleBatch'));
        }
    }
}
