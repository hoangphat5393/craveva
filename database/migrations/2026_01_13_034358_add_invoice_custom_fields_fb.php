<?php

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::firstOrCreate([
                'name' => 'Invoice',
                'model' => 'App\\Models\\Invoice',
                'company_id' => $company->id,
            ]);

            $fields = [
                ['name' => 'batch_number', 'label' => 'Batch / Lot Number', 'type' => 'text', 'values' => null],
                ['name' => 'expiry_date', 'label' => 'Expiry Date', 'type' => 'date', 'values' => null],
                ['name' => 'storage_condition', 'label' => 'Storage Condition', 'type' => 'select', 'values' => json_encode(['Frozen', 'Chilled', 'Ambient'])],
                ['name' => 'unit_of_measure', 'label' => 'Unit of Measure', 'type' => 'select', 'values' => json_encode(['kg', 'g', 'carton', 'pack'])],
                ['name' => 'cost_per_unit', 'label' => 'Cost per Unit', 'type' => 'number', 'values' => null],
                ['name' => 'delivery_reference_no', 'label' => 'Delivery Reference No.', 'type' => 'text', 'values' => null],
                ['name' => 'purchase_order_reference', 'label' => 'Purchase Order Reference', 'type' => 'text', 'values' => null],
                ['name' => 'internal_product_category', 'label' => 'Internal Product Category', 'type' => 'select', 'values' => json_encode(['Food', 'Beverage', 'Alcohol'])],
                ['name' => 'hs_code', 'label' => 'HS Code', 'type' => 'text', 'values' => null],
                ['name' => 'certification_tag', 'label' => 'Halal / Certification Tag', 'type' => 'text', 'values' => null],
            ];

            foreach ($fields as $f) {
                $exists = CustomField::where('custom_field_group_id', $group->id)
                    ->where('name', $f['name'])
                    ->exists();

                if (! $exists) {
                    $field = new CustomField;
                    $field->custom_field_group_id = $group->id;
                    $field->company_id = $company->id;
                    $field->label = $f['label'];
                    $field->name = $f['name'];
                    $field->type = $f['type'];
                    $field->required = 'no';
                    $field->export = 1;
                    if (! is_null($f['values'])) {
                        $field->values = $f['values'];
                    }
                    $field->save();
                }
            }
        }
    }

    public function down(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();
        foreach ($companies as $company) {
            $group = CustomFieldGroup::where('name', 'Invoice')
                ->where('model', 'App\\Models\\Invoice')
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->whereIn('name', [
                        'batch_number',
                        'expiry_date',
                        'storage_condition',
                        'unit_of_measure',
                        'cost_per_unit',
                        'delivery_reference_no',
                        'purchase_order_reference',
                        'internal_product_category',
                        'hs_code',
                        'certification_tag',
                    ])->delete();
            }
        }
    }
};
