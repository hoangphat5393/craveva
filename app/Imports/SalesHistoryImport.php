<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesHistoryImport implements WithMultipleSheets, SkipsUnknownSheets
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

    /**
     * @return array<int, SalesHistorySheetImport>
     */
    public function sheets(): array
    {
        $sheets = [];
        for ($i = 0; $i < 60; $i++) {
            $sheets[$i] = new SalesHistorySheetImport($this, $i);
        }

        return $sheets;
    }

    public function appendRows(array $rows, int $sheetIndex): void
    {
        if ($rows === []) {
            return;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $row['__sheet'] = (string) $sheetIndex;
            $this->processedData[] = $row;
        }
    }

    public function appendRow(array $row, int $sheetIndex): void
    {
        if ($row === []) {
            return;
        }

        $row['__sheet'] = (string) $sheetIndex;
        $this->processedData[] = $row;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }

    public function onUnknownSheet($sheetName): void
    {
        // Ignore unknown sheet names/indexes safely.
    }
}
