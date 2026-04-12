<?php

namespace Modules\Warehouse\Services;

use App\Models\CreditNoteItem;
use Modules\Warehouse\Contracts\SalesReturnInboundGateInterface;

class AllowAllSalesReturnInboundGate implements SalesReturnInboundGateInterface
{
    public function allowInboundPosting(CreditNoteItem $item): bool
    {
        return true;
    }
}
