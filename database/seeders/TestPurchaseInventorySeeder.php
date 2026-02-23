<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Purchase\Entities\PurchaseStockAdjustmentReason;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Warehouse\Entities\Warehouse;
use Carbon\Carbon;

class TestPurchaseInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have prerequisites: Reason, Product, Warehouse
        $reason = PurchaseStockAdjustmentReason::first();
        if (!$reason) {
            $reason = PurchaseStockAdjustmentReason::create([
                'name' => 'Stock Correction',
                'company_id' => 1
            ]);
        }

        $product = PurchaseProduct::first();
        if (!$product) {
            $product = PurchaseProduct::create([
                'name' => 'Test Product',
                'price' => 100,
                'type' => 'goods',
                'company_id' => 1
            ]);
        }

        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'company_id' => 1,
                'status' => 'active'
            ]);
        }

        // 2. Create 3 PurchaseInventory records
        for ($i = 1; $i <= 3; $i++) {
            // Create a unique product for each iteration to avoid unique constraint violation on (product_id, warehouse_id)
            $iterProduct = PurchaseProduct::create([
                'name' => "Test Product $i",
                'price' => 100 * $i,
                'type' => 'goods',
                'company_id' => 1
            ]);

            $inventory = PurchaseInventory::create([
                'date' => Carbon::now()->subDays($i),
                'reason_id' => $reason->id,
                // 'description' => "Test Inventory Adjustment $i", // Column does not exist
                // 'status' => 'active', // Column does not exist
                'company_id' => 1,
            ]);

            // 3. Create associated PurchaseStockAdjustment records
            PurchaseStockAdjustment::create([
                'inventory_id' => $inventory->id,
                'product_id' => $iterProduct->id,
                'warehouse_id' => $warehouse->id,
                'reason_id' => $reason->id,
                'type' => 'quantity',
                'date' => Carbon::now()->subDays($i),
                'reference_number' => 'REF-' . $inventory->id,
                'net_quantity' => 10,
                'quantity_adjustment' => 5,
                'description' => "Adjustment for item $i",
                'status' => 'active',
                'changed_value' => 500, // 5 * 100
                'adjusted_value' => 1500,
                'company_id' => 1,
            ]);
        }
    }
}
