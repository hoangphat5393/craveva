<?php

namespace App\Observers;

use App\Models\CreditNoteItem;
use Modules\Warehouse\Services\CreditNoteWarehouseStockService;

class CreditNoteItemObserver
{
    public function created(CreditNoteItem $item): void
    {
        app(CreditNoteWarehouseStockService::class)->postInboundForCreditNoteItem($item);
    }
}
