<?php

namespace Modules\DeveloperTools\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileRecord extends Model
{
    use HasFactory;

    protected $table = 'developer_tools_files';

    protected $fillable = [
        'name',
        'path',
        'language',
        'framework',
        'role',
        'module',
        'version',
        'last_modified_at',
        'hash',
        'extra',
    ];

    protected $casts = [
        'last_modified_at' => 'datetime',
        'extra' => 'array',
    ];

    public function dependencies()
    {
        return $this->hasMany(FileDependency::class, 'file_id');
    }
}
