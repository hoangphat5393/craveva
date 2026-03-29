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
            $purchaseOrderGroup = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Purchase Order',
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseOrder',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'delivery_fee',
                'label' => 'Delivery Fee',
                'type' => 'number',
                'values' => null,
            ]);

            // DO delivery_fee: use column delivery_orders.delivery_fee (see migration), not custom field.
        }
    }

    private function createFieldIfMissing(CustomFieldGroup $group, Company $company, array $definition): void
    {
        if (! CustomField::where('custom_field_group_id', $group->id)->where('name', $definition['name'])->exists()) {
            $field = new CustomField;
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

    public function down(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $purchaseOrderGroup = CustomFieldGroup::where('name', 'Purchase Order')
                ->where('model', 'Modules\\Purchase\\Entities\\PurchaseOrder')
                ->where('company_id', $company->id)
                ->first();

            if ($purchaseOrderGroup) {
                CustomField::where('custom_field_group_id', $purchaseOrderGroup->id)
                    ->where('name', 'delivery_fee')
                    ->delete();
            }
        }
    }
};
