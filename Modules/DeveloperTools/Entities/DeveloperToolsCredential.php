<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\DeveloperTools\Database\factories\DeveloperToolsCredentialFactory;

class DeveloperToolsCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'developer_tools_credentials';

    protected $fillable = [
        'company_id',
        'db_username',
        'db_host',
        'db_port',
        'db_database',
        'allowed_modules',
        'created_views_count',
        'generation_duration_ms',
        'last_generated_at',
        'last_generation_warnings',
        'created_by',
    ];

    protected $casts = [
        'allowed_modules' => 'array',
        'last_generated_at' => 'datetime',
    ];

    protected static function newFactory(): DeveloperToolsCredentialFactory
    {
        // return DeveloperToolsCredentialFactory::new();
    }
}
