<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes legacy demo/seed custom fields for Invoice & PO (each tenant configures CF in Settings).
     * Drops migration history rows for deleted seed files so migrate/rollback stays consistent.
     */
    public function up(): void
    {
        $legacyMigrationNames = [
            '2026_01_13_034358_add_invoice_custom_fields_fb',
            '2026_01_18_000200_add_invoice_delivery_fee_custom_field_fb',
            '2026_01_15_000006_add_purchase_and_delivery_order_custom_fields_fb',
            '2026_01_18_000100_add_delivery_fee_custom_fields_for_po_and_do_fb',
        ];

        if (Schema::hasTable('migrations')) {
            DB::table('migrations')->whereIn('migration', $legacyMigrationNames)->delete();
            // Some installs store with .php suffix
            foreach ($legacyMigrationNames as $name) {
                DB::table('migrations')->where('migration', $name . '.php')->delete();
            }
        }

        if (! Schema::hasTable('custom_fields') || ! class_exists(CustomField::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $invoiceSlugs = [
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
            'delivery_fee',
        ];

        $poSlugs = [
            'batch_tracking_required',
            'erp_po_reference',
            'wms_po_reference',
            'delivery_fee',
        ];

        $this->deleteFieldsForModel('App\\Models\\Invoice', $invoiceSlugs);
        $this->deleteFieldsForModel('Modules\\Purchase\\Entities\\PurchaseOrder', $poSlugs);
    }

    /**
     * @param  array<int, string>  $names
     */
    private function deleteFieldsForModel(string $model, array $names): void
    {
        $groupIds = CustomFieldGroup::query()->where('model', $model)->pluck('id');
        if ($groupIds->isEmpty()) {
            return;
        }

        $fieldIds = CustomField::query()
            ->whereIn('custom_field_group_id', $groupIds)
            ->whereIn('name', $names)
            ->pluck('id');

        if ($fieldIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('custom_fields_data')) {
            DB::table('custom_fields_data')->whereIn('custom_field_id', $fieldIds)->delete();
        }

        CustomField::whereIn('id', $fieldIds)->delete();
    }

    public function down(): void
    {
        // Irreversible: seed migrations removed from repo.
    }
};
