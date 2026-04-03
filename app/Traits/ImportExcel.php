<?php

namespace App\Traits;

use App\Helper\Files;
use App\Imports\ChunkReadFilter;
use App\Imports\SalesHistoryImport;
use Illuminate\Bus\PendingBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ReflectionClass;

trait ImportExcel
{
    private function memoryLimitToBytes(?string $value): int
    {
        if (! is_string($value) || trim($value) === '') {
            return 0;
        }

        $value = trim($value);
        if ($value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $value,
        };
    }

    private function boostImportRuntimeLimits(): void
    {
        @set_time_limit(0);

        $current = ini_get('memory_limit');
        if (! is_string($current) || $current === '' || $current === '-1') {
            return;
        }

        $currentBytes = $this->memoryLimitToBytes($current);
        $targetBytes = 1024 * 1024 * 1024; // 1GB

        if ($currentBytes > 0 && $currentBytes < $targetBytes) {
            @ini_set('memory_limit', '1024M');
        }
    }

    public function importFileProcess($request, $importClass)
    {
        $this->boostImportRuntimeLimits();

        // get class name from $importClass
        $this->importClassName = (new ReflectionClass($importClass))->getShortName();

        if (! $request->hasFile('import_file') || ! $request->file('import_file')->isValid()) {
            $msg = __('messages.pleaseSelectFile');
            if ($msg === 'messages.pleaseSelectFile') {
                $msg = 'Please select a valid file to upload.';
            }
            throw ValidationException::withMessages([
                'import_file' => [$msg],
            ]);
        }

        $uploadedSize = (int) ($request->file('import_file')->getSize() ?? 0);
        $memoryBytes = $this->memoryLimitToBytes(ini_get('memory_limit'));
        $requiredBytesForLargeExcel = 1024 * 1024 * 1024; // 1GB
        $largeExcelThreshold = 10 * 1024 * 1024; // 10MB

        if ($uploadedSize >= $largeExcelThreshold && $memoryBytes !== -1 && $memoryBytes > 0 && $memoryBytes < $requiredBytesForLargeExcel) {
            throw ValidationException::withMessages([
                'import_file' => ['Excel file is large. Increase PHP memory_limit to at least 1024M (or upload monthly CSV files).'],
            ]);
        }

        $this->file = Files::upload($request->import_file, Files::IMPORT_FOLDER);

        $filePath = public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->file);
        if (Files::isCsvDisguisedAsXlsx($filePath)) {
            Files::deleteFile($this->file, Files::IMPORT_FOLDER);
            throw ValidationException::withMessages([
                'import_file' => [__('messages.importFileCsvDisguisedAsXlsx')],
            ]);
        }

        $this->hasHeading = $request->boolean('heading');
        $this->heading = [];
        $this->fileHeading = [];

        $this->columns = $importClass::fields();
        if (method_exists($importClass, 'mergeDynamicColumns')) {
            $this->columns = $importClass::mergeDynamicColumns($this->columns);
        }
        $this->importMatchedColumns = [];
        $this->matchedColumns = [];

        $this->hasSkipFooter = $request->boolean('skip_footer');

        // Optimization for large multi-sheet sales history files:
        // mapping step only needs one representative sheet, not full workbook.
        if ($importClass === SalesHistoryImport::class) {
            if ($this->hasHeading) {
                [$this->heading, $this->fileHeading] = $this->readFirstSheetHeadingRows($filePath);

                $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
                $importMatchedColumns = [];
                foreach ($this->matchedColumns as $matchedColumn) {
                    $importMatchedColumns[$matchedColumn] = 1;
                }
                $this->importMatchedColumns = $importMatchedColumns;
            }

            $this->importSample = $this->readFirstSheetSampleRows($filePath, $this->hasHeading, 5);
            if ($this->importSample === []) {
                return 'abort';
            }

            return;
        }

        $importInstance = new $importClass;
        Excel::import($importInstance, $filePath);
        $excelData = $importInstance->getProcessedData();
        $excelData = $this->stripHeadingFooterFromRows(
            $excelData,
            $request->boolean('heading'),
            $request->boolean('skip_footer')
        );

        $isDataNull = true;
        foreach ($excelData as $rowitem) {
            if (array_filter($rowitem)) {
                $isDataNull = false;
                break;
            }
        }
        if ($isDataNull) {
            return 'abort';
        }

