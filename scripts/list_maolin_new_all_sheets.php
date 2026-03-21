<?php

/**
 * List every worksheet name + row 1 text in PROJECT MAOLIN New/*.xlsx
 * Uses read filter: first 3 rows only per sheet to limit memory.
 */
$base = dirname(__DIR__) . '/PROJECT MAOLIN New';
require dirname(__DIR__) . '/vendor/autoload.php';

final class FirstRowsFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function __construct(private int $maxRow = 3) {}

    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        return $row <= $this->maxRow;
    }
}

$files = glob($base . '/*.xlsx') ?: [];
sort($files);

foreach ($files as $path) {
    $bn = basename($path);
    if (str_starts_with($bn, '~$')) {
        continue;
    }
    echo "=== $bn ===\n";
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $sheetList = $reader->listWorksheetNames($path);
        echo '  sheet count: ' . count($sheetList) . "\n";

        foreach ($sheetList as $idx => $sheetName) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $reader->setLoadSheetsOnly([$sheetName]);
            $reader->setReadFilter(new FirstRowsFilter(3));
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if ($sheet === null) {
                echo "  - [$idx] (missing)\n";
                continue;
            }
            $highest = $sheet->getHighestColumn();
            $row1 = $sheet->rangeToArray('A1:' . $highest . '1', null, true, true, true)[1];
            $parts = [];
            foreach ($row1 as $col => $val) {
                $v = trim((string) ($val ?? ''));
                if ($v !== '') {
                    $parts[] = "$col:$v";
                }
            }
            $r1 = $parts !== [] ? implode(' | ', $parts) : '(row1 empty or merged)';
            echo '  - [' . $idx . '] ' . $sheetName . "\n";
            echo '      row1: ' . $r1 . "\n";
        }
    } catch (Throwable $e) {
        echo '  ERROR: ' . $e->getMessage() . "\n";
    }
    echo "\n";
}
