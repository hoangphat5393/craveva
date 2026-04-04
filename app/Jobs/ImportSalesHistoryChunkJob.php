<?php

namespace App\Jobs;

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

    /** Batched DB IO + bulk insert (aligned with former stream job). */
    public int $timeout = 300;

    public int $tries = 2;

    private const INSERT_CHUNK = 200;

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

        $salesHistoryId = isset($this->options['sales_history_id']) ? (int) $this->options['sales_history_id'] : 0;
        if ($salesHistoryId <= 0) {
            $this->fail(__('messages.invalidData') . ': sales_history_id is required for sales history import.');

            return;
        }

        @set_time_limit(0);

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $missingRequiredCount = 0;
        $invalidStatusCount = 0;
        $failures = [];
        $candidates = [];

        foreach ($this->rows as $index => $row) {
            $fileRow = $this->chunkStartIndex + $index + 2;
            $row = $this->normalizeRow($row);

            if ($this->isEmptyRow($row)) {
                $skippedCount++;

                continue;
            }

            try {
                $prepared = $this->prepareDataRow($row, $this->company);
            } catch (Exception $e) {
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();

                continue;
            }

            if ($prepared === null) {
                $skippedCount++;
                $missingRequiredCount++;

                continue;
            }

            $candidates[] = [
                'fileRow' => $fileRow,
                'data' => $prepared,
            ];
        }

        if ($candidates !== []) {
            $company = $this->company;
            $codes = [];
            $skus = [];
            foreach ($candidates as $c) {
                $codes[$c['data']['customerCode']] = true;
                $skus[$c['data']['sku']] = true;
            }
            $codeList = array_keys($codes);
            $skuList = array_keys($skus);

            $clientMap = DB::table('client_details')
                ->where('company_id', $company->id)
                ->whereIn('client_code', $codeList)
                ->get(['user_id', 'id', 'client_code'])
                ->keyBy('client_code');

            $productMap = DB::table('products')
                ->where('company_id', $company->id)
                ->whereIn('sku', $skuList)
                ->pluck('id', 'sku');

            $hashesForLookup = [];
            foreach ($candidates as $c) {
                $hashesForLookup[$c['data']['sourceHash']] = true;
            }
            $hashList = array_keys($hashesForLookup);

            $existingHashes = $hashList === []
                ? []
                : DB::table('sales_history_lines')
                ->where('company_id', $company->id)
                ->whereIn('source_row_hash', $hashList)
                ->pluck('source_row_hash')
                ->all();

            $seenHash = array_flip($existingHashes);

            $insertRows = [];
            $now = now()->toDateTimeString();

            foreach ($candidates as $c) {
                $fileRow = $c['fileRow'];
                $d = $c['data'];
                $code = $d['customerCode'];
                $sku = $d['sku'];

                $clientRow = $clientMap->get($code);
                if (! $clientRow || ! $clientRow->user_id) {
                    $failures[] = 'Row ' . $fileRow . ': Client not found by code: ' . $code;

                    continue;
                }

                if (! $productMap->has($sku)) {
                    $failures[] = 'Row ' . $fileRow . ': Product not found by SKU: ' . $sku;

                    continue;
                }

                $h = $d['sourceHash'];
                if (isset($seenHash[$h])) {
                    $updatedCount++;

                    continue;
                }
                $seenHash[$h] = true;

                $insertRows[] = [
                    'company_id' => $company->id,
                    'sales_history_id' => $salesHistoryId,
                    'shipment_date' => $d['shipmentDate'],
                    'client_id' => $clientRow->user_id,
                    'client_details_id' => $clientRow->id,
                    'product_id' => (int) $productMap[$sku],
                    'quantity' => $d['qty'],
                    'quantity_abs' => $d['qtyAbs'],
                    'amount' => $d['amountAbs'],
                    'unit_price' => $d['unitPrice'],
                    'is_return' => $d['isReturn'] ? 1 : 0,
                    'currency_id' => $company->currency_id,
                    'source_sheet_name' => null,
                    'source_row_hash' => $h,
                    'net_sales_volume_raw' => $d['net_sales_volume_raw'],
                    'net_sales_amount_raw' => $d['net_sales_amount_raw'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($insertRows, self::INSERT_CHUNK) as $chunk) {
                if ($chunk === []) {
                    continue;
                }
                try {
                    DB::table('sales_history_lines')->insert($chunk);
                    $createdCount += count($chunk);
                } catch (\Throwable $e) {
                    foreach ($chunk as $one) {
                        try {
                            DB::table('sales_history_lines')->insert([$one]);
                            $createdCount++;
                        } catch (\Throwable $inner) {
                            if ($this->isDuplicateHashException($inner)) {
                                $updatedCount++;
                            } else {
                                $failures[] = 'Row (hash ' . substr((string) $one['source_row_hash'], 0, 8) . '…): ' . $inner->getMessage();
                            }
                        }
                    }
                }
            }
        }

        if ($this->batchId) {
            $this->mergeImportBatchMetrics($this->batchId, [
                'created' => $createdCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'skipped_missing_required' => $missingRequiredCount,
                'invalid_status' => $invalidStatusCount + count($failures),
            ]);
        }

        if ($failures !== []) {
            $this->mergeImportBatchRowErrors($this->batchId, $failures);
        }
    }

    /**
     * @return null|array<string, mixed> null = missing required columns (skip row)
     *
     * @throws Exception
     */
    private function prepareDataRow(array $row, $company): ?array
    {
        $customerCode = trim((string) $this->getValue($row, 'customer_number'));
        $sku = trim((string) $this->getValue($row, 'product_part_number'));
        $shipmentDateRaw = $this->getValue($row, 'shipment_return_date');
        $qtyRaw = $this->getValue($row, 'net_sales_volume');
        $amountRaw = $this->getValue($row, 'net_sales_amount');

        if ($customerCode === '' || $sku === '' || $shipmentDateRaw === null || trim((string) $shipmentDateRaw) === '' || $qtyRaw === null || trim((string) $qtyRaw) === '') {
            return null;
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

        return [
            'customerCode' => $customerCode,
            'sku' => $sku,
            'shipmentDate' => $shipmentDate,
            'qty' => $qty,
            'qtyAbs' => $qtyAbs,
            'amountAbs' => $amountAbs !== null ? round($amountAbs, 6) : null,
            'unitPrice' => round($unitPrice, 6),
            'isReturn' => $isReturn,
            'sourceHash' => $sourceHash,
            'net_sales_volume_raw' => $qty,
            'net_sales_amount_raw' => $amount,
        ];
    }

    private function isDuplicateHashException(\Throwable $e): bool
    {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate') !== false || stripos($msg, 'UNIQUE') !== false || stripos($msg, '1062') !== false) {
            return true;
        }

        $prev = $e->getPrevious();
        if ($prev instanceof \Throwable) {
            return $this->isDuplicateHashException($prev);
        }

        return false;
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
        $keys = array_keys($this->columns, $fieldId, true);

        return $keys !== [] ? ($row[$keys[0]] ?? null) : null;
    }
}
