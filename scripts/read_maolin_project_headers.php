<?php

/**
 * One-off: Read row1 headers from PROJECT MAOLIN/*.xlsx (read filter: first 5 rows only).
 * Run: php scripts/read_maolin_project_headers.php
 */
$base = dirname(__DIR__) . '/PROJECT MAOLIN';
require dirname(__DIR__) . '/vendor/autoload.php';

final class FirstRowsFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function __construct(private int $maxRow = 5)
    {
    }

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
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setReadFilter(new FirstRowsFilter(5));
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highest = $sheet->getHighestColumn();
        $row1 = $sheet->rangeToArray('A1:' . $highest . '1', null, true, true, true)[1];
        echo "=== $bn ===\n";
        foreach ($row1 as $col => $val) {
            $v = trim((string) ($val ?? ''));
            if ($v !== '') {
                echo "  $col: $v\n";
            }
        }
        echo "  (first sheet; row1 only — full row count not loaded)\n\n";
    } catch (Throwable $e) {
        echo "ERROR $bn: " . $e->getMessage() . "\n\n";
    }
}
