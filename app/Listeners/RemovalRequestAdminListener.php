<?php

namespace App\Listeners;

use App\Events\RemovalRequestAdminEvent;
use App\Models\User;
use App\Notifications\RemovalRequestAdminNotification;
use Illuminate\Support\Facades\Notification;

class RemovalRequestAdminListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    // phpcs:ignore
    public function handle(RemovalRequestAdminEvent $event)
    {
        Notification::send(User::allAdmins(), new RemovalRequestAdminNotification);
    }
}
