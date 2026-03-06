<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\SupportTicketReplyEvent;
use App\Models\User;
use App\Notifications\SuperAdmin\NewSupportTicketReply;
use Illuminate\Support\Facades\Notification;

class SupportTicketReplyListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SupportTicketReplyEvent $event)
    {
        if (! is_null($event->notifyUser)) {
            Notification::send($event->notifyUser, new NewSupportTicketReply($event->ticketReply));
        } else {
            Notification::send(User::allSuperAdmin(), new NewSupportTicketReply($event->ticketReply));
        }
    }
}
