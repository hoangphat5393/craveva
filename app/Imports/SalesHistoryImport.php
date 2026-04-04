<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Single-sheet import (first sheet only), aligned with Client/Product for light map + chunked queue.
 * Extra sheets in the workbook are ignored.
 */
class SalesHistoryImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'shipment_return_date', 'name' => 'Shipment/Return Date', 'required' => 'Yes'],
            ['id' => 'customer_number', 'name' => 'Customer Number', 'required' => 'Yes'],
            ['id' => 'product_part_number', 'name' => 'Product Part Number (SKU)', 'required' => 'Yes'],
            ['id' => 'net_sales_volume', 'name' => 'Net Sales Volume', 'required' => 'Yes'],
            ['id' => 'net_sales_amount', 'name' => 'Net Sales Amount', 'required' => 'No'],
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
