<?php

namespace App\Events\SuperAdmin;

use App\Models\SuperAdmin\OfflinePlanChange;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfflinePackageChangeRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offlinePlanChange;

    public $company;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($company, OfflinePlanChange $offlinePlanChange)
    {
        $this->offlinePlanChange = $offlinePlanChange;
        $this->company = $company;
    }
}
