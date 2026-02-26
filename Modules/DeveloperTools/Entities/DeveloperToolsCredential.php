<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DeveloperTools\Database\factories\DeveloperToolsCredentialFactory;

class DeveloperToolsCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'developer_tools_credentials';
    protected $fillable = ['company_id', 'db_username', 'db_host', 'db_port', 'db_database', 'created_by'];
    
    protected static function newFactory(): DeveloperToolsCredentialFactory
    {
        //return DeveloperToolsCredentialFactory::new();
    }
}
