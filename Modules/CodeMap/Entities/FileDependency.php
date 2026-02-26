<?php

namespace Modules\CodeMap\Entities;

use Illuminate\Database\Eloquent\Model;

class FileDependency extends Model
{
    protected $table = 'code_map_dependencies';

    protected $fillable = [
        'file_id',
        'depends_on_file_id',
        'relation_type',
    ];

    public function file()
    {
        return $this->belongsTo(FileRecord::class, 'file_id');
    }

    public function dependsOn()
    {
        return $this->belongsTo(FileRecord::class, 'depends_on_file_id');
    }
}
