<?php

require dirname(__DIR__) . '/vendor/autoload.php';

final class FirstNRows implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function __construct(private int $n)
    {
    }

    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        return $row <= $this->n;
    }
}

$path = $argv[1] ?? '';
if ($path === '' || !is_file($path)) {
    fwrite(STDERR, "Usage: php peek_maolin_sheet.php <path-to-xlsx>\n");
    exit(1);
}

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setReadFilter(new FirstNRows(20));
$sheet = $reader->load($path)->getActiveSheet();

for ($i = 1; $i <= 15; $i++) {
    $row = $sheet->rangeToArray('A' . $i . ':AZ' . $i, null, true, false)[0];
    $nonEmpty = array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== '');
    if ($nonEmpty !== []) {
        echo 'R' . $i . ': ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
