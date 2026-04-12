<?php

namespace Modules\Warehouse\Contracts;

use App\Models\CreditNoteItem;

/**
 * Optional gate before posting sales-return (credit note) inbound stock.
 *
 * TODO: Wire QC workflow (accepted / reject / scrap) — return false until QC passes when implemented.
 */
interface SalesReturnInboundGateInterface
{
    public function allowInboundPosting(CreditNoteItem $item): bool;
}
