<?php

namespace App\Listeners;

use App\Events\EventInviteMentionEvent;
use App\Notifications\EventInviteMention;
use Illuminate\Support\Facades\Notification;

class EventInviteMentionListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EventInviteMentionEvent $event)
    {

        Notification::send($event->notifyUser, new EventInviteMention($event->event));
    }
}
