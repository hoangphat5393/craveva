<?php

namespace App\Listeners;

use App\Events\SubmitWeeklyTimesheet;
use App\Models\User;
use App\Notifications\NewTimesheetApproval;
use Illuminate\Support\Facades\Notification;

class SubmitWeeklyTimesheetListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubmitWeeklyTimesheet $event): void
    {
        // $admins = User::allAdmins();
        $reportingManager = $event->weeklyTimesheet->user->employeeDetails->reportingTo;

        // Notification::send($admins, new NewTimesheetApproval($event->weeklyTimesheet));

        if ($reportingManager) {
            $reportingManager->notify(new NewTimesheetApproval($event->weeklyTimesheet));
        }
    }
}
