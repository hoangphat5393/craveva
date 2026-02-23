<?php

namespace Modules\CyberSecurity\Listeners;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Modules\CyberSecurity\Entities\CyberSecurity;
use Modules\CyberSecurity\Notifications\DifferentIpNotification;

class DifferentIpListener
{

    public function handle($event): void
    {
        RateLimiter::clear('cybersecurity:login' . $event->ip);
        RateLimiter::clear('cybersecurity:loginLockout' . $event->ip);
    }
}
