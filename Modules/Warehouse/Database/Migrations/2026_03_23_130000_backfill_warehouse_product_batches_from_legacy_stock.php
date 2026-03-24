<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-time: seed batch-level rows from legacy warehouse_product_stock so enabling
     * StockMovementService does not reset totals (legacy was updated without batch rows).
     */
    public function up(): void
    {
        if (! Schema::hasTable('warehouse_product_stock') || ! Schema::hasTable('warehouse_product_batches')) {
            return;
        }

        $stocks = DB::table('warehouse_product_stock')
            ->where('quantity', '>', 0)
            ->get(['id', 'warehouse_id', 'product_id', 'quantity']);

        foreach ($stocks as $row) {
            $exists = DB::table('warehouse_product_batches')
                ->where('warehouse_id', $row->warehouse_id)
                ->where('product_id', $row->product_id)
                ->whereNull('batch_number')
                ->whereNull('expiration_date')
                ->exists();

            if ($exists) {
                continue;
            }

            $companyId = DB::table('warehouses')->where('id', $row->warehouse_id)->value('company_id');

            DB::table('warehouse_product_batches')->insert([
                'company_id' => $companyId,
                'warehouse_id' => $row->warehouse_id,
                'product_id' => $row->product_id,
                'batch_number' => null,
                'expiration_date' => null,
                'manufacturing_date' => null,
                'quantity' => $row->quantity,
                'reserved_quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive: do not delete seeded batch rows
    }
};
