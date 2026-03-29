<?php

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            // Purchase Order custom fields
            $purchaseOrderGroup = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Purchase Order',
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseOrder',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'batch_tracking_required',
                'label' => 'Batch Tracking Required (At Receiving)',
                'type' => 'select',
                'values' => json_encode(['yes', 'no']),
            ]);

            // destination_warehouse_code/name: removed — prototype before multi-warehouse; use purchase_orders.warehouse_id.

            // expected_delivery_date: use column purchase_orders.expected_delivery_date only (no duplicate CF)

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'erp_po_reference',
                'label' => 'ERP Purchase Order Reference',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'wms_po_reference',
                'label' => 'WMS Purchase Order Reference',
                'type' => 'text',
                'values' => null,
            ]);

            // Delivery Order: no custom fields seeded — use delivery_orders / delivery_order_items columns + Warehouse module.
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

    /**
     * Reverse the migrations.
     */
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
                    ->whereIn('name', [
                        'batch_tracking_required',
                        'erp_po_reference',
                        'wms_po_reference',
                    ])
                    ->delete();
            }
        }
    }
};
