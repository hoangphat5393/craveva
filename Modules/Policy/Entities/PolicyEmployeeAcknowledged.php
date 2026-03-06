<?php

namespace Modules\Policy\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyEmployeeAcknowledged extends BaseModel
{
    protected $dates = ['acknowledged_on'];

    protected $table = 'policy_employee_acknowledged';

    protected $appends = ['employee_signature'];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withoutGlobalScope(ActiveScope::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'policy_id', 'id');
    }

    public function getEmployeeSignatureAttribute()
    {
        return asset_url_local_s3('policy/sign/'.$this->signature_file);
    }
}
