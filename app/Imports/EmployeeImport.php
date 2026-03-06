<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'name', 'name' => __('modules.employees.employeeName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('modules.employees.employeeEmail'), 'required' => 'Yes'],
            ['id' => 'mobile', 'name' => __('app.mobile'), 'required' => 'No'],
            ['id' => 'gender', 'name' => __('modules.employees.gender'), 'required' => 'No'],
            ['id' => 'employee_id', 'name' => __('modules.employees.employeeId'), 'required' => 'Yes'],
            ['id' => 'joining_date', 'name' => __('modules.employees.joiningDate'), 'required' => 'Yes'],
            ['id' => 'address', 'name' => __('app.address'), 'required' => 'No'],
            ['id' => 'hourly_rate', 'name' => __('modules.employees.hourlyRate'), 'required' => 'No'],
        ];
    }

    public function array(array $array): array
    {
        $this->processedData = $array;

        return $array;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }
}
