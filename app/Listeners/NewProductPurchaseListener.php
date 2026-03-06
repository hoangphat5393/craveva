<?php

namespace App\Listeners;

use App\Events\NewProductPurchaseEvent;
use App\Models\User;
use App\Notifications\NewProductPurchaseRequest;
use Illuminate\Support\Facades\Notification;

class NewProductPurchaseListener
{
    /**
     * NewProductPurchaseListener constructor.
     */
    public function __construct()
    {
        //
    }

    public function handle(NewProductPurchaseEvent $event)
    {
        $admins = User::allAdmins($event->invoice->company->id);
        Notification::send($admins, new NewProductPurchaseRequest($event->invoice));
    }
}
