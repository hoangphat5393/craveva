<?php

namespace App\Listeners;

use App\Events\EstimateRequestAcceptedEvent;
use App\Notifications\EstimateRequestAccepted;
use Illuminate\Support\Facades\Notification;

class EstimateRequestAcceptedListener
{
    /**
     * Handle the event.
     */
    public function handle(EstimateRequestAcceptedEvent $event): void
    {
        $notifiable = $event->estimateRequest->client;

        if (isset($notifiable->email)) {
            Notification::send($notifiable, new EstimateRequestAccepted($event->estimateRequest));
        }
    }
}
