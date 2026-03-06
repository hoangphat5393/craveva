<?php

namespace App\Listeners;

use App\Events\EstimateDeclinedEvent;
use App\Models\User;
use App\Notifications\EstimateDeclined;
use Illuminate\Support\Facades\Notification;

class EstimateDeclinedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EstimateDeclinedEvent $event)
    {
        $company = $event->estimate->company;
        Notification::send(User::allAdmins($company->id), new EstimateDeclined($event->estimate));
    }
}
