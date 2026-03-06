<?php

namespace App\Listeners;

use App\Events\NewExpenseRecurringEvent;
use App\Notifications\ExpenseRecurringStatus;
use App\Notifications\NewExpenseRecurringMember;
use Illuminate\Support\Facades\Notification;

class NewExpenseRecurringListener
{
    /**
     * NewExpenseRecurringListener constructor.
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
    public function handle(NewExpenseRecurringEvent $event)
    {
        if ($event->status == 'status') {
            Notification::send($event->expense->user, new ExpenseRecurringStatus($event->expense));
        } else {
            Notification::send($event->expense->user, new NewExpenseRecurringMember($event->expense));
        }
    }
}
