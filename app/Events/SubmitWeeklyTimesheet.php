<?php

namespace App\Events;

use App\Models\WeeklyTimesheet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmitWeeklyTimesheet
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $weeklyTimesheet;

    public function __construct(WeeklyTimesheet $weeklyTimesheet)
    {
        $this->weeklyTimesheet = $weeklyTimesheet;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('new-weekly-timesheets'),
        ];
    }
}
