<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class SalesOrderSheetImport implements ToArray
{
    public function __construct(private SalesOrderImport $parent) {}

    public function array(array $array): array
    {
        $this->parent->appendRows($array);

        return $array;
    }
}
