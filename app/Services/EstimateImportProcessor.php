<?php

namespace App\Services;

use App\Imports\EstimateImport;
use App\Models\ClientDetails;
use App\Models\Currency;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Product;
use App\Models\UnitType;
use App\Traits\UniversalSearchTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class EstimateImportProcessor
{
    use UniversalSearchTrait;

    /**
     * @param  array<int|string, mixed>  $row
     * @param  array<int|string, string|null>  $columns  index => field id
     * @param  array{import_user_id?: int|null}  $options
     *
     * @throws Exception
     */
    public static function processRow(array $row, $company, array $columns, array $options = []): string
    {
        $companyId = $company?->id;
        if (! $companyId) {
            throw new Exception(__('messages.invalidData'));
        }

        $quotationNumber = trim((string) self::getValue($row, $columns, 'quotation_number'));
        if ($quotationNumber === '') {
            throw new Exception(__('messages.invalidData'));
        }

        $sku = trim((string) self::getValue($row, $columns, 'line_sku'));
        if ($sku === '') {
            throw new Exception(__('messages.invalidData'));
        }

        $qty = self::parseDecimal(self::getValue($row, $columns, 'line_qty'));
        if ($qty === null || $qty <= 0) {
            throw new Exception(__('messages.quantityNumber'));
        }

        $unitPrice = self::parseDecimal(self::getValue($row, $columns, 'line_unit_price'));
        $amount = self::parseDecimal(self::getValue($row, $columns, 'line_amount'));
        if ($amount === null && $unitPrice !== null) {
            $amount = round($qty * $unitPrice, 2);
        }
        if ($amount === null) {
            throw new Exception(__('messages.amountNumber'));
        }
        if ($unitPrice === null && $qty > 0) {
            $unitPrice = round($amount / $qty, 4);
        }
        if ($unitPrice === null) {
            $unitPrice = 0;
        }

        $clientCode = trim((string) self::getValue($row, $columns, 'client_code'));
        if ($clientCode === '') {
            throw new Exception(__('messages.selectCustomer'));
        }

        $clientDetails = ClientDetails::where('company_id', $companyId)
            ->where('client_code', $clientCode)
            ->first();
        if (! $clientDetails) {
            throw new Exception(__('messages.recordNotFound') . ' (client_code: ' . $clientCode . ')');
        }
        $clientId = (int) $clientDetails->user_id;

        $currencyCode = trim((string) (self::getValue($row, $columns, 'currency_code') ?? ''));
        $currencyId = null;
        if ($currencyCode !== '') {
            $currencyId = Currency::where('company_id', $companyId)
                ->where(function ($q) use ($currencyCode) {
                    $q->where('currency_code', $currencyCode)
                        ->orWhere('currency_name', $currencyCode);
                })
                ->value('id');
        }
        if ($currencyId === null) {
            $currencyId = Currency::where('company_id', $companyId)->value('id');
        }
        if ($currencyId === null) {
            throw new Exception(__('messages.recordNotFound') . ' (currency)');
        }

        $quotationDate = self::parseRocOrIsoDate(self::getValue($row, $columns, 'quotation_date'));

        $validTill = $quotationDate?->copy()->addDays(30)
            ?? now()->addDays(30);

        $lineName = trim((string) (self::getValue($row, $columns, 'line_name') ?? ''));
        if ($lineName === '') {
            $lineName = $sku;
        }
        $lineSpec = self::getValue($row, $columns, 'line_spec');
        $itemSummary = $lineSpec !== null && trim((string) $lineSpec) !== '' ? trim((string) $lineSpec) : null;

        $unitLabel = trim((string) (self::getValue($row, $columns, 'line_unit') ?? ''));
        $unitId = null;
        if ($unitLabel !== '') {
            $unitId = UnitType::where('company_id', $companyId)->where('unit_type', $unitLabel)->value('id');
        }

        $productId = Product::where('company_id', $companyId)->where('sku', $sku)->value('id');

        $importUserId = isset($options['import_user_id']) ? (int) $options['import_user_id'] : null;

        return DB::transaction(function () use (
            $companyId,
            $quotationNumber,
            $clientId,
            $currencyId,
            $validTill,
            $lineName,
            $itemSummary,
            $qty,
            $unitPrice,
            $amount,
            $unitId,
            $productId,
            $importUserId,
            $row,
            $columns
        ) {
            $estimate = Estimate::where('company_id', $companyId)
                ->where('estimate_number', $quotationNumber)
                ->lockForUpdate()
                ->first();

            $created = false;
            if (! $estimate) {
                $created = true;
                $estimate = new Estimate;
                $estimate->company_id = $companyId;
                $estimate->client_id = $clientId;
                $estimate->currency_id = $currencyId;
                $estimate->estimate_number = $quotationNumber;
                $estimate->valid_till = $validTill->toDateString();
                $estimate->sub_total = $amount;
                $estimate->total = $amount;
                $estimate->discount = 0;
                $estimate->discount_type = 'percent';
                $estimate->status = 'waiting';
                $estimate->note = null;
                $estimate->description = null;
                $estimate->calculate_tax = 'after_discount';
                $estimate->send_status = true;
                if ($importUserId) {
                    $estimate->added_by = $importUserId;
                    $estimate->last_updated_by = $importUserId;
                }
                $estimate->save();

                $processor = new self;
                $processor->logSearchEntry($estimate->id, $estimate->estimate_number, 'estimates.show', 'estimate', $companyId);
            } else {
                if ((int) $estimate->client_id !== $clientId) {
                    throw new Exception('Quotation ' . $quotationNumber . ' exists for another client.');
                }
                $newSub = round((float) $estimate->sub_total + $amount, 2);
                $newTotal = round((float) $estimate->total + $amount, 2);
                $estimate->sub_total = $newSub;
                $estimate->total = $newTotal;
                if ($importUserId) {
                    $estimate->last_updated_by = $importUserId;
                }
                $estimate->save();
            }

            $item = new EstimateItem;
            $item->estimate_id = $estimate->id;
            $item->item_name = $lineName;
            $item->item_summary = $itemSummary;
            $item->type = 'item';
            $item->quantity = $qty;
            $item->unit_price = $unitPrice;
            $item->amount = $amount;
            $item->taxes = null;
            $item->hsn_sac_code = null;
            if ($productId) {
                $item->product_id = $productId;
            }
            if ($unitId) {
                $item->unit_id = $unitId;
            }
            $item->save();

            self::saveEstimateCustomFieldsFromRow($estimate, $row, $columns, $companyId);

            return $created ? 'created' : 'updated';
        });
    }

    private static function saveEstimateCustomFieldsFromRow(Estimate $estimate, array $row, array $columns, int $companyId): void
    {
        $group = CustomFieldGroup::where('name', 'Estimate')
            ->where('model', Estimate::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();
        if (! $group) {
            return;
        }

        $standardIds = collect(EstimateImport::fields())->pluck('id')->flip();
        $fields = CustomField::where('custom_field_group_id', $group->id)->get()->keyBy('name');

        $data = [];
        foreach ($columns as $idx => $fieldId) {
            if ($fieldId === null || $fieldId === '' || $standardIds->has($fieldId)) {
                continue;
            }
            $field = $fields->get($fieldId);
            if (! $field) {
                continue;
            }
            $val = $row[(int) $idx] ?? null;
            if ($val === null || trim((string) $val) === '') {
                continue;
            }
            $data['field_' . $field->id] = is_scalar($val) ? (string) $val : '';
        }

        if ($data !== []) {
            $estimate->updateCustomFieldData($data, $companyId);
        }
    }

    private static function getValue(array $row, array $columns, string $fieldId): mixed
    {
        foreach ($columns as $idx => $id) {
            if ($id === $fieldId) {
                return $row[(int) $idx] ?? null;
            }
        }

        return null;
    }

    private static function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string) $value));

        return is_numeric($s) ? (float) $s : null;
    }

    private static function parseRocOrIsoDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);
        if (preg_match('/^(\d{1,3})\/(\d{1,2})\/(\d{1,2})$/', $s, $m)) {
            $rocYear = (int) $m[1];
            $year = $rocYear + 1911;

            return Carbon::create($year, (int) $m[2], (int) $m[3])->startOfDay();
        }
        try {
            return Carbon::parse($s)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
