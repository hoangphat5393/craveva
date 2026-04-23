<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'wpb_company_wh_product_batch_exp_unique';

    public function up(): void
    {
        if (! Schema::hasTable('warehouse_product_batches')) {
            return;
        }

        $duplicates = DB::table('warehouse_product_batches')
            ->selectRaw('company_id, warehouse_id, product_id, batch_number, expiration_date, COUNT(*) as duplicate_count')
            ->groupBy('company_id', 'warehouse_id', 'product_id', 'batch_number', 'expiration_date')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicates > 0) {
            throw new RuntimeException(
                'Duplicate warehouse batch identity rows found. Run `php artisan warehouse:batch-dedupe --apply` before this migration.'
            );
        }

        Schema::table('warehouse_product_batches', function (Blueprint $table) {
            $table->unique(
                ['company_id', 'warehouse_id', 'product_id', 'batch_number', 'expiration_date'],
                $this->indexName
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouse_product_batches')) {
            return;
        }

        Schema::table('warehouse_product_batches', function (Blueprint $table) {
            $table->dropUnique($this->indexName);
        });
    }
};
