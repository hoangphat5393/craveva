<?php

namespace App\Listeners;

use App\Events\NewUserEvent;
use App\Notifications\NewUser;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NewUserListener
{
    public function handle(NewUserEvent $event)
    {
        try {
            Notification::send($event->user, new NewUser($event->user, $event->password, $event->clientSignup));
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
