<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove UNIQUE(product_id, warehouse_id) — it blocks valid data:
     * each PurchaseInventory document has its own lines; the same product in the same
     * warehouse may appear on many documents (imports, adjustments, batches).
     *
     * MySQL/InnoDB: that composite UNIQUE is often the supporting index for FK(product_id)->products.
     * You cannot DROP INDEX until those foreign keys are dropped, then re-created (new indexes).
     */
    public function up(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        if (! Schema::hasIndex('purchase_stock_adjustments', 'psa_product_warehouse_unique')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                $table->dropUnique('psa_product_warehouse_unique');
            });

            return;
        }

        $this->dropAllForeignKeysOnPurchaseStockAdjustments();

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->dropUnique('psa_product_warehouse_unique');
        });

        $this->restorePurchaseStockAdjustmentsForeignKeys();
    }

    public function down(): void
    {
        if (! Schema::hasTable('purchase_stock_adjustments')) {
            return;
        }

        if (Schema::hasIndex('purchase_stock_adjustments', 'psa_product_warehouse_unique')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
                $table->unique(['product_id', 'warehouse_id'], 'psa_product_warehouse_unique');
            });

            return;
        }

        $this->dropAllForeignKeysOnPurchaseStockAdjustments();

        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id'], 'psa_product_warehouse_unique');
        });

        $this->restorePurchaseStockAdjustmentsForeignKeys();
    }

    private function dropAllForeignKeysOnPurchaseStockAdjustments(): void
    {
        $schema = Schema::getConnection()->getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$schema, 'purchase_stock_adjustments', 'FOREIGN KEY']
        );

        foreach ($rows as $row) {
            $name = (string) $row->CONSTRAINT_NAME;
            DB::statement('ALTER TABLE `purchase_stock_adjustments` DROP FOREIGN KEY `' . str_replace('`', '``', $name) . '`');
        }
    }

    private function restorePurchaseStockAdjustmentsForeignKeys(): void
    {
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            if (Schema::hasTable('companies') && Schema::hasColumn('purchase_stock_adjustments', 'company_id')) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (Schema::hasTable('purchase_inventory_adjustment') && Schema::hasColumn('purchase_stock_adjustments', 'inventory_id')) {
                $table->foreign('inventory_id')
                    ->references('id')
                    ->on('purchase_inventory_adjustment')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (Schema::hasTable('products') && Schema::hasColumn('purchase_stock_adjustments', 'product_id')) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (Schema::hasTable('purchase_stock_adjustment_reasons') && Schema::hasColumn('purchase_stock_adjustments', 'reason_id')) {
                $table->foreign('reason_id')
                    ->references('id')
                    ->on('purchase_stock_adjustment_reasons')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }

            if (Schema::hasTable('warehouses') && Schema::hasColumn('purchase_stock_adjustments', 'warehouse_id')) {
                $table->foreign('warehouse_id')
                    ->references('id')
                    ->on('warehouses')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            }
        });
    }
};
