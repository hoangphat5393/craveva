<?php

declare(strict_types=1);

namespace Modules\Production\Support;

/**
 * Compact view action button for production tables (icon + label).
 */
final class ProductionViewButton
{
    public static function html(string $url, ?string $label = null): string
    {
        $label = $label ?? (string) __('app.view');

        return '<a href="'.e($url).'" class="btn btn-sm btn-secondary rounded f-13">'
            .'<i class="fa fa-eye mr-1"></i>'.e($label).'</a>';
    }
}
