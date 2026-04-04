<?php

namespace App\Traits;

use App\Helper\Files;
use App\Imports\ChunkReadFilter;
use App\Imports\ClientImport;
use App\Imports\ProductImport;
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
use Modules\Purchase\Imports\InventoryImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ReflectionClass;

trait ImportExcel
{
    /**
     * First rows scanned to infer map column width (no full-file metadata scan).
     */
    private const IMPORT_MAP_COLUMN_SCAN_ROWS = 200;

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

    /**
     * First-sheet light map (no full Excel::import on upload step).
     *
     * @param  class-string  $importClass
     */
    private function importClassUsesLightMap(string $importClass): bool
    {
        return in_array($importClass, [
            SalesHistoryImport::class,
            ClientImport::class,
            ProductImport::class,
            InventoryImport::class,
        ], true);
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
        // Client / Product / Inventory: merge dynamic columns inside the light-read branch (full targets before heading match).
        if (method_exists($importClass, 'mergeDynamicColumns') && ! in_array($importClass, [ClientImport::class, ProductImport::class, InventoryImport::class], true)) {
            $this->columns = $importClass::mergeDynamicColumns($this->columns);
        }
        $this->importMatchedColumns = [];
        $this->matchedColumns = [];

        $this->hasSkipFooter = $request->boolean('skip_footer');

        // Light mapping read (first sheet only): heading + sample (≤100 rows scanned for 5 samples), width from first 200 rows.
        // Sales History, Client, Product, Purchase Inventory — avoids full Excel::import on "Upload next".
        if ($this->importClassUsesLightMap($importClass)) {
            if ($importClass === ClientImport::class) {
                $this->columns = ClientImport::mergeDynamicColumns(ClientImport::fields());
            }
            if ($importClass === InventoryImport::class) {
                $this->columns = InventoryImport::mergeDynamicColumns(InventoryImport::fields());
            }
            if ($importClass === ProductImport::class) {
                $this->columns = ProductImport::mergeDynamicColumns(ProductImport::fields());
            }

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

            // Pad heading + samples to inferred width (scan first IMPORT_MAP_COLUMN_SCAN_ROWS for non-empty cells).
            $this->normalizeLightImportMapColumnWidth($filePath);

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

    /**
     * Max column count from first N rows of sheet 1: rightmost index with non-empty trimmed cell value, max over rows.
     * Bounded I/O (CSV/XLSX); avoids listWorksheetInfo full-file CSV scan for map UI width only.
     */
    private function getMaxColumnCountFromFirstSheetRows(string $filePath, int $maxRows): int
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        $sheetNames = $this->listWorksheetNamesForReader($reader, $filePath);
        if ($sheetNames === []) {
            return 0;
        }

        $reader->setLoadSheetsOnly([(string) $sheetNames[0]]);
        $readFilter = new ChunkReadFilter;
        $readFilter->setRows(1, $maxRows);
        $reader->setReadFilter($readFilter);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = (int) $sheet->getHighestDataRow();
        if ($highestRow < 1) {
            $spreadsheet->disconnectWorksheets();

            return 0;
        }

        $endRow = min($maxRows, $highestRow);
        $endColumn = $sheet->getHighestDataColumn();
        if ($endColumn === null || $endColumn === '') {
            $spreadsheet->disconnectWorksheets();

            return 0;
        }

        $rows = $sheet->rangeToArray("A1:{$endColumn}{$endRow}", null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        $maxCol = 0;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $lastDataIndex = -1;
            foreach ($row as $idx => $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $lastDataIndex = max($lastDataIndex, (int) $idx);
                }
            }
            if ($lastDataIndex >= 0) {
                $maxCol = max($maxCol, $lastDataIndex + 1);
            }
        }

        return $maxCol;
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    private function padImportRowToColumnCount(array $row, int $columnCount): array
    {
        if (count($row) >= $columnCount) {
            return array_slice($row, 0, $columnCount);
        }

        return array_pad($row, $columnCount, null);
    }

    /**
     * Ensure heading + sample rows span sheet width for the map form (pad to max of: data in first N rows, heading, samples).
     */
    private function normalizeLightImportMapColumnWidth(string $filePath): void
    {
        $fromFirstRows = $this->getMaxColumnCountFromFirstSheetRows($filePath, self::IMPORT_MAP_COLUMN_SCAN_ROWS);

        $widthCandidates = [
            $fromFirstRows,
            count($this->heading),
            count($this->fileHeading),
        ];
        foreach ($this->importSample as $sampleRow) {
            $widthCandidates[] = count($sampleRow);
        }

        $columnCount = max($widthCandidates);
        if ($columnCount < 1) {
            return;
        }

        if ($this->hasHeading) {
            $this->fileHeading = $this->padImportRowToColumnCount($this->fileHeading, $columnCount);
            $this->heading = HeadingRowFormatter::format($this->fileHeading);

            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = [];
            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }
            $this->importMatchedColumns = $importMatchedColumns;
        }

        $this->importSample = array_map(
            fn(array $row) => $this->padImportRowToColumnCount($row, $columnCount),
            $this->importSample
        );
    }

