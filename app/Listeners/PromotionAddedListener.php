<?php

namespace App\Listeners;

use App\Events\PromotionAddedEvent;
use App\Notifications\PromotionAdded;
use Illuminate\Support\Facades\Notification;

class PromotionAddedListener
{
    /**
     * Handle the event.
     */
    public function handle(PromotionAddedEvent $event): void
    {
        Notification::send($event->user, new PromotionAdded($event->promotion));
    }
}
