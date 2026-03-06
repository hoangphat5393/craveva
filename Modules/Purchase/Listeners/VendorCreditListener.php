<?php

namespace Modules\Purchase\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Purchase\Events\VendorCreditEvent;
use Modules\Purchase\Notifications\VendorCredit;

class VendorCreditListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(VendorCreditEvent $event)
    {
        Notification::send($event->notifyUsers, new VendorCredit($event->vendorCredit));
    }
}
