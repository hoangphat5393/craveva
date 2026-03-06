<?php

namespace App\Events;

use App\Models\Contract;
use App\Models\ContractSign;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractSignedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contract;

    public $contractSign;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Contract $contract, ContractSign $contractSign)
    {
        $this->contract = $contract;
        $this->contractSign = $contractSign;
    }
}
