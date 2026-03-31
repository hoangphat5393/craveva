<?php

namespace Modules\Purchase\Support;

class GrnRuntime
{
    public static function isCutoverEnabled(): bool
    {
        return (bool) config('purchase.do_grn_cutover_enabled', false);
    }

    public static function headerModelClass(): string
    {
        return self::isCutoverEnabled() ? 'App\\Models\\Grn' : 'App\\Models\\DeliveryOrder';
    }

    public static function itemModelClass(): string
    {
        return self::isCutoverEnabled() ? 'Modules\\Purchase\\Entities\\GrnItem' : 'Modules\\Purchase\\Entities\\DeliveryOrderItem';
    }

    public static function headerTable(): string
    {
        return self::isCutoverEnabled() ? 'grns' : 'delivery_orders';
    }

    public static function itemTable(): string
    {
        return self::isCutoverEnabled() ? 'grn_items' : 'delivery_order_items';
    }

    public static function numberColumn(): string
    {
        return self::isCutoverEnabled() ? 'grn_number' : 'delivery_number';
    }

    public static function dateColumn(): string
    {
        return self::isCutoverEnabled() ? 'grn_date' : 'delivery_date';
    }

    public static function itemForeignKey(): string
    {
        return self::isCutoverEnabled() ? 'grn_id' : 'delivery_order_id';
    }
}
