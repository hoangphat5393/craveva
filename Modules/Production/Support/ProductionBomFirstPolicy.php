<?php

declare(strict_types=1);

namespace Modules\Production\Support;

/**
 * BOM-first production order workflow (Biomixing): required BOM, FG from BOM, preview on form, no manual batch RM lines.
 */
final class ProductionBomFirstPolicy
{
    public static function enabled(): bool
    {
        return (bool) config('production.ui.bom_first_workflow_enabled', false);
    }

    public static function requireBomOnOrder(): bool
    {
        return self::enabled() && (bool) config('production.ui.require_bom_on_production_order', true);
    }

    public static function bomFirstDisableFgSelect(): bool
    {
        return self::enabled() && (bool) config('production.ui.bom_first_disable_fg_select', true);
    }

    public static function showBomPreviewOnOrderForm(): bool
    {
        return self::enabled() && (bool) config('production.ui.show_bom_preview_on_order_form', true);
    }

    public static function allowManualBatchConsumptionLines(): bool
    {
        if (! self::enabled()) {
            return true;
        }

        return (bool) config('production.ui.allow_manual_batch_consumption_lines', false);
    }
}
