<?php

namespace Modules\Purchase\Observers;

use App\Models\Payment;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Warehouse\Services\WarehouseFlowConfigService;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        $invoice = $payment->invoice;

        if (! $invoice) {
            return;
        }

        $this->adjustStock($invoice, 'minus');
    }

    public function deleting(Payment $payment)
    {

        $invoice = $payment->invoice;

        if (! $invoice) {
            return;
        }

        $this->adjustStock($invoice, 'add');
    }

    public function adjustStock($invoice, $addOrMinus)
    {
        $companyId = $invoice->company_id ? (int) $invoice->company_id : null;
        if (app(WarehouseFlowConfigService::class)->salesOutboundEnabled($companyId)) {
            return;
        }

        foreach ($invoice->items as $item) {
            if ($stock = PurchaseStockAdjustment::where('product_id', $item->product_id)->first()) {
                $stock->net_quantity += ($addOrMinus == 'add') ? $item->quantity : -$item->quantity;
                $stock->save();
            }
        }
    }
}
