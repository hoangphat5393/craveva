<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class DealImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'email', 'name' => __('modules.deal.leadContactEmail'), 'required' => 'Yes'],
            ['id' => 'name', 'name' => __('modules.deal.dealName'), 'required' => 'Yes'],
            ['id' => 'pipeline', 'name' => __('modules.deal.pipeline'), 'required' => 'Yes'],
            ['id' => 'stages', 'name' => __('modules.deal.stages'), 'required' => 'Yes'],
            ['id' => 'value', 'name' => __('modules.deal.dealValue'), 'required' => 'Yes'],
            ['id' => 'close_date', 'name' => __('modules.deal.closeDate'), 'required' => 'Yes'],
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
