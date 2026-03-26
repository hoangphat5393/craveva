<?php

namespace Modules\Warehouse\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class WarehouseImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'warehouse_code', 'name' => __('warehouse::app.code'), 'required' => 'No'],
            ['id' => 'warehouse_name', 'name' => __('warehouse::app.name'), 'required' => 'Yes'],
            ['id' => 'status', 'name' => __('app.status') . ' (active/inactive)', 'required' => 'No'],
            ['id' => 'address', 'name' => __('warehouse::app.address'), 'required' => 'No'],
            ['id' => 'description', 'name' => __('warehouse::app.description'), 'required' => 'No'],
            ['id' => 'is_default', 'name' => __('warehouse::app.isDefault') . ' (1/0, yes/no)', 'required' => 'No'],
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
