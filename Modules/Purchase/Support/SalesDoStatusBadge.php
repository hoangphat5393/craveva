<?php

namespace Modules\Purchase\Support;

class SalesDoStatusBadge
{
    public static function badgeClass(string $status): string
    {
        return match ($status) {
            'cancelled' => 'badge-danger',
            'delivered', 'shipped' => 'badge-success',
            'confirmed' => 'badge-info',
            'draft' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public static function html(string $status): string
    {
        $label = e(trans('purchase::modules.salesShipment.' . $status));

        return '<span class="badge ' . self::badgeClass($status) . '">' . $label . '</span>';
    }
}
