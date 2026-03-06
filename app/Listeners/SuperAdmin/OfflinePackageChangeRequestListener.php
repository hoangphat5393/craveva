<?php

namespace App\Listeners\SuperAdmin;

use App\Events\SuperAdmin\OfflinePackageChangeRequestEvent;
use App\Models\User;
use App\Notifications\SuperAdmin\OfflinePackageChangeRequest;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Notification;

class OfflinePackageChangeRequestListener
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(OfflinePackageChangeRequestEvent $event)
    {
        $generatedBy = User::withoutGlobalScope(CompanyScope::class)->whereNull('company_id')->get();
        Notification::send($generatedBy, new OfflinePackageChangeRequest($event->company, $event->offlinePlanChange));
    }
}
