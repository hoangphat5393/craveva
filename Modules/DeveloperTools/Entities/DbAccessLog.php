<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Model;

class DbAccessLog extends Model
{
    protected $table = 'developer_tools_db_access_logs';

    protected $fillable = [
        'company_id',
        'db_username',
        'db_database',
        'requested_modules',
        'allowed_tables',
        'allowed_tables_count',
        'created_views_count',
        'duration_ms',
        'status',
        'warnings',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'requested_modules' => 'array',
        'allowed_tables' => 'array',
    ];
}
