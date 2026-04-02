<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SalesHistoryLine;
use App\Traits\StoresImportBatchMetrics;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportSalesHistoryChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StoresImportBatchMetrics;

    private array $rows;

    private array $columns;

    private $company;

    private int $chunkStartIndex;

    /** @var array<string, mixed> */
    private array $options;

    public function __construct(array $rows, array $columns, $company = null, int $chunkStartIndex = 0, array $options = [])
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
        $this->chunkStartIndex = $chunkStartIndex;
        $this->options = $options;
    }

    public function handle(): void
    {
        if (! $this->company?->id) {
            $this->fail(__('messages.invalidData') . ': Company context is required for sales history import.');

            return;
        }

        company($this->company);

        $failures = [];
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $missingRequiredCount = 0;

        foreach ($this->rows as $index => $row) {
            try {
                $result = DB::transaction(function () use ($row) {
                    return $this->processRow($this->normalizeRow($row));
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
                $fileRow = $this->chunkStartIndex + $index + 2;
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $this->mergeImportBatchMetrics($this->batchId, [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'skipped_missing_required' => $missingRequiredCount,
            'invalid_status' => 0,
        ]);

        if ($failures !== []) {
            $message = implode("\n", array_slice($failures, 0, 50));
            if (count($failures) > 50) {
                $message .= "\n… and " . (count($failures) - 50) . ' more';
            }
            $this->fail($message);
        }
    }

    /**
     * @return string created|updated|skipped|missing_required
     */
    private function processRow(array $row): string
    {
        if ($this->isEmptyRow($row)) {
            return 'skipped';
        }

        unset($row['__sheet']);

        $customerCode = trim((string) $this->getValue($row, 'customer_number'));
        $sku = trim((string) $this->getValue($row, 'product_part_number'));
        $shipmentDateRaw = $this->getValue($row, 'shipment_return_date');
        $qtyRaw = $this->getValue($row, 'net_sales_volume');
        $amountRaw = $this->getValue($row, 'net_sales_amount');

        if ($customerCode === '' || $sku === '' || $shipmentDateRaw === null || trim((string) $shipmentDateRaw) === '' || $qtyRaw === null || trim((string) $qtyRaw) === '') {
            return 'missing_required';
        }

        $details = DB::table('client_details')
            ->where('company_id', $this->company->id)
            ->where('client_code', $customerCode)
            ->first(['user_id', 'id']);

        if (! $details?->user_id) {
            throw new Exception("Client not found by code: {$customerCode}");
        }

        $product = Product::query()
            ->where('company_id', $this->company->id)
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

        $hashBase = implode('|', [
            $this->company->id,
            $shipmentDate,
            $customerCode,
            $sku,
            (string) $qty,
            (string) ($amount ?? ''),
        ]);
        $sourceHash = sha1($hashBase);

        $existing = SalesHistoryLine::query()
            ->where('company_id', $this->company->id)
            ->where('source_row_hash', $sourceHash)
            ->first(['id']);

        if ($existing) {
            return 'updated';
        }

        $salesHistoryId = isset($this->options['sales_history_id']) ? (int) $this->options['sales_history_id'] : null;
        $line = new SalesHistoryLine;
        $line->company_id = $this->company->id;
        $line->sales_history_id = $salesHistoryId;
        $line->shipment_date = $shipmentDate;
        $line->client_id = $details->user_id;
        $line->client_details_id = $details->id;
        $line->product_id = $product->id;
        $line->quantity = $qty;
        $line->quantity_abs = $qtyAbs;
        $line->amount = $amountAbs !== null ? round($amountAbs, 6) : null;
        $line->unit_price = round($unitPrice, 6);
        $line->is_return = $isReturn;
        $line->currency_id = $this->company->currency_id;
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
        foreach ($row as $key => $value) {
            if (is_string($key) && str_starts_with($key, '__')) {
                continue;
            }
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function getValue(array $row, string $fieldId)
    {
        $keys = array_keys($this->columns, $fieldId);

        return $keys !== [] ? ($row[$keys[0]] ?? null) : null;
    }
}
