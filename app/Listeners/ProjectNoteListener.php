<?php

namespace App\Listeners;

use App\Events\ProjectNoteEvent;
use App\Notifications\NewProjectNote;
use Illuminate\Support\Facades\Notification;

class ProjectNoteListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ProjectNoteEvent $event)
    {
        Notification::send($event->unmentionUser, new NewProjectNote($event->project, $event));

    }
}
