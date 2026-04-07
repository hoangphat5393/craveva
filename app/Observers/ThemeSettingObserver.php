<?php

namespace App\Observers;

use App\Models\ThemeSetting;

class ThemeSettingObserver
{
    public function creating(ThemeSetting $model): void
    {
        if (! company()) {
            return;
        }

        // Global superadmin theme row is stored with company_id NULL; do not override.
        if ($model->panel === 'superadmin' && $model->company_id === null) {
            return;
        }

        $model->company_id = company()->id;
    }
}
