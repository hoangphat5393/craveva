<?php

namespace App\Jobs;

use App\Helper\Files;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesHistoryLine;
use App\Traits\StoresImportBatchMetrics;
use Carbon\Carbon;
use Exception;
use App\Imports\ChunkReadFilter;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportSalesHistoryStreamJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StoresImportBatchMetrics;

    public int $timeout = 180;

    public int $tries = 2;

    public function __construct(
        private string $uploadedFileName,
        private int $companyId,
        private int $salesHistoryId,
        private array $columns,
        private string $sheetName,
        private int $sheetIndex,
        private int $startRow,
        private int $endRow
    ) {}

    public function handle(): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            throw new Exception('Company context is required for sales history import.');
        }

        company($company);
        @set_time_limit(0);

        $fullPath = public_path(Files::UPLOAD_FOLDER . '/' . Files::IMPORT_FOLDER . '/' . $this->uploadedFileName);
        if (! is_file($fullPath)) {
            throw new Exception('Import file not found.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $missingRequiredCount = 0;
        $invalidStatusCount = 0; // kept for shared UI metrics schema
        $failures = [];

        if ($this->startRow > $this->endRow) {
            return;
        }

        $chunkSize = $this->endRow - $this->startRow + 1;
        $readFilter = new ChunkReadFilter;
        $readFilter->setRows($this->startRow, $chunkSize);

        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }
        $reader->setReadFilter($readFilter);
        $reader->setLoadSheetsOnly([$this->sheetName]);

        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getSheetByName($this->sheetName) ?? $spreadsheet->getActiveSheet();
        $endColumn = $sheet->getHighestDataColumn();
        $rows = $sheet->rangeToArray("A{$this->startRow}:{$endColumn}{$this->endRow}", null, true, true, false);

        foreach ($rows as $offset => $row) {
            try {
                $result = DB::transaction(function () use ($row, $company) {
                    return $this->processRow($this->normalizeRow($row), $company);
                });

                if ($result === 'created') {
                    $createdCount++;
                } elseif ($result === 'updated') {
                    $updatedCount++;
                } elseif ($result === 'missing_required') {
                    $skippedCount++;
                    $missingRequiredCount++;
                } else {
                    $skippedCount++;
                }
            } catch (Exception $e) {
                $fileRow = $this->startRow + $offset;
                $failures[] = 'Sheet ' . $this->sheetIndex . ' Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($rows, $sheet, $spreadsheet, $reader);

        if ($this->batchId) {
            $this->mergeImportBatchMetrics($this->batchId, [
                'created' => $createdCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'skipped_missing_required' => $missingRequiredCount,
                'invalid_status' => $invalidStatusCount,
            ]);
        }

        if ($failures !== []) {
            $message = implode("\n", array_slice($failures, 0, 50));
            if (count($failures) > 50) {
                $message .= "\n… and " . (count($failures) - 50) . ' more';
            }
            throw new Exception($message);
        }
    }

    /**
     * @return string created|updated|skipped|missing_required
     */
    private function processRow(array $row, Company $company): string
    {
        if ($this->isEmptyRow($row)) {
            return 'skipped';
        }

        $customerCode = trim((string) $this->getValue($row, 'customer_number'));
        $sku = trim((string) $this->getValue($row, 'product_part_number'));
        $shipmentDateRaw = $this->getValue($row, 'shipment_return_date');
        $qtyRaw = $this->getValue($row, 'net_sales_volume');
        $amountRaw = $this->getValue($row, 'net_sales_amount');

        if ($customerCode === '' || $sku === '' || $shipmentDateRaw === null || trim((string) $shipmentDateRaw) === '' || $qtyRaw === null || trim((string) $qtyRaw) === '') {
            return 'missing_required';
        }

        $details = DB::table('client_details')
            ->where('company_id', $company->id)
            ->where('client_code', $customerCode)
            ->first(['user_id', 'id']);

        if (! $details?->user_id) {
            throw new Exception("Client not found by code: {$customerCode}");
        }

        $product = Product::query()
            ->where('company_id', $company->id)
            ->where('sku', $sku)
            ->first(['id']);

        if (! $product) {
            throw new Exception("Product not found by SKU: {$sku}");
        }

        $shipmentDate = $this->parseDateToYmd($shipmentDateRaw);
        $qty = $this->parseNumber($qtyRaw);
        $amount = $amountRaw === null || trim((string) $amountRaw) === '' ? null : $this->parseNumber($amountRaw);
        $isReturn = $qty < 0 || (($amount ?? 0) < 0);

        $qtyAbs = abs($qty);
        $amountAbs = $amount !== null ? abs($amount) : null;
        $unitPrice = $qtyAbs > 0 ? (($amountAbs ?? 0) / $qtyAbs) : ($amountAbs ?? 0);

        $sourceHash = sha1(implode('|', [
            $company->id,
            $shipmentDate,
            $customerCode,
            $sku,
            (string) $qty,
            (string) ($amount ?? ''),
        ]));

        $existing = SalesHistoryLine::query()
            ->where('company_id', $company->id)
            ->where('source_row_hash', $sourceHash)
            ->exists();

        if ($existing) {
            return 'updated';
        }

        $line = new SalesHistoryLine;
        $line->company_id = $company->id;
        $line->sales_history_id = $this->salesHistoryId;
        $line->shipment_date = $shipmentDate;
        $line->client_id = $details->user_id;
        $line->client_details_id = $details->id;
        $line->product_id = $product->id;
        $line->quantity = $qty;
        $line->quantity_abs = $qtyAbs;
        $line->amount = $amountAbs !== null ? round($amountAbs, 6) : null;
        $line->unit_price = round($unitPrice, 6);
        $line->is_return = $isReturn;
        $line->currency_id = $company->currency_id;
        $line->source_row_hash = $sourceHash;
        $line->net_sales_volume_raw = $qty;
        $line->net_sales_amount_raw = $amount;
        $line->save();

        return 'created';
    }

    private function parseDateToYmd($value): string
    {
        if ($value === null || trim((string) $value) === '') {
            throw new Exception('Shipment/Return Date is empty');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        $str = trim((string) $value);
        $str = str_replace(['.', '-'], '/', $str);

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new Exception('Invalid Shipment/Return Date: ' . $value);
        }
    }

    private function parseNumber($value): float
    {
        if ($value === null) {
            return 0.0;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return 0.0;
        }
        $s = str_replace(["\xC2\xA0", ' '], '', $s);
        $s = str_replace(',', '', $s);

        if (! is_numeric($s)) {
            throw new Exception('Invalid numeric value: ' . $value);
        }

        return (float) $s;
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $k => $v) {
            $normalized[$k] = is_scalar($v) || $v === null ? $v : (string) $v;
        }

        return $normalized;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function getValue(array $row, string $fieldId)
    {
        $keys = array_keys($this->columns, $fieldId, true);

        return $keys !== [] ? ($row[$keys[0]] ?? null) : null;
    }
}