    /**
     * Load first-sheet data rows for client import using row range (no array_shift on a full-sheet array).
     * Returns null to signal caller should fall back to Maatwebsite Excel::import + stripHeadingFooterFromRows.
     *
     * @return array<int, array<int, mixed>>|null
     */
    /**
     * First sheet only: read data rows by range (skip header row via startRow), no array_shift on full sheet.
     * Used after map for Client, Product, Purchase Inventory.
     */
    private function loadFirstSheetDataRowsByRowRange(string $filePath, bool $hasHeading, bool $skipFooter): ?array
    {
        $infoReader = IOFactory::createReaderForFile($filePath);
        $infoReader->setReadDataOnly(true);
        if (method_exists($infoReader, 'setReadEmptyCells')) {
            $infoReader->setReadEmptyCells(false);
        }

        if (! method_exists($infoReader, 'listWorksheetInfo')) {
            return null;
        }

        $infos = (array) call_user_func([$infoReader, 'listWorksheetInfo'], $filePath);
        if ($infos === []) {
            return null;
        }

        $totalRows = (int) ($infos[0]['totalRows'] ?? 0);
        $sheetName = (string) ($infos[0]['worksheetName'] ?? 'Worksheet');

        if ($totalRows < 1) {
            return null;
        }

        $startRow = $hasHeading ? 2 : 1;
        if ($startRow > $totalRows) {
            return [];
        }

        $endRow = $skipFooter ? max($startRow, $totalRows - 1) : $totalRows;
        if ($startRow > $endRow) {
            return [];
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }

        $reader->setLoadSheetsOnly([$sheetName]);
        $rowCount = $endRow - $startRow + 1;
        $readFilter = new ChunkReadFilter;
        $readFilter->setRows($startRow, $rowCount);
        $reader->setReadFilter($readFilter);

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $endColumn = $sheet->getHighestDataColumn();
        $rows = $sheet->rangeToArray("A{$startRow}:{$endColumn}{$endRow}", null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        $out = [];
        foreach ($rows as $row) {
            while ($row !== [] && end($row) === null) {
                array_pop($row);
            }
            $out[] = $row;
        }

        return $out;
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

        $filePath = public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $request->file);
        $hasHeading = $request->boolean('has_heading');
        $hasSkipFooter = $request->boolean('has_skip_footer');

        $excelData = null;
        if (in_array($importClass, [ClientImport::class, ProductImport::class, InventoryImport::class], true)) {
            $excelData = $this->loadFirstSheetDataRowsByRowRange($filePath, $hasHeading, $hasSkipFooter);
        }

        if ($excelData === null) {
            $importInstance = new $importClass;
            Excel::import($importInstance, $filePath);
            $excelData = $importInstance->getProcessedData();
            $excelData = $this->stripHeadingFooterFromRows($excelData, $hasHeading, $hasSkipFooter);
        }

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

        $hasHeading = $request->boolean('has_heading');
        $hasSkipFooter = $request->boolean('has_skip_footer');

        if (in_array($importClass, [ClientImport::class, ProductImport::class, InventoryImport::class], true)) {
            $excelData = $this->loadFirstSheetDataRowsByRowRange($filePath, $hasHeading, $hasSkipFooter);
        }

        if (! isset($excelData) || $excelData === null) {
            $importInstance = new $importClass;
            Excel::import($importInstance, $filePath);
            $excelData = $importInstance->getProcessedData();
            $excelData = $this->stripHeadingFooterFromRows($excelData, $hasHeading, $hasSkipFooter);
        }

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
