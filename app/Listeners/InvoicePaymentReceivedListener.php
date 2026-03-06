<?php

namespace App\Listeners;

use App\Events\InvoicePaymentReceivedEvent;
use App\Models\User;
use App\Notifications\InvoicePaymentReceived;
use Illuminate\Support\Facades\Notification;

class InvoicePaymentReceivedListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(InvoicePaymentReceivedEvent $event)
    {
        Notification::send(User::allAdmins($event->payment->company->id), new InvoicePaymentReceived($event->payment));
    }
}
