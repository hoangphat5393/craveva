<?php

namespace App\Events\SuperAdmin;

use App\Models\PackageUpdateNotify;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageUpdateNotifyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $packageUpdateNotify;

    /**
     * Create a new event instance.
     */
    public function __construct(PackageUpdateNotify $packageUpdateNotify)
    {
        $this->packageUpdateNotify = $packageUpdateNotify;
    }
}
