<?php

namespace App\Listeners;

use App\Events\NewEstimateRequestEvent;
use App\Models\User;
use App\Notifications\NewEstimateRequest;
use Illuminate\Support\Facades\Notification;

class NewEstimateRequestListener
{
    /**
     * Handle the event.
     */
    public function handle(NewEstimateRequestEvent $event): void
    {
        $companyId = $event->estimateRequest->company->id;
        Notification::send(User::allAdmins($companyId), new NewEstimateRequest($event->estimateRequest));
    }
}
