<?php

namespace App\Listeners;

use App\Events\NewOrderEvent;
use App\Models\User;
use App\Notifications\NewOrder;
use Illuminate\Support\Facades\Notification;

class NewOrderListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewOrderEvent $event)
    {
        Notification::send($event->notifyUser, new NewOrder($event->order));
        Notification::send(User::allAdmins($event->order->company->id), new NewOrder($event->order));
    }
}
