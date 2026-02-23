<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Product',
                    'model' => 'App\\Models\\Product',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($group, $company, [
                'name' => 'batch_tracking_enabled',
                'label' => 'Batch / Lot Tracking Enabled',
                'type' => 'select',
                'values' => json_encode(['yes', 'no']),
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'inventory_issue_rule',
                'label' => 'FEFO / FIFO Rule',
                'type' => 'select',
                'values' => json_encode(['FIFO', 'FEFO']),
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'near_expiry_days_threshold',
                'label' => 'Near-Expiry Days Threshold',
                'type' => 'number',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'near_expiry_discount_eligible',
                'label' => 'Near-Expiry Discount Eligible',
                'type' => 'select',
                'values' => json_encode(['yes', 'no']),
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'erp_sku_mapping',
                'label' => 'ERP SKU Mapping',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'wms_sku_mapping',
                'label' => 'WMS SKU Mapping',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'brand',
                'label' => 'Brand',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'shelf_life_days',
                'label' => 'Shelf Life (Days)',
                'type' => 'number',
                'values' => null,
            ]);
        }
    }

    private function createFieldIfMissing(CustomFieldGroup $group, Company $company, array $definition): void
    {
        if (!CustomField::where('custom_field_group_id', $group->id)->where('name', $definition['name'])->exists()) {
            $field = new CustomField();
            $field->custom_field_group_id = $group->id;
            $field->company_id = $company->id;
            $field->label = $definition['label'];
            $field->name = $definition['name'];
            $field->type = $definition['type'];
            $field->values = $definition['values'];
            $field->required = 'no';
            $field->export = 1;
            $field->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::where('name', 'Product')
                ->where('model', 'App\\Models\\Product')
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->whereIn('name', [
                        'batch_tracking_enabled',
                        'inventory_issue_rule',
                        'near_expiry_days_threshold',
                        'near_expiry_discount_eligible',
                        'erp_sku_mapping',
                        'wms_sku_mapping',
                        'brand',
                        'shelf_life_days',
                    ])
                    ->delete();
            }
        }
    }
};
