<?php

namespace Modules\CodeMap\Entities;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    protected $table = 'code_map_files';

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
