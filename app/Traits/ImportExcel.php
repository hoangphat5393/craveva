<?php

namespace App\Traits;

use App\Helper\Files;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use ReflectionClass;

trait ImportExcel
{
    public function importFileProcess($request, $importClass)
    {
        // get class name from $importClass
        $this->importClassName = (new ReflectionClass($importClass))->getShortName();

        $this->file = Files::upload($request->import_file, Files::IMPORT_FOLDER);

        $filePath = public_path(Files::UPLOAD_FOLDER.'/'.Files::IMPORT_FOLDER.'/'.$this->file);
        if (Files::isCsvDisguisedAsXlsx($filePath)) {
            Files::deleteFile($this->file, Files::IMPORT_FOLDER);
            throw ValidationException::withMessages([
                'import_file' => [__('messages.importFileCsvDisguisedAsXlsx')],
            ]);
        }

        $importInstance = new $importClass;
        Excel::import($importInstance, $filePath);
        $excelData = $importInstance->getProcessedData();
        if ($request->has('heading')) {
            array_shift($excelData);
        }
        if ($request->has('skip_footer') && count($excelData) > 1) {
            array_pop($excelData);
        }

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

        $this->hasHeading = $request->has('heading');
        $this->heading = [];
        $this->fileHeading = [];

        $this->columns = $importClass::fields();
        $this->importMatchedColumns = [];
        $this->matchedColumns = [];

        $this->hasSkipFooter = $request->has('skip_footer');
        if ($this->hasHeading) {
            $this->heading = (new HeadingRowImport)->toArray($filePath)[0][0];

            // Excel Format None for get Heading Row Without Format and after change back to config
            HeadingRowFormatter::default('none');
            $this->fileHeading = (new HeadingRowImport)->toArray($filePath)[0][0];
            HeadingRowFormatter::default(config('excel.imports.heading_row.formatter'));

            array_shift($excelData);
            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = [];

            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }

            $this->importMatchedColumns = $importMatchedColumns;
        }

        $this->importSample = array_slice($excelData, 0, 5);
    }

    public function importJobProcess($request, $importClass, $importJobClass)
    {
        // get class name from $importClass
        $importClassName = (new ReflectionClass($importClass))->getShortName();

        // clear previous import
        Artisan::call('queue:clear database --queue='.$importClassName);
        Artisan::call('queue:flush');
        // Get index of an array not null value with key
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        $importInstance = new $importClass;
        Excel::import($importInstance, public_path(Files::UPLOAD_FOLDER.'/'.Files::IMPORT_FOLDER.'/'.$request->file));
        $excelData = $importInstance->getProcessedData();

        if ($request->has_heading) {
            array_shift($excelData);
        }
        if ($request->boolean('has_skip_footer') && count($excelData) > 1) {
            array_pop($excelData);
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
     * Dispatch import as chunk jobs (e.g. 100 rows per job) to reduce queue overhead and speed up import.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $importClass  e.g. ClientImport::class
     * @param  string  $chunkJobClass  Job that accepts (array $rows, array $columns, $company) e.g. ImportClientChunkJob::class
     * @param  int  $chunkSize  Rows per chunk (default 100)
     * @param  array  $options  Optional data passed to each job (e.g. ['default_unit_id' => 1])
     * @return \Illuminate\Bus\PendingBatch
     */
    public function importJobProcessChunked($request, $importClass, $chunkJobClass, int $chunkSize = 100, array $options = [])
    {
        $importClassName = (new ReflectionClass($importClass))->getShortName();

        Artisan::call('queue:clear database --queue='.$importClassName);
        Artisan::call('queue:flush');

        $columns = array_filter($request->columns, fn ($value) => $value !== null);

        $filePath = public_path(Files::UPLOAD_FOLDER.'/'.Files::IMPORT_FOLDER.'/'.$request->file);
        if (Files::isCsvDisguisedAsXlsx($filePath)) {
            throw ValidationException::withMessages([
                'file' => [__('messages.importFileCsvDisguisedAsXlsx')],
            ]);
        }

        $importInstance = new $importClass;
        Excel::import($importInstance, $filePath);
        $excelData = $importInstance->getProcessedData();

        if ($request->has_heading) {
            array_shift($excelData);
        }
        if ($request->boolean('has_skip_footer') && count($excelData) > 1) {
            array_pop($excelData);
        }

        $excelData = self::normalizeExcelRows($excelData);

        $jobs = [];
        $chunkStartIndex = 0;
        foreach (array_chunk($excelData, $chunkSize) as $chunk) {
            $jobs[] = new $chunkJobClass($chunk, $columns, company(), $chunkStartIndex, $options);
            $chunkStartIndex += count($chunk);
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue($importClassName)->name($importClassName.'-chunked')->dispatch();

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
