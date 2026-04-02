<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class SalesHistorySheetImport implements OnEachRow, WithChunkReading
{
    public function __construct(
        private SalesHistoryImport $parent,
        private int $sheetIndex
    ) {}

    public function onRow(Row $row): void
    {
        $this->parent->appendRows([$row->toArray()], $this->sheetIndex);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
