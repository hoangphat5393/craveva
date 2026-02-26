<?php

namespace Modules\FuncNews\Entities;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    protected $table = 'func_news_files';

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
