<?php

namespace App\Listeners;

use App\Events\OrderUpdatedEvent;
use App\Models\User;
use App\Notifications\OrderUpdated;
use Illuminate\Support\Facades\Notification;

class OrderUpdatedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(OrderUpdatedEvent $event)
    {
        Notification::send($event->notifyUser, new OrderUpdated($event->order));
        Notification::send(User::allAdmins($event->order->company->id), new OrderUpdated($event->order));
    }
}
