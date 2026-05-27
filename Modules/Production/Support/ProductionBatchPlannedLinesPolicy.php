<?php

declare(strict_types=1);

namespace Modules\Production\Support;

/**
 * Batch UI/workflow for planned RM lines from order BOM snapshot (former "Step 1").
 *
 * @see FUNC_LOGIC/PRODUCTION_BATCH_STEP1_RESTORE_VI.md
 */
final class ProductionBatchPlannedLinesPolicy
{
    public static function autoApplyBomSnapshotOnBatch(): bool
    {
        return (bool) config('production.ui.auto_apply_bom_snapshot_on_batch', true);
    }

    public static function showBatchWorkflowStepPlannedLines(): bool
    {
        return (bool) config('production.ui.show_batch_workflow_step_planned_lines', false);
    }

    public static function showApplyPlannedFromSnapshotButton(): bool
    {
        return (bool) config('production.ui.show_apply_planned_from_snapshot_button', false);
    }
}
