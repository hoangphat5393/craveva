<?php

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Schema Changes for Purchase Tables
        $this->updatePurchaseTables();

        // 2. Custom Fields Setup for Inventory
        $this->setupInventoryCustomFields();
    }

    private function updatePurchaseTables()
    {
        // purchase_inventory_adjustment
        if (Schema::hasTable('purchase_inventory_adjustment')) {
            Schema::table('purchase_inventory_adjustment', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_inventory_adjustment', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('date');
                    $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
                } else {
                    // Ensure it's nullable
                    try {
                        DB::statement('ALTER TABLE purchase_inventory_adjustment MODIFY warehouse_id BIGINT UNSIGNED NULL');
                    } catch (\Exception $e) {
                    }
                }

                if (! Schema::hasColumn('purchase_inventory_adjustment', 'added_by')) {
                    $table->integer('added_by')->unsigned()->nullable()->after('warehouse_id');
                    $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('CASCADE');
                }
            });
        }

        // purchase_stock_adjustments
        if (Schema::hasTable('purchase_stock_adjustments')) {
            Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                if (! Schema::hasColumn('purchase_stock_adjustments', 'warehouse_id')) {
                    $table->unsignedBigInteger('warehouse_id')->nullable()->after('product_id');
                    $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
                } else {
                    // Ensure it's nullable
                    try {
                        DB::statement('ALTER TABLE purchase_stock_adjustments MODIFY warehouse_id BIGINT UNSIGNED NULL');
                    } catch (\Exception $e) {
                    }
                }

                if (! Schema::hasColumn('purchase_stock_adjustments', 'manufacturing_date')) {
                    $table->date('manufacturing_date')->nullable()->after('date');
                }

                if (! Schema::hasColumn('purchase_stock_adjustments', 'expiration_date')) {
                    $table->date('expiration_date')->nullable()->after('manufacturing_date');
                }

                if (! Schema::hasColumn('purchase_stock_adjustments', 'status')) {
                    $table->string('status')->default('draft')->nullable()->after('description');
                }

                if (! Schema::hasColumn('purchase_stock_adjustments', 'changed_value')) {
                    $table->double('changed_value', 16, 2)->default(0)->nullable()->after('status');
                }

                if (! Schema::hasColumn('purchase_stock_adjustments', 'adjusted_value')) {
                    $table->double('adjusted_value', 16, 2)->default(0)->nullable()->after('changed_value');
                }
            });

            // Ensure warehouse_id type is correct
            if (Schema::hasColumn('purchase_stock_adjustments', 'warehouse_id')) {
                try {
                    DB::statement('ALTER TABLE purchase_stock_adjustments MODIFY warehouse_id BIGINT UNSIGNED NULL');
                } catch (\Exception $e) {
                }
            }
        }

        // purchase_inventories - ensure warehouse_id is nullable if it exists
        if (Schema::hasTable('purchase_inventories') && Schema::hasColumn('purchase_inventories', 'warehouse_id')) {
            try {
                DB::statement('ALTER TABLE purchase_inventories MODIFY warehouse_id BIGINT UNSIGNED NULL');
            } catch (\Exception $e) {
            }
        }
    }

    private function setupInventoryCustomFields()
    {
        if (! class_exists(Company::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            // Get or Create Custom Field Group for PurchaseInventory
            $group = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Inventory',
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseInventory',
                    'company_id' => $company->id,
                ]
            );

            // Cleanup duplicates
            CustomField::where('label', 'Beginning Packaging Inventory')
                ->where('custom_field_group_id', $group->id)
                ->delete();

            // List of fields to ensure exist
            $fields = [
                // Warehouse & Context
                ['name' => 'warehouse_code', 'label' => 'purchase::modules.inventory.warehouseCode', 'type' => 'text', 'sort_order' => 1],
                ['name' => 'warehouse_name', 'label' => 'purchase::modules.inventory.warehouseName', 'type' => 'text', 'sort_order' => 2],
                ['name' => 'batch_number', 'label' => 'purchase::modules.inventory.batchNumber', 'type' => 'text', 'sort_order' => 3],

                // Dates
                ['name' => 'expiration_date', 'label' => 'purchase::modules.inventory.expirationDate', 'type' => 'date', 'sort_order' => 4],
                ['name' => 'manufacturing_date', 'label' => 'purchase::modules.inventory.manufacturingDate', 'type' => 'date', 'sort_order' => 5],
                ['name' => 'recent_inbound_date', 'label' => 'purchase::modules.inventory.recentInboundDate', 'type' => 'date', 'sort_order' => 12],
                ['name' => 'batch_recent_inbound_date', 'label' => 'purchase::modules.inventory.batchRecentInboundDate', 'type' => 'date', 'sort_order' => 14],

                // Specs & Units
                ['name' => 'specification', 'label' => 'purchase::modules.inventory.specification', 'type' => 'text', 'sort_order' => 6],
                ['name' => 'packaging_unit', 'label' => 'purchase::modules.inventory.packagingUnit', 'type' => 'text', 'sort_order' => 7],
                ['name' => 'small_unit', 'label' => 'purchase::modules.inventory.smallUnit', 'type' => 'text', 'sort_order' => 8],

                // Quantities
                ['name' => 'beginning_inventory', 'label' => 'purchase::modules.inventory.beginningInventory', 'type' => 'number', 'sort_order' => 9],
                ['name' => 'inbound_quantity', 'label' => 'purchase::modules.inventory.inboundQuantity', 'type' => 'number', 'sort_order' => 10],
                ['name' => 'outbound_quantity', 'label' => 'purchase::modules.inventory.outboundQuantity', 'type' => 'number', 'sort_order' => 11],
                ['name' => 'beginning_package_inventory', 'label' => 'purchase::modules.inventory.beginningPackageInventory', 'type' => 'number', 'sort_order' => 13],

                // New Packaging Quantities
                ['name' => 'packaging_inbound_quantity', 'label' => 'purchase::modules.inventory.packagingInboundQuantity', 'type' => 'number', 'sort_order' => 16],
                ['name' => 'packaging_outbound_quantity', 'label' => 'purchase::modules.inventory.packagingOutboundQuantity', 'type' => 'number', 'sort_order' => 17],

                // Other
                ['name' => 'closing_code', 'label' => 'purchase::modules.inventory.closingCode', 'type' => 'text', 'sort_order' => 15],
            ];

            foreach ($fields as $fieldDef) {
                $field = CustomField::where('custom_field_group_id', $group->id)
                    ->where('name', $fieldDef['name'])
                    ->first();

                if (! $field) {
                    $field = new CustomField;
                    $field->custom_field_group_id = $group->id;
                    $field->company_id = $company->id;
                    $field->name = $fieldDef['name'];
                    $field->required = 'no';
                    $field->export = 1;
                }

                // Update properties
                $field->label = $fieldDef['label'];
                $field->type = $fieldDef['type'];
                $field->sort_order = $fieldDef['sort_order'];
                $field->save();
            }

            // Also update legacy/alt names if they exist to match standard
            CustomField::where('custom_field_group_id', $group->id)
                ->where('name', 'expiry_date')
                ->update(['label' => 'purchase::modules.inventory.expirationDate']);

            CustomField::where('custom_field_group_id', $group->id)
                ->where('name', 'location_code')
                ->update(['label' => 'purchase::modules.inventory.locationCode']);

            CustomField::where('custom_field_group_id', $group->id)
                ->where('name', 'near_expiry_status')
                ->update(['label' => 'purchase::modules.inventory.nearExpiryStatus']);

            CustomField::where('custom_field_group_id', $group->id)
                ->where('name', 'reserved_quantity')
                ->update(['label' => 'purchase::modules.inventory.reservedQuantity']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for merged additive migration
    }
};
