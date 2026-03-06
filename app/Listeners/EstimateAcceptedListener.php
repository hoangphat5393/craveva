<?php

namespace App\Listeners;

use App\Events\EstimateAcceptedEvent;
use App\Models\User;
use App\Notifications\EstimateAccepted;
use Illuminate\Support\Facades\Notification;

class EstimateAcceptedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EstimateAcceptedEvent $event)
    {
        $company = $event->estimate->company;
        Notification::send(User::allAdmins($company->id), new EstimateAccepted($event->estimate));
    }
}
