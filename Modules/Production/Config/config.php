<?php

return [
    'name' => 'Production',
    'phase2' => [
        /*
         * When enabled, FG outputs that exceed policy tolerances require explicit approval
         * (approved_by / approved_at) before posting FG receipt.
         */
        'enforce_variance_approval' => true,
        /*
         * When enabled, Sales DO shipping is blocked if the linked sales order has
         * production orders that are not completed yet.
         */
        'enforce_quality_lock_sales_do' => true,
        /*
         * Shadow mode for Phase 2/3 yield + UOM conversion:
         * - Keep current planned_quantity behavior unchanged.
         * - Also compute planned_quantity_shadow using unit conversion + yield factor.
         */
        'yield_uom_shadow_enabled' => true,
    ],

    'fg_quantity_policy' => [
        'defaults' => [
            /*
             * strict: total registered FG lines must not exceed planned_quantity.
             * controlled: planned + max(percent_tolerance, absolute_tolerance) is allowed without escalation;
             *             amounts beyond that tolerance may require variance_reason depending on *_require_* toggles below.
             * flexible: FG totals may exceed planned_quantity, but escalation requires variance_reason when over planned.
             */
            'policy_mode' => 'controlled',

            // Used by controlled policy (both are treated generously: max(plan * percent/100, absolute)).
            'tolerance_percent' => 5.0,
            'tolerance_absolute' => 0.0,

            'controlled_require_reason_beyond_tolerance' => true,

            /*
             * When true, FG posting is blocked whenever totals exceed tolerance, even when a variance reason is provided.
             * Keep false during pilot rollout so operators can attach an audit reason instead of reopening tolerance settings.
             */
            'controlled_block_beyond_tolerance' => false,

            'flexible_require_reason_when_over_planned' => true,
        ],
    ],
];
