<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class AttendanceImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'email', 'name' => __('app.email'), 'required' => 'Yes'],
            ['id' => 'clock_in_time', 'name' => __('modules.attendance.clock_in'), 'required' => 'Yes'],
            ['id' => 'clock_out_time', 'name' => __('modules.attendance.clock_out'), 'required' => 'No'],
            ['id' => 'clock_in_ip', 'name' => __('modules.attendance.clock_in_ip'), 'required' => 'No'],
            ['id' => 'clock_out_ip', 'name' => __('modules.attendance.clock_out_ip'), 'required' => 'No'],
            ['id' => 'working_from', 'name' => __('modules.attendance.working_from'), 'required' => 'No'],
            ['id' => 'late', 'name' => __('modules.attendance.late'), 'required' => 'No'],
            ['id' => 'half_day', 'name' => __('modules.attendance.halfDay'), 'required' => 'No'],
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
