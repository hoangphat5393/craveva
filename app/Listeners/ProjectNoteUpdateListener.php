<?php

namespace App\Listeners;

use App\Events\ProjectNoteUpdateEvent;
use App\Notifications\ProjectNoteUpdated;
use Illuminate\Support\Facades\Notification;

class ProjectNoteUpdateListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ProjectNoteUpdateEvent $event)
    {
        Notification::send($event->notifyUser, new ProjectNoteUpdated($event->project, $event->projectNote));
    }
}
