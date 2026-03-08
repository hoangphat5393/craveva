<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfNotExists('custom_fields_data', 'cfd_model_modelid_fieldid_idx', 'CREATE INDEX cfd_model_modelid_fieldid_idx ON custom_fields_data (model(100), model_id, custom_field_id)');
        $this->addIndexIfNotExists('purchase_inventory_adjustment', 'pia_company_created_idx', 'CREATE INDEX pia_company_created_idx ON purchase_inventory_adjustment (company_id, created_at)');
        $this->addIndexIfNotExists('purchase_stock_adjustments', 'psa_inventory_product_idx', 'CREATE INDEX psa_inventory_product_idx ON purchase_stock_adjustments (inventory_id, product_id)');
    }

    public function down(): void
    {
        Schema::table('custom_fields_data', function (Blueprint $table) {
            $table->dropIndex('cfd_model_modelid_fieldid_idx');
        });
        Schema::table('purchase_inventory_adjustment', function (Blueprint $table) {
            $table->dropIndex('pia_company_created_idx');
        });
        Schema::table('purchase_stock_adjustments', function (Blueprint $table) {
            $table->dropIndex('psa_inventory_product_idx');
        });
    }

    private function addIndexIfNotExists(string $table, string $indexName, string $sql): void
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        if (empty($indexes)) {
            DB::statement($sql);
        }
    }
};
