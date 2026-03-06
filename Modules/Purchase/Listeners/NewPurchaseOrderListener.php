<?php

namespace Modules\Purchase\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Purchase\Events\NewPurchaseOrderEvent as EventsNewPurchaseOrderEvent;
use Modules\Purchase\Notifications\NewPurchaseOrder;

class NewPurchaseOrderListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EventsNewPurchaseOrderEvent $event)
    {
        if ($event->notifyUser->email != null) {
            Notification::send($event->notifyUser, new NewPurchaseOrder($event->order));
        }

    }
}
