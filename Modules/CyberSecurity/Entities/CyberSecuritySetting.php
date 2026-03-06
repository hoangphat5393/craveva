<?php

namespace Modules\CyberSecurity\Entities;

use App\Models\BaseModel;

class CyberSecuritySetting extends BaseModel
{
    protected $guarded = ['id'];

    const MODULE_NAME = 'cybersecurity';
}
