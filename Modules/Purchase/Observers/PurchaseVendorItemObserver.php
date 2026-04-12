<?php

namespace Modules\Purchase\Observers;

use Modules\Purchase\Entities\PurchaseVendorItem;
use Modules\Warehouse\Services\VendorCreditWarehouseStockService;

class PurchaseVendorItemObserver
{
    public function created(PurchaseVendorItem $item): void
    {
        app(VendorCreditWarehouseStockService::class)->postOutboundForVendorCreditItem($item);
    }

    public function updated(PurchaseVendorItem $item): void
    {
        if (! $item->credit_id) {
            return;
        }

        if ($item->wasChanged(['quantity', 'product_id', 'warehouse_id', 'unit_id', 'type'])) {
            app(VendorCreditWarehouseStockService::class)->resyncOutboundForVendorCreditItem($item);
        }
    }

    public function deleting(PurchaseVendorItem $item): void
    {
        if (! $item->credit_id) {
            return;
        }

        app(VendorCreditWarehouseStockService::class)->reverseOutboundForVendorCreditItem($item);
    }
}
