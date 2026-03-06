<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\PackageUpdateNotifyEvent;
use App\Models\Company;
use App\Notifications\SuperAdmin\PackageEmployeeIssue;
use Illuminate\Support\Facades\Notification;

class PackageUpdateNotifyListener
{
    /**
     * Handle the event.
     */
    public function handle(PackageUpdateNotifyEvent $event)
    {
        $notifyUser = Company::firstActiveAdmin($event->packageUpdateNotify->company);
        Notification::send($notifyUser, new PackageEmployeeIssue($event->packageUpdateNotify));
    }
}
