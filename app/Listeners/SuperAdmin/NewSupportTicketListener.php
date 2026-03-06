<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\NewSupportTicketEvent;
use App\Notifications\SuperAdmin\NewSupportTicket;
use Illuminate\Support\Facades\Notification;

class NewSupportTicketListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(NewSupportTicketEvent $event)
    {
        if (! is_null($event->notifyUser)) {
            Notification::send($event->notifyUser, new NewSupportTicket($event->ticket));
        }
    }
}
