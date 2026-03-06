<?php

namespace Modules\Purchase\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Purchase\Entities\PurchaseVendorCredit;

class VendorCreditEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $vendorCredit;

    public $notifyUsers;

    public function __construct(PurchaseVendorCredit $vendorCredit, $notifyUsers)
    {
        $this->vendorCredit = $vendorCredit;
        $this->notifyUsers = $notifyUsers;

    }
}
