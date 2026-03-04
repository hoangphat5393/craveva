<?php

namespace App\Observers;

use App\Models\UserActivity;

class UserActivityObserver
{

    public function creating(UserActivity $model)
    {
        if ($model->user) {
            $model->company_id = $model->user->company_id;
        }
    }

}
