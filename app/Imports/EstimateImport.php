<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\ToArray;

class EstimateImport implements ToArray
{
    protected array $processedData = [];

    /**
     * Column ids that are "header" level in Maolin export and may be blank on continuation lines.
     */
    public static function forwardFillFieldIds(): array
    {
        return [
            'quotation_date',
            'quotation_number',
            'client_code',
            'client_short_name',
            'document_date',
            'customer_full_name',
            'currency_code',
        ];
    }

    public static function fields(): array
    {
        return [
            ['id' => 'quotation_date', 'name' => 'Quotation date (ROC y/m/d)', 'required' => 'No'],
            ['id' => 'quotation_number', 'name' => 'Quotation # / 報價單號', 'required' => 'Yes'],
            ['id' => 'client_code', 'name' => 'Customer code / 客戶代號', 'required' => 'Yes'],
            ['id' => 'client_short_name', 'name' => 'Customer short name / 客戶簡稱', 'required' => 'No'],
            ['id' => 'document_date', 'name' => 'Document date / 單據日期', 'required' => 'No'],
            ['id' => 'customer_full_name', 'name' => 'Customer full name / 客戶全名', 'required' => 'No'],
            ['id' => 'currency_code', 'name' => 'Currency / 幣別', 'required' => 'No'],
            ['id' => 'line_sku', 'name' => 'Line SKU / 品號', 'required' => 'Yes'],
            ['id' => 'line_name', 'name' => 'Line product name / 品名', 'required' => 'No'],
            ['id' => 'line_spec', 'name' => 'Line spec / 規格', 'required' => 'No'],
            ['id' => 'line_qty', 'name' => 'Line quantity / 數量', 'required' => 'Yes'],
            ['id' => 'line_unit', 'name' => 'Line unit / 單位', 'required' => 'No'],
            ['id' => 'line_unit_price', 'name' => 'Unit price / 單價', 'required' => 'No'],
            ['id' => 'line_amount', 'name' => 'Line amount / 金額', 'required' => 'No'],
        ];
    }

    public static function resolveImportCompanyId(): ?int
    {
        $co = company();
        if ($co instanceof Company) {
            return (int) $co->id;
        }

        $user = function_exists('user') ? user() : null;
        if ($user && ($user->company_id ?? null)) {
            return (int) $user->company_id;
        }

        return null;
    }

    public static function mergeDynamicColumns(array $columns): array
    {
        $companyId = self::resolveImportCompanyId();
        if (! $companyId) {
            return $columns;
        }

        $group = CustomFieldGroup::where('name', 'Estimate')
            ->where('model', Estimate::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();

        if (! $group) {
            return $columns;
        }

        $existingIds = collect($columns)->pluck('id')->flip();
        $customFields = CustomField::where('custom_field_group_id', $group->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($customFields as $cf) {
            if ($existingIds->has($cf->name)) {
                continue;
            }
            $columns[] = [
                'id' => $cf->name,
                'name' => $cf->label,
                'required' => strtolower((string) $cf->required) === 'yes' ? 'Yes' : 'No',
            ];
        }

        return $columns;
    }

    /**
     * Forward-fill header columns so each row is self-contained (Maolin continuation lines).
     *
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int|string, string|null>  $columns
     * @return array<int, array<int, mixed>>
     */
    public static function forwardFillRows(array $rows, array $columns): array
    {
        $fillIds = self::forwardFillFieldIds();
        $indexByField = [];
        foreach ($columns as $idx => $fieldId) {
            if ($fieldId === null || $fieldId === '') {
                continue;
            }
            if (in_array($fieldId, $fillIds, true)) {
                $indexByField[$fieldId] = (int) $idx;
            }
        }

        if ($indexByField === []) {
            return $rows;
        }

        $last = [];
        $out = [];
        foreach ($rows as $row) {
            $row = array_values($row);
            foreach ($indexByField as $fieldId => $colIdx) {
                $raw = $row[$colIdx] ?? null;
                $val = $raw === null ? '' : trim((string) $raw);
                if ($val === '' && array_key_exists($fieldId, $last)) {
                    $row[$colIdx] = $last[$fieldId];
                } elseif ($val !== '') {
                    $last[$fieldId] = $row[$colIdx];
                }
            }
            $out[] = $row;
        }

        return $out;
    }

    public function array(array $array): array
    {
        $this->processedData = $array;

        return $array;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }
}
