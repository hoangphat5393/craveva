<?php

namespace Modules\CyberSecurity\Listeners;

use Illuminate\Support\Facades\RateLimiter;

class DifferentIpListener
{
    public function handle($event): void
    {
        RateLimiter::clear('cybersecurity:login'.$event->ip);
        RateLimiter::clear('cybersecurity:loginLockout'.$event->ip);
    }
}
