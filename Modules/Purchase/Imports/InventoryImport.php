<?php

namespace Modules\Purchase\Imports;

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Maatwebsite\Excel\Concerns\ToArray;
use Modules\Purchase\Entities\PurchaseInventory;

class InventoryImport implements ToArray
{
    protected array $processedData = [];

    /**
     * Core import column ids (ImportInventoryJob + stock line). Custom fields must not duplicate these slugs.
     */
    private const CORE_COLUMN_IDS = [
        'sku',
        'product_name',
        'date',
        'type',
        'warehouse_code',
        'warehouse_name',
        'quantity',
        'ending_inventory',
        'reserved_quantity',
        'cost_price',
        'description',
        'unit',
        'specification',
        'batch_number',
        'manufacturing_date',
        'expiration_date',
    ];

    public static function fields(): array
    {
        // Order tuned for typical ERP exports (SKU → warehouse → batch/dates → quantities → optional).
        $fields = [
            ['id' => 'sku', 'name' => __('app.sku'), 'required' => 'No'],
            ['id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'No'],
            ['id' => 'warehouse_code', 'name' => __('purchase::modules.inventory.warehouseCode'), 'required' => 'No'],
            ['id' => 'warehouse_name', 'name' => __('purchase::modules.inventory.warehouseName'), 'required' => 'No'],
            ['id' => 'batch_number', 'name' => __('purchase::modules.inventory.batchNumber'), 'required' => 'No'],
            ['id' => 'expiration_date', 'name' => __('purchase::modules.inventory.expirationDate'), 'required' => 'No'],
            ['id' => 'manufacturing_date', 'name' => __('purchase::modules.inventory.manufacturingDate'), 'required' => 'No'],
            ['id' => 'ending_inventory', 'name' => __('purchase::modules.inventory.endingInventory'), 'required' => 'No'],
            ['id' => 'quantity', 'name' => __('purchase::modules.product.quantity'), 'required' => 'No'],
            ['id' => 'reserved_quantity', 'name' => __('purchase::modules.inventory.reservedQuantity'), 'required' => 'No'],
            ['id' => 'specification', 'name' => __('purchase::modules.inventory.specification'), 'required' => 'No'],
            ['id' => 'unit', 'name' => __('modules.unitType.unitType'), 'required' => 'No'],
            ['id' => 'date', 'name' => __('app.date'), 'required' => 'No'],
            ['id' => 'type', 'name' => __('app.type'), 'required' => 'No'],
            ['id' => 'cost_price', 'name' => __('app.price'), 'required' => 'No'],
            ['id' => 'description', 'name' => __('app.description'), 'required' => 'No'],
        ];

        // ImportExcel trait calls mergeDynamicColumns() after fields() — do not merge here (avoids duplicates).
        return $fields;
    }

    /**
     * Same resolution as Client import map (company() may be false while user has company_id).
     */
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

    /**
     * Append Inventory custom fields; dedupe by slug (name) vs core columns and by translated label vs core labels.
     */
    public static function mergeDynamicColumns(array $columns): array
    {
        $companyId = self::resolveImportCompanyId();
        if (! $companyId || ! request()->user()) {
            return $columns;
        }

        $group = CustomFieldGroup::where('model', PurchaseInventory::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();

        if (! $group) {
            return $columns;
        }

        $existingIds = collect($columns)->pluck('id')->flip();
        $usedLabels = collect($columns)
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower(trim((string) $n)))
            ->filter()
            ->all();

        $fields = CustomField::where('custom_field_group_id', $group->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($fields as $cf) {
            if ($existingIds->has('field_'.$cf->id)) {
                continue;
            }

            $slug = (string) $cf->name;
            if ($slug !== '' && in_array($slug, self::CORE_COLUMN_IDS, true)) {
                // Same semantic as a core column (e.g. batch_number) — map only the core field.
                continue;
            }

            $display = trim((string) __($cf->label));
            if ($display === '') {
                continue;
            }

            $labelKey = mb_strtolower($display);
            if (in_array($labelKey, $usedLabels, true)) {
                continue;
            }

            $badge = __('purchase::modules.inventory.importCustomFieldBadge');
            $suffix = $badge !== 'purchase::modules.inventory.importCustomFieldBadge' ? $badge : '';

            $columns[] = [
                'id' => 'field_'.$cf->id,
                'name' => $suffix !== '' ? ($display.$suffix) : $display,
                'required' => strtolower((string) $cf->required) === 'yes' ? 'Yes' : 'No',
            ];
            $usedLabels[] = $labelKey;
        }

        return $columns;
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
