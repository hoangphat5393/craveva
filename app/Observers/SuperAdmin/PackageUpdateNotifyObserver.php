<?php

namespace App\Observers\SuperAdmin;

use App\Events\SuperAdmin\PackageUpdateNotifyEvent;
use App\Models\PackageUpdateNotify;

class PackageUpdateNotifyObserver
{
    public function created(PackageUpdateNotify $packageUpdateNotify)
    {
        if (! isRunningInConsoleOrSeeding()) {
            event(new PackageUpdateNotifyEvent($packageUpdateNotify));
        }
    }
}
