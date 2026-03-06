<?php

namespace Modules\Performance\Listeners;

use Modules\Performance\Events\CheckInReminderEvent;
use Modules\Performance\Notifications\CheckInReminderNotification;

class CheckInReminderListener
{
    /**
     * Handle the meeting.
     *
     * @return void
     */
    public function handle(CheckInReminderEvent $reminder)
    {
        if ($reminder->owners) {
            $keyResult = $reminder->keyResult ?? null;

            foreach ($reminder->owners as $owner) {
                if ($owner->user) {
                    $owner->user->notify(new CheckInReminderNotification($reminder->objective, $keyResult));
                }
            }
        }
    }
}
