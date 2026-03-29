<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove custom fields that duplicate native columns or obsolete prototypes:
     * - PO: expected_delivery_date (purchase_orders.expected_delivery_date)
     * - PO: destination_warehouse_code, destination_warehouse_name (replaced by purchase_orders.warehouse_id)
     * - DO: erp_shipment_reference, wms_shipment_reference (delivery_orders.*)
     *
     * Safe to run after manual UI deletion; cleans leftover rows and syncs all envs.
     */
    public function up(): void
    {
        if (! class_exists(CustomField::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $poGroupIds = CustomFieldGroup::query()
            ->where('model', 'Modules\\Purchase\\Entities\\PurchaseOrder')
            ->pluck('id');

        $doGroupIds = CustomFieldGroup::query()
            ->whereIn('model', ['App\\Models\\DeliveryOrder', 'App\\Models\\OrderDelivery'])
            ->pluck('id');

        $fieldIds = collect();

        if ($poGroupIds->isNotEmpty()) {
            $fieldIds = $fieldIds->merge(
                CustomField::query()
                    ->whereIn('custom_field_group_id', $poGroupIds)
                    ->whereIn('name', [
                        'expected_delivery_date',
                        'destination_warehouse_code',
                        'destination_warehouse_name',
                    ])
                    ->pluck('id')
            );
        }

        if ($doGroupIds->isNotEmpty()) {
            $fieldIds = $fieldIds->merge(
                CustomField::query()
                    ->whereIn('custom_field_group_id', $doGroupIds)
                    ->whereIn('name', ['erp_shipment_reference', 'wms_shipment_reference'])
                    ->pluck('id')
            );
        }

        $fieldIds = $fieldIds->unique()->values();

        if ($fieldIds->isEmpty()) {
            return;
        }

        DB::table('custom_fields_data')->whereIn('custom_field_id', $fieldIds)->delete();
        CustomField::whereIn('id', $fieldIds)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally empty: re-seeding removed fields would duplicate business data sources.
    }
};
