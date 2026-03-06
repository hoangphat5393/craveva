<?php

namespace Modules\Webhooks\Observers;

use App\Models\EmployeeDetails;
use Modules\Webhooks\Jobs\SendWebhook;

class EmployeeDetailsObserver
{
    public function created(EmployeeDetails $employeeDetails)
    {
        $data = $employeeDetails->toArray();
        $userModel = $employeeDetails->user()->withoutGlobalScopes()->first();

        if ($userModel) {
            $user = $userModel->toArray();
            $data = array_merge($data, $user);
        }

        SendWebhook::dispatch($data, 'Employee', $employeeDetails->company_id)
            ->delay(5)
            ->onQueue('default');
    }
}
