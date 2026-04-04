<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToArray;

class ProductImport implements ToArray
{
    protected array $processedData = [];

    /**
     * Slugs that must not collide with custom field `name` (same as static import columns).
     *
     * @var list<string>
     */
    private const CORE_COLUMN_IDS = [
        'product_name',
        'price',
        'unit_type',
        'product_category',
        'product_sub_category',
        'sku',
        'description',
        'specification',
        'storage_condition',
        'certification',
        'wholesale_price',
        'price_per_box',
        'employee_price',
        'shelf_life_days',
        'track_inventory',
        'inventory_type',
        'status',
        'allow_purchase',
        'standard_price',
        'product_grade',
        'product_source',
        'brand',
    ];

    public static function fields(): array
    {
        return [
            ['id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'Yes'],
            ['id' => 'price', 'name' => __('app.price'), 'required' => 'No'],
            ['id' => 'unit_type', 'name' => __('modules.unitType.unitType'), 'required' => 'No'],
            ['id' => 'product_category', 'name' => __('modules.productCategory.productCategory'), 'required' => 'No'],
            ['id' => 'product_sub_category', 'name' => __('modules.productCategory.productSubCategory'), 'required' => 'No'],
            ['id' => 'sku', 'name' => __('app.sku'), 'required' => 'Yes'],
            ['id' => 'description', 'name' => __('app.description'), 'required' => 'No'],
            ['id' => 'specification', 'name' => __('app.specification'), 'required' => 'No'],
            ['id' => 'storage_condition', 'name' => 'Storage Condition', 'required' => 'No'],
            ['id' => 'certification', 'name' => 'Certification', 'required' => 'No'],
            ['id' => 'wholesale_price', 'name' => 'Wholesale Price', 'required' => 'No'],
            ['id' => 'price_per_box', 'name' => 'Price Per Box', 'required' => 'No'],
            ['id' => 'employee_price', 'name' => 'Employee Price', 'required' => 'No'],
            ['id' => 'shelf_life_days', 'name' => __('app.shelfLifeDays'), 'required' => 'No'],
            ['id' => 'track_inventory', 'name' => 'Track Inventory (Yes/No)', 'required' => 'No'],
            ['id' => 'inventory_type', 'name' => 'Inventory Type', 'required' => 'No'],
            ['id' => 'status', 'name' => 'Status (Active/Inactive)', 'required' => 'No'],
            ['id' => 'allow_purchase', 'name' => 'Allow Purchase (Yes/No)', 'required' => 'No'],
            ['id' => 'standard_price', 'name' => 'Standard Price', 'required' => 'No'],
            ['id' => 'product_grade', 'name' => __('app.productGrade'), 'required' => 'No'],
            ['id' => 'product_source', 'name' => __('app.productSource'), 'required' => 'No'],
            ['id' => 'brand', 'name' => __('app.brand'), 'required' => 'No'],
        ];
    }

    /**
     * Same resolution as Client / Inventory import map (company() may be false while user has company_id).
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
     * Append Product module custom fields from DB so import mapping lists them (column id = field `name` slug).
     */
    public static function mergeDynamicColumns(array $columns): array
    {
        $companyId = self::resolveImportCompanyId();
        if (! $companyId) {
            return $columns;
        }

        $group = CustomFieldGroup::where('model', Product::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();

        if (! $group) {
            return $columns;
        }

        $existingIds = collect($columns)->pluck('id')->flip();
        $usedLabels = collect($columns)
            ->pluck('name')
            ->map(fn($n) => mb_strtolower(trim((string) $n)))
            ->filter()
            ->all();

        $customFields = CustomField::where('custom_field_group_id', $group->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($customFields as $cf) {
            $slug = (string) $cf->name;
            if ($slug === '' || in_array($slug, self::CORE_COLUMN_IDS, true)) {
                continue;
            }
            if ($existingIds->has($slug)) {
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

            $columns[] = [
                'id' => $slug,
                'name' => $display,
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
