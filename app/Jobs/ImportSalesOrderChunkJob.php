<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\UnitType;
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

class ImportSalesOrderChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StoresImportBatchMetrics;

    private array $rows;

    private array $columns;

    private $company;

    private int $chunkStartIndex;

    public function __construct(array $rows, array $columns, $company = null, int $chunkStartIndex = 0, array $options = [])
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
        $this->chunkStartIndex = $chunkStartIndex;
    }

    public function handle(): void
    {
        if (! $this->company?->id) {
            $this->fail(__('messages.invalidData').': Company context is required for SO import.');

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
                $failures[] = 'Row '.$fileRow.': '.$e->getMessage();
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
                $message .= "\n… and ".(count($failures) - 50).' more';
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

        $customerCode = trim((string) $this->getValue($row, 'customer_number'));
        $sku = trim((string) $this->getValue($row, 'product_part_number'));
        $shipmentDateRaw = $this->getValue($row, 'shipment_return_date');
        $qtyRaw = $this->getValue($row, 'net_sales_volume');
        $amountRaw = $this->getValue($row, 'net_sales_amount');

        if ($customerCode === '' || $sku === '' || $shipmentDateRaw === null || trim((string) $shipmentDateRaw) === '' || $qtyRaw === null || trim((string) $qtyRaw) === '') {
            return 'missing_required';
        }

        $client = DB::table('client_details')
            ->where('company_id', $this->company->id)
            ->where('client_code', $customerCode)
            ->first(['user_id']);

        if (! $client?->user_id) {
            throw new Exception("Client not found by code: {$customerCode}");
        }

        $product = Product::query()
            ->where('company_id', $this->company->id)
            ->where('sku', $sku)
            ->first(['id', 'name', 'sku', 'unit_id']);

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

        $existing = DB::table('order_import_rows')
            ->where('company_id', $this->company->id)
            ->where('source_hash', $sourceHash)
            ->first(['id']);

        if ($existing) {
            return 'updated';
        }

        $order = new Order;
        $order->company_id = $this->company->id;
        $order->client_id = $client->user_id;
        $order->order_number = 'SOIMP-'.substr($sourceHash, 0, 10);
        $order->order_date = $shipmentDate;
        $order->sub_total = round($amountAbs ?? ($qtyAbs * $unitPrice), 2);
        $order->total = round($amountAbs ?? ($qtyAbs * $unitPrice), 2);
        $order->discount = 0;
        $order->discount_type = 'fixed';
        $order->status = $isReturn ? 'refunded' : 'completed';
        $order->currency_id = $this->company->currency_id;
        $order->show_shipping_address = 'no';
        $order->company_address_id = DB::table('company_addresses')
            ->where('company_id', $this->company->id)
            ->where('is_default', 1)
            ->value('id');
        $order->note = trim('Imported SO from Last year net sales. Source hash: '.$sourceHash);
        $order->save();

        $item = new OrderItems;
        $item->order_id = $order->id;
        $item->product_id = $product->id;
        $item->item_name = $product->name;
        $item->item_summary = '';
        $item->type = 'item';
        $item->quantity = $qtyAbs;
        $item->unit_price = round($unitPrice, 2);
        $item->amount = round($amountAbs ?? ($qtyAbs * $unitPrice), 2);
        $item->unit_id = $product->unit_id ?: UnitType::query()
            ->where('company_id', $this->company->id)
            ->value('id');
        $item->sku = $product->sku;
        $item->field_order = 1;
        $item->save();

        DB::table('order_import_rows')->insert([
            'company_id' => $this->company->id,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'source_hash' => $sourceHash,
            'shipment_date' => $shipmentDate,
            'customer_code' => $customerCode,
            'product_sku' => $sku,
            'net_sales_volume' => $qty,
            'net_sales_amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            throw new Exception('Invalid Shipment/Return Date: '.$value);
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
            throw new Exception('Invalid numeric value: '.$value);
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
        $keys = array_keys($this->columns, $fieldId);

        return $keys !== [] ? ($row[$keys[0]] ?? null) : null;
    }
}
