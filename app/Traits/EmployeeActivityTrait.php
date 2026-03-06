<?php

namespace App\Traits;

use App\Models\EmployeeActivity;

trait EmployeeActivityTrait
{
    public static function createEmployeeActivity($empId, string $employeeActivity, $id = null, $type = null): void
    {

        $fieldName = $type.'_id';

        $employeeActivityData = new EmployeeActivity;
        $employeeActivityData->employee_activity = $employeeActivity;
        $employeeActivityData->emp_id = $empId;

        if ($type) {
            $employeeActivityData->{$fieldName} = $id;
        }

        $employeeActivityData->save();
    }
}
