<?php

namespace Modules\FuncNews\Entities;

use Illuminate\Database\Eloquent\Model;

class FileDependency extends Model
{
    protected $table = 'func_news_dependencies';

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
