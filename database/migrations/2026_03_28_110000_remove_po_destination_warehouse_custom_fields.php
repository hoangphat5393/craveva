<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove PO prototype CFs (pre–multi-warehouse): use purchase_orders.warehouse_id.
     * Idempotent; for DBs that already ran 2026_03_27_140000 before destination_* was added to that migration.
     */
    public function up(): void
    {
        if (! class_exists(CustomField::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $poGroupIds = CustomFieldGroup::query()
            ->where('model', 'Modules\\Purchase\\Entities\\PurchaseOrder')
            ->pluck('id');

        if ($poGroupIds->isEmpty()) {
            return;
        }

        $fieldIds = CustomField::query()
            ->whereIn('custom_field_group_id', $poGroupIds)
            ->whereIn('name', ['destination_warehouse_code', 'destination_warehouse_name'])
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        DB::table('custom_fields_data')->whereIn('custom_field_id', $fieldIds)->delete();
        CustomField::whereIn('id', $fieldIds)->delete();
    }

    public function down(): void
    {
        // Intentionally empty — do not re-seed prototype fields.
    }
};
