<?php

return [
    'name' => 'Production',

    /*
     * Temporary UI: hide optional "Project" on production order create/edit (and detail when unset).
     * Set true when Biomixing wants project linkage on the form again.
     */
    'ui' => [
        'show_linked_project_on_order_form' => false,
        /*
         * BOM / material-requirements waste % column. Backend still stores waste_percent (default 0).
         * Set true when PM enables scrap allowance in UI again.
         */
        'show_bom_waste_percent_ui' => false,

        /*
         * "Default for manufactured product" on BOM create/edit, list column, and detail.
         * Hidden while prefill-from-SO is the only consumer; set true to restore UI.
         */
        'show_bom_default_for_manufactured_product_ui' => false,

        /*
         * BOM-first workflow (Biomixing): required BOM on orders, FG derived from BOM, AJAX recipe preview on form,
         * manual "add raw material" on batch disabled. Set false to restore legacy FG-first + optional BOM + manual batch lines.
         */
        'bom_first_workflow_enabled' => true,
        'require_bom_on_production_order' => true,
        'bom_first_disable_fg_select' => true,
        'show_bom_preview_on_order_form' => true,
        'allow_manual_batch_consumption_lines' => false,

        /*
         * Batch planned RM lines (former checklist "Step 1"):
         * - auto_apply: insert production_batch_consumptions from order BOM snapshot on release / batch open.
         * - show_* false: hide manual button + workflow step; restore via PRODUCTION_BATCH_STEP1_RESTORE_VI.md
         */
        'auto_apply_bom_snapshot_on_batch' => true,
        'show_batch_workflow_step_planned_lines' => false,
        'show_apply_planned_from_snapshot_button' => false,
    ],

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
         * Shadow mode for yield + UOM conversion (optional; requires business sign-off).
         * - Default false: Phase 1–2 run on planned_quantity only (BOM × planned FG).
         * - When true: also compute planned_quantity_shadow for comparison; see FUNC_IMPROVE/11_*.
         */
        'yield_uom_shadow_enabled' => false,
    ],

    /*
     * When true, saving a Production BOM syncs standard cost to the output FG
     * when products.cost_from_bom is enabled (Custom checkbox on product form).
     */
    'cost_sync' => [
        'bom_drives_fg_purchase_price' => env('PRODUCTION_BOM_DRIVES_FG_PURCHASE_PRICE', false),
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
