<?php

namespace Modules\Purchase\Support;

use Modules\Purchase\Entities\SalesShipment;
use Modules\Purchase\Entities\SalesShipmentItem;

class SalesDoRuntime
{
    public static function isCutoverEnabled(): bool
    {
        // Legacy shipment tables are removed in phase 5.
        // Runtime is now permanently pinned to new Sales DO tables.
        return true;
    }

    public static function headerModelClass(): string
    {
        return self::isCutoverEnabled() ? 'Modules\\Purchase\\Entities\\SalesDo' : SalesShipment::class;
    }

    public static function itemModelClass(): string
    {
        return self::isCutoverEnabled() ? 'Modules\\Purchase\\Entities\\SalesDoItem' : SalesShipmentItem::class;
    }

    public static function headerTable(): string
    {
        return self::isCutoverEnabled() ? 'sales_dos' : 'sales_shipments';
    }

    public static function itemTable(): string
    {
        return self::isCutoverEnabled() ? 'sales_do_items' : 'sales_shipment_items';
    }

    public static function numberColumn(): string
    {
        return self::isCutoverEnabled() ? 'do_number' : 'shipment_number';
    }

    public static function dateColumn(): string
    {
        return self::isCutoverEnabled() ? 'do_date' : 'shipment_date';
    }

    public static function itemForeignKey(): string
    {
        return self::isCutoverEnabled() ? 'sales_do_id' : 'sales_shipment_id';
    }
}
