<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Model;

class DbUserMapping extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'db_user_mapping';

    protected $fillable = ['db_username', 'company_id'];
}
