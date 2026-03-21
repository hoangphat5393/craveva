<?php

/**
 * One-off: Read headers from PROJECT MAOLIN New Excel files.
 * Run: php scripts/read_maolin_headers.php
 */
$base = dirname(__DIR__) . '/PROJECT MAOLIN New';
$files = [
    'Craveva customer.xlsx',
    'Craveva product.xlsx',
    'Craveva fullinventory.xlsx',
    'Quote, unit price, inventory.xlsx',
    'Last year net sales.xlsx',
];

require dirname(__DIR__) . '/vendor/autoload.php';

foreach ($files as $name) {
    $path = $base . '/' . $name;
    if (!is_file($path)) {
        echo "SKIP (not found): $name\n";
        continue;
    }
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highest = $sheet->getHighestColumn();
        $row1 = $sheet->rangeToArray('A1:' . $highest . '1', null, true, true, true)[1];
        echo "=== $name ===\n";
        foreach ($row1 as $col => $val) {
            $v = trim((string) ($val ?? ''));
            echo "  $col: " . ($v !== '' ? $v : '(empty)') . "\n";
        }
        echo "\n";
    } catch (Throwable $e) {
        echo "ERROR $name: " . $e->getMessage() . "\n\n";
    }
}
