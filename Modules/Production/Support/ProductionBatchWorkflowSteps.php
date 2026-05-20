<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use Modules\Production\Entities\ProductionBatch;

/**
 * @phpstan-type WorkflowStep array{key: string, label: string, done: bool, current: bool}
 */
final class ProductionBatchWorkflowSteps
{
    /**
     * @return list<WorkflowStep>
     */
    public function forBatch(ProductionBatch $batch): array
    {
        $batch->loadMissing(['consumptions', 'outputs', 'order']);

        $hasPlannedLines = $batch->consumptions->isNotEmpty();
        $allBatchesAssigned = $hasPlannedLines
            && $batch->consumptions->every(static fn ($line): bool => $line->warehouse_product_batch_id !== null);
        $rmPosted = $batch->posted_consumptions_at !== null;
        $hasFgLines = $batch->outputs->isNotEmpty();
        $fgPosted = $batch->posted_receipt_at !== null;

        $steps = [
            [
                'key' => 'planned_lines',
                'label' => __('production::app.batchStepPlannedLines'),
                'done' => $hasPlannedLines,
                'current' => false,
            ],
            [
                'key' => 'assign_batches',
                'label' => __('production::app.batchStepAssignRmBatches'),
                'done' => $allBatchesAssigned,
                'current' => false,
            ],
            [
                'key' => 'post_rm',
                'label' => __('production::app.batchStepPostRm'),
                'done' => $rmPosted,
                'current' => false,
            ],
            [
                'key' => 'add_fg',
                'label' => __('production::app.batchStepAddFg'),
                'done' => $hasFgLines,
                'current' => false,
            ],
            [
                'key' => 'post_fg',
                'label' => __('production::app.batchStepPostFg'),
                'done' => $fgPosted,
                'current' => false,
            ],
        ];

        $markedCurrent = false;
        foreach ($steps as $index => $step) {
            if ($markedCurrent) {
                continue;
            }

            if (! $step['done']) {
                $steps[$index]['current'] = true;
                $markedCurrent = true;
            }
        }

        if (! $markedCurrent && $steps !== []) {
            $last = array_key_last($steps);
            $steps[$last]['current'] = true;
        }

        return $steps;
    }
}
