<?php

namespace Modules\Recruit\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class JobApplicationImport implements ToArray
{
    public static function fields(): array
    {
        return [
            ['id' => 'full_name', 'name' => __('recruit::modules.interviewSchedule.candidateName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('recruit::modules.form.email'), 'required' => 'No'],
            ['id' => 'phone', 'name' => __('recruit::modules.form.phone'), 'required' => 'No'],
            ['id' => 'gender', 'name' => __('recruit::modules.form.gender'), 'required' => 'No'],
            ['id' => 'location', 'name' => __('recruit::modules.jobApplication.location'), 'required' => 'No'],
            ['id' => 'application_sources', 'name' => __('recruit::modules.form.application_source'), 'required' => 'No'],
            ['id' => 'current_ctc', 'name' => __('recruit::modules.form.current_ctc'), 'required' => 'No'],
            ['id' => 'expected_ctc', 'name' => __('recruit::modules.form.expected_ctc'), 'required' => 'No'],
            ['id' => 'total_experience', 'name' => __('recruit::modules.form.total_experience'), 'required' => 'No'],
        ];
    }

    public function array(array $array): array
    {
        return $array;
    }
}
