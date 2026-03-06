<?php

namespace App\Events\SuperAdmin;

use App\Models\SuperAdmin\OfflinePlanChange;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfflinePackageChangeConfirmationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offlinePlanChange;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OfflinePlanChange $offlinePlanChange)
    {
        $this->offlinePlanChange = $offlinePlanChange;
    }
}
