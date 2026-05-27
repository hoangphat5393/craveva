<?php

declare(strict_types=1);

namespace Modules\Production\Services;

use Modules\Production\Entities\ProductionBatch;
use Modules\Production\Support\ProductionBatchPlannedLinesPolicy;

/**
 * Writes {@see ProductionBatchConsumption} rows from the order BOM snapshot (same as manual "Create planned…" button).
 */
final class ProductionBatchPlannedLinesApplicator
{
    public function __construct(
        private readonly ProductionPlannedConsumptionFromSnapshotService $plannedFromSnapshot,
    ) {}

    public function autoApplyIfConfigured(ProductionBatch $batch): bool
    {
        if (! ProductionBatchPlannedLinesPolicy::autoApplyBomSnapshotOnBatch()) {
            return false;
        }

        return $this->applyWhenEligible($batch);
    }

    public function applyWhenEligible(ProductionBatch $batch): bool
    {
        $batch->loadMissing('order');

        if ($batch->order === null || $batch->consumptions()->exists()) {
            return false;
        }

        if ($batch->order->bom_snapshot_at === null) {
            return false;
        }

        try {
            $this->plannedFromSnapshot->applySnapshotToBatch($batch);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
