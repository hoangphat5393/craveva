<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;
use App\Models\LanguageSetting;

class FrontMenu extends BaseModel
{
    protected $guarded = ['id'];

    protected $table = 'front_menu_buttons';

    public function language()
    {
        return $this->belongsTo(LanguageSetting::class, 'language_setting_id');
    }
}
