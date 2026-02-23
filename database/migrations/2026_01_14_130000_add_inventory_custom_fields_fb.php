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
                    'name' => 'Inventory',
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseInventory',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($group, $company, [
                'name' => 'warehouse_code',
                'label' => 'Warehouse Code',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'warehouse_name',
                'label' => 'Warehouse Name',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'location_code',
                'label' => 'Location Code',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'batch_number',
                'label' => 'Batch / Lot Number',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'manufacturing_date',
                'label' => 'Manufacturing Date',
                'type' => 'date',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'expiry_date',
                'label' => 'Expiry Date',
                'type' => 'date',
                'values' => null,
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'near_expiry_status',
                'label' => 'Near-Expiry Status',
                'type' => 'select',
                'values' => json_encode(['normal', 'near_expiry', 'expired']),
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'reserved_quantity',
                'label' => 'Reserved Quantity',
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
            $group = CustomFieldGroup::where('name', 'Inventory')
                ->where('model', 'Modules\\Purchase\\Entities\\PurchaseInventory')
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->whereIn('name', [
                        'warehouse_code',
                        'warehouse_name',
                        'location_code',
                        'batch_number',
                        'manufacturing_date',
                        'expiry_date',
                        'near_expiry_status',
                        'reserved_quantity',
                    ])
                    ->delete();
            }
        }
    }
};
