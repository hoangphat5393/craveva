<?php

declare(strict_types=1);

namespace Modules\Production\Support;

use Modules\Production\Entities\ProductionOrder;

/**
 * Read-only status badge HTML for production order lists and detail headers.
 *
 * @see FUNC_LOGIC/DESIGN_BACKEND_UI_UX_VI.md §5.4
 */
final class ProductionOrderStatusBadge
{
    /** @var array<string, string> status value => Bootstrap 4 badge-* suffix */
    private const VARIANT_BY_STATUS = [
        ProductionOrder::STATUS_DRAFT => 'secondary',
        ProductionOrder::STATUS_RELEASED => 'info',
        ProductionOrder::STATUS_IN_PROGRESS => 'warning',
        ProductionOrder::STATUS_COMPLETED => 'success',
        ProductionOrder::STATUS_CANCELLED => 'danger',
    ];

    public static function variant(string $status): string
    {
        return self::VARIANT_BY_STATUS[$status] ?? 'secondary';
    }

    public static function label(string $status): string
    {
        $key = 'production::app.statusLabels.' . $status;
        $label = __($key);

        return $label !== $key ? $label : ucfirst(str_replace('_', ' ', $status));
    }

    public static function html(string $status): string
    {
        $variant = self::variant($status);
        $label = e(self::label($status));

        return '<span class="badge badge-' . $variant . '">' . $label . '</span>';
    }
}
