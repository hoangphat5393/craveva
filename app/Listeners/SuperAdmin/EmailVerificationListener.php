<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\EmailVerificationEvent;
use App\Notifications\SuperAdmin\EmailVerification;

class EmailVerificationListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EmailVerificationEvent $event)
    {
        $event->user->notify(new EmailVerification($event->user));
    }
}
