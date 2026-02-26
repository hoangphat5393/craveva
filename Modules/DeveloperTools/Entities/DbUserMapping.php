<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DeveloperTools\Database\factories\DbUserMappingFactory;

class DbUserMapping extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'db_user_mapping';
    protected $fillable = ['db_username', 'company_id'];
    
    protected static function newFactory(): DbUserMappingFactory
    {
        //return DbUserMappingFactory::new();
    }
}
