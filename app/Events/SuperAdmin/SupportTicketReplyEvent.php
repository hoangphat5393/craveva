<?php

namespace App\Events\SuperAdmin;

use App\Models\SuperAdmin\SupportTicketReply;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticketReply;

    public $notifyUser;

    public function __construct(SupportTicketReply $ticketReply, $notifyUser)
    {
        $this->ticketReply = $ticketReply;
        $this->notifyUser = $notifyUser;
    }
}
