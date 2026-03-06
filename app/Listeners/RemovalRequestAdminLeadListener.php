<?php

namespace App\Listeners;

use App\Events\RemovalRequestAdminLeadEvent;
use App\Models\User;
use App\Notifications\RemovalRequestAdminNotification;
use Illuminate\Support\Facades\Notification;

class RemovalRequestAdminLeadListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    // phpcs:ignore
    public function handle(RemovalRequestAdminLeadEvent $event)
    {
        Notification::send(User::allAdmins($event->removalRequestLead->company->id), new RemovalRequestAdminNotification);
    }
}
