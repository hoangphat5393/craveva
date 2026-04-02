<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class SalesOrderSheetImport implements OnEachRow, WithChunkReading
{
    public function __construct(private SalesOrderImport $parent) {}

    public function onRow(Row $row): void
    {
        $this->parent->appendRows([$row->toArray()]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
