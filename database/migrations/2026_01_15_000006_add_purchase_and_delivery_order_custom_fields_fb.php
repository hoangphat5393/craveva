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

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'destination_warehouse_code',
                'label' => 'Destination Warehouse Code',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'destination_warehouse_name',
                'label' => 'Destination Warehouse Name',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($purchaseOrderGroup, $company, [
                'name' => 'expected_delivery_date',
                'label' => 'Expected Delivery Date',
                'type' => 'date',
                'values' => null,
            ]);

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

            // Delivery Order custom fields
            $deliveryOrderGroup = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Delivery Order',
                    'model' => 'App\\Models\\OrderDelivery',
                    'company_id' => $company->id,
                ]
            );

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'batch_number',
                'label' => 'Batch / Lot Number',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'expiry_date',
                'label' => 'Expiry Date',
                'type' => 'date',
                'values' => null,
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'picking_rule_applied',
                'label' => 'Picking Rule Applied (FIFO / FEFO)',
                'type' => 'select',
                'values' => json_encode(['FIFO', 'FEFO']),
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'source_warehouse_code',
                'label' => 'Source Warehouse Code',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'source_warehouse_name',
                'label' => 'Source Warehouse Name',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'erp_shipment_reference',
                'label' => 'ERP Shipment Reference',
                'type' => 'text',
                'values' => null,
            ]);

            $this->createFieldIfMissing($deliveryOrderGroup, $company, [
                'name' => 'wms_shipment_reference',
                'label' => 'WMS Shipment Reference',
                'type' => 'text',
                'values' => null,
            ]);
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
                        'destination_warehouse_code',
                        'destination_warehouse_name',
                        'expected_delivery_date',
                        'erp_po_reference',
                        'wms_po_reference',
                    ])
                    ->delete();
            }

            $deliveryOrderGroup = CustomFieldGroup::where('name', 'Delivery Order')
                ->where('model', 'App\\Models\\OrderDelivery')
                ->where('company_id', $company->id)
                ->first();

            if ($deliveryOrderGroup) {
                CustomField::where('custom_field_group_id', $deliveryOrderGroup->id)
                    ->whereIn('name', [
                        'batch_number',
                        'expiry_date',
                        'picking_rule_applied',
                        'source_warehouse_code',
                        'source_warehouse_name',
                        'erp_shipment_reference',
                        'wms_shipment_reference',
                    ])
                    ->delete();
            }
        }
    }
};
