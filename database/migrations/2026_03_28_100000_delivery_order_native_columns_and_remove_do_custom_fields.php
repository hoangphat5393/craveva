<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - delivery_order_items: batch / expiry / picking (per line, for inbound stock)
     * - delivery_orders.delivery_fee (replaces DO custom field)
     * - Remove all custom fields tied to Delivery Order models (data + definitions; optional empty groups)
     */
    public function up(): void
    {
        if (Schema::hasTable('delivery_order_items')) {
            Schema::table('delivery_order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('delivery_order_items', 'batch_number')) {
                    $table->string('batch_number', 191)->nullable()->after('product_id');
                }
                if (! Schema::hasColumn('delivery_order_items', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('batch_number');
                }
                if (! Schema::hasColumn('delivery_order_items', 'picking_rule_applied')) {
                    $table->string('picking_rule_applied', 10)->nullable()->after('expiry_date');
                }
            });
        }

        if (Schema::hasTable('delivery_orders')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('delivery_orders', 'delivery_fee')) {
                    $table->decimal('delivery_fee', 16, 2)->nullable()->after('wms_shipment_reference');
                }
            });
        }

        if (! class_exists(CustomField::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $groupIds = CustomFieldGroup::query()
            ->whereIn('model', ['App\\Models\\DeliveryOrder', 'App\\Models\\OrderDelivery'])
            ->pluck('id');

        if ($groupIds->isEmpty()) {
            return;
        }

        $fieldIds = CustomField::query()->whereIn('custom_field_group_id', $groupIds)->pluck('id');

        if ($fieldIds->isNotEmpty()) {
            DB::table('custom_fields_data')->whereIn('custom_field_id', $fieldIds)->delete();
            CustomField::whereIn('id', $fieldIds)->delete();
        }

        CustomFieldGroup::whereIn('id', $groupIds)->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('delivery_orders') && Schema::hasColumn('delivery_orders', 'delivery_fee')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->dropColumn('delivery_fee');
            });
        }

        if (Schema::hasTable('delivery_order_items')) {
            Schema::table('delivery_order_items', function (Blueprint $table) {
                if (Schema::hasColumn('delivery_order_items', 'picking_rule_applied')) {
                    $table->dropColumn('picking_rule_applied');
                }
                if (Schema::hasColumn('delivery_order_items', 'expiry_date')) {
                    $table->dropColumn('expiry_date');
                }
                if (Schema::hasColumn('delivery_order_items', 'batch_number')) {
                    $table->dropColumn('batch_number');
                }
            });
        }

        // Custom field groups are not recreated in down().
    }
};