        if ($this->hasHeading) {
            $this->heading = (new HeadingRowImport)->toArray($filePath)[0][0];

            // Excel Format None for get Heading Row Without Format and after change back to config
            HeadingRowFormatter::default('none');
            $this->fileHeading = (new HeadingRowImport)->toArray($filePath)[0][0];
            HeadingRowFormatter::default(config('excel.imports.heading_row.formatter'));

            // Tiêu đề đã bỏ bằng stripHeadingFooterFromRows(); HeadingRowImport đọc lại từ file — không shift $excelData thêm lần nữa.
            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = [];

            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }

            $this->importMatchedColumns = $importMatchedColumns;
        }

        $this->importSample = array_slice($excelData, 0, 5);
    }

    /**
     * Csv reader has no listWorksheetNames() (Xlsx/Xls do). Use listWorksheetInfo or single-sheet fallback.
     *
     * @return array<int, string>
     */
    private function listWorksheetNamesForReader($reader, string $filePath): array
    {
        if (method_exists($reader, 'listWorksheetNames')) {
            $names = (array) call_user_func([$reader, 'listWorksheetNames'], $filePath);
            if ($names !== []) {
                return $names;
            }
        }

        if (method_exists($reader, 'listWorksheetInfo')) {
            $info = call_user_func([$reader, 'listWorksheetInfo'], $filePath);
            $names = [];
            foreach (is_array($info) ? $info : [] as $row) {
                if (! empty($row['worksheetName'])) {
                    $names[] = (string) $row['worksheetName'];
                }
            }
            if ($names !== []) {
                return $names;
            }
        }

        return ['Worksheet'];
    }

    /**
     * Read sample rows from first sheet only (for mapping UI performance).
     *
     * @return array<int, array<int, mixed>>
     */
    private function readFirstSheetSampleRows(string $filePath, bool $hasHeading, int $sampleSize = 5): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        $sheetNames = $this->listWorksheetNamesForReader($reader, $filePath);

        if ($sheetNames === []) {
            return [];
        }

        $reader->setLoadSheetsOnly([(string) $sheetNames[0]]);
        $startRow = $hasHeading ? 2 : 1;
        $maxScanRows = 100;
        $readFilter = new ChunkReadFilter;
        $readFilter->setRows($startRow, $maxScanRows);
        $reader->setReadFilter($readFilter);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $endRow = $startRow + $maxScanRows - 1;
        $endColumn = $sheet->getHighestDataColumn();
        $rows = $sheet->rangeToArray("A{$startRow}:{$endColumn}{$endRow}", null, true, true, false);

        $samples = [];
        foreach ($rows as $row) {
            while ($row !== [] && end($row) === null) {
                array_pop($row);
            }

            if (count(array_filter($row, static fn($value) => $value !== null && trim((string) $value) !== '')) === 0) {
                continue;
            }

            $samples[] = $row;
            if (count($samples) >= $sampleSize) {
                break;
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $samples;
    }

    /**
     * Read heading row from first sheet only.
     *
     * @return array{0: array<int, mixed>, 1: array<int, mixed>} [formattedHeading, rawHeading]
     */
    private function readFirstSheetHeadingRows(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        $sheetNames = $this->listWorksheetNamesForReader($reader, $filePath);

        if ($sheetNames === []) {
            return [[], []];
        }

        $reader->setLoadSheetsOnly([(string) $sheetNames[0]]);
        $readFilter = new ChunkReadFilter;
        $readFilter->setRows(1, 1);
        $reader->setReadFilter($readFilter);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $endColumn = $sheet->getHighestDataColumn();
        $rawHeading = $sheet->rangeToArray("A1:{$endColumn}1", null, true, true, false)[0] ?? [];

        while ($rawHeading !== [] && end($rawHeading) === null) {
            array_pop($rawHeading);
        }

        $spreadsheet->disconnectWorksheets();

        $formattedHeading = HeadingRowFormatter::format($rawHeading);

        return [$formattedHeading, $rawHeading];
    }

    public function importJobProcess($request, $importClass, $importJobClass)
    {
        $this->boostImportRuntimeLimits();

        // get class name from $importClass
        $importClassName = (new ReflectionClass($importClass))->getShortName();

        // Clear only this import queue (do not queue:flush — that wipes all failed_jobs globally).
        Artisan::call('queue:clear database --queue=' . $importClassName);
        // Get index of an array not null value with key
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        $importInstance = new $importClass;
        Excel::import($importInstance, public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $request->file));
        $excelData = $importInstance->getProcessedData();
        $excelData = $this->stripHeadingFooterFromRows(
            $excelData,
            $request->boolean('has_heading'),
            $request->boolean('has_skip_footer')
        );

        $jobs = [];

        Session::put('leads_count', count($excelData));

        foreach ($excelData as $row) {

            $jobs[] = (new $importJobClass($row, $columns, company()));
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue($importClassName)->name($importClassName)->dispatch();

        Files::deleteFile($request->file, Files::IMPORT_FOLDER);

        return $batch;
    }

    /**
     * Strip header and/or footer rows from imported sheet data. Single place for shift/pop so it cannot run twice by mistake.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array<int, mixed>>
     */
    private function stripHeadingFooterFromRows(array $rows, bool $stripHeading, bool $stripFooter): array
    {
        if ($stripHeading && $rows !== []) {
            array_shift($rows);
        }
        if ($stripFooter && count($rows) > 1) {
            array_pop($rows);
        }

        return $rows;
    }

    /**
     * Dispatch import as chunk jobs (e.g. 100 rows per job) to reduce queue overhead and speed up import.
     *
     * @param  Request  $request
     * @param  string  $importClass  e.g. ClientImport::class
     * @param  string  $chunkJobClass  Job that accepts (array $rows, array $columns, $company) e.g. ImportClientChunkJob::class
     * @param  int  $chunkSize  Rows per chunk (default 100)
     * @param  array  $options  Optional data passed to each job (e.g. ['default_unit_id' => 1])
     * @return PendingBatch
     */
    public function importJobProcessChunked($request, $importClass, $chunkJobClass, int $chunkSize = 100, array $options = [])
    {
        $this->boostImportRuntimeLimits();

        $importClassName = (new ReflectionClass($importClass))->getShortName();

        // Clear only this import queue (do not queue:flush — that wipes all failed_jobs globally).
        Artisan::call('queue:clear database --queue=' . $importClassName);

        $columns = array_filter($request->columns, fn($value) => $value !== null);

        $filePath = public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $request->file);
        if (Files::isCsvDisguisedAsXlsx($filePath)) {
            throw ValidationException::withMessages([
                'file' => [__('messages.importFileCsvDisguisedAsXlsx')],
            ]);
        }

        $importInstance = new $importClass;
        Excel::import($importInstance, $filePath);
        $excelData = $importInstance->getProcessedData();
        $excelData = $this->stripHeadingFooterFromRows(
            $excelData,
            $request->boolean('has_heading'),
            $request->boolean('has_skip_footer')
        );

        $excelData = self::normalizeExcelRows($excelData);

        $jobs = [];
        $chunkStartIndex = 0;
        foreach (array_chunk($excelData, $chunkSize) as $chunk) {
            $jobs[] = new $chunkJobClass($chunk, $columns, company(), $chunkStartIndex, $options);
            $chunkStartIndex += count($chunk);
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue($importClassName)->name($importClassName . '-chunked');
        if ($request->filled('original_filename')) {
            $batch = $batch->withOption('original_filename', $request->input('original_filename'));
        }
        $batch = $batch->dispatch();

        Files::deleteFile($request->file, Files::IMPORT_FOLDER);

        Session::put('leads_count', count($excelData));

        return $batch;
    }

    /**
     * Convert all cell values to scalars to avoid PhpSpreadsheet objects (Cell, RichText)
     * causing "separation symbol" or serialization issues when queuing jobs.
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @return array<int, array<int, string|int|float|null>>
     */
    protected static function normalizeExcelRows(array $rows): array
    {
        return array_map(function (array $row) {
            $result = [];
            foreach ($row as $key => $value) {
                if ($value === null || is_scalar($value)) {
                    $result[$key] = $value;
                } else {
                    $result[$key] = self::cellValueToScalar($value);
                }
            }

            return $result;
        }, $rows);
    }

    /**
     * Safely convert Cell/RichText to scalar. getFormattedValue() can throw
     * "The separation symbol could not be found" during number/date formatting.
     */
    private static function cellValueToScalar($value)
    {
        try {
            if (is_object($value) && method_exists($value, 'getFormattedValue')) {
                return $value->getFormattedValue();
            }
            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }

            return $value === null ? null : (string) $value;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
