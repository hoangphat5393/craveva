<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;

return new class extends Migration
{
    public function up(): void
    {
        if (!class_exists(Company::class)) {
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

            $deliveryOrderGroup = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Delivery Order',
                    'model' => 'App\\Models\\DeliveryOrder',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'delivery_fee',
                'label' => 'Delivery Fee',
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

    public function down(): void
    {
        if (!class_exists(Company::class)) {
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

            $deliveryOrderGroup = CustomFieldGroup::where('name', 'Delivery Order')
                ->where('model', 'App\\Models\\DeliveryOrder')
                ->where('company_id', $company->id)
                ->first();

            if ($deliveryOrderGroup) {
                CustomField::where('custom_field_group_id', $deliveryOrderGroup->id)
                    ->where('name', 'delivery_fee')
                    ->delete();
            }
        }
    }
};
