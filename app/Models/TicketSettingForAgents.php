<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketSettingForAgents extends BaseModel
{
    use HasCompany, HasFactory;

    protected $table = 'ticket_settings_for_agents';

    protected $fillable = [
        'ticket_scope',
        'group_id',
        'updated_by',
    ];

    protected $casts = [
        'group_id' => 'array',
    ];
}
