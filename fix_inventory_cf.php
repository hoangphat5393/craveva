<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Field Mapping based on previous debug output
$fieldMapping = [
    'ending_inventory' => 200,   // ending-inventory-1
    'specification' => 182,      // specification
    'manufacturing_date' => 204, // manufacturing_date
    'expiration_date' => 203,    // expiration_date
];

echo "Starting Custom Field Migration for Inventory...\n";

// Get all inventories
$inventories = PurchaseInventory::with('stocks')->get();
$count = 0;
$updated = 0;

foreach ($inventories as $inventory) {
    $count++;
    
    // Get the primary stock adjustment (usually the first one or the one matching inventory_id)
    $stock = $inventory->stocks->first();
    
    if (!$stock) {
        continue;
    }
    
    $dataToUpdate = [];
    
    // 1. Ending Inventory (from net_quantity)
    if (!is_null($stock->net_quantity)) {
        // Only update if not already present? Or overwrite? 
        // Let's overwrite to ensure consistency with core data.
        $dataToUpdate['field_' . $fieldMapping['ending_inventory']] = $stock->net_quantity;
    }
    
    // 2. Manufacturing Date
    if (!empty($stock->manufacturing_date)) {
        // Format date according to company settings if possible, but raw Y-m-d is usually safer for storage if that's what CF expects?
        // CF Trait usually expects Y-m-d or company format. 
        // Let's use the raw date from DB which is Y-m-d.
        $dataToUpdate['field_' . $fieldMapping['manufacturing_date']] = $stock->manufacturing_date; // stored as Y-m-d in DB
    }
    
    // 3. Expiration Date
    if (!empty($stock->expiration_date)) {
        $dataToUpdate['field_' . $fieldMapping['expiration_date']] = $stock->expiration_date;
    }
    
    // 4. Specification (from description)
    if (!empty($stock->description)) {
        $dataToUpdate['field_' . $fieldMapping['specification']] = $stock->description;
    }
    
    if (!empty($dataToUpdate)) {
        // We use the updateCustomFieldData method if available, or insert directly into DB
        // Using trait method is safer but slower. Let's try direct DB insert/update for speed and to avoid Trait issues if any.
        
        foreach ($dataToUpdate as $fieldKey => $value) {
            $fieldId = str_replace('field_', '', $fieldKey);
            
            // Check if exists
            $exists = DB::table('custom_fields_data')
                ->where('model', 'Modules\Purchase\Entities\PurchaseInventory')
                ->where('model_id', $inventory->id)
                ->where('custom_field_id', $fieldId)
                ->exists();
                
            if ($exists) {
                DB::table('custom_fields_data')
                    ->where('model', 'Modules\Purchase\Entities\PurchaseInventory')
                    ->where('model_id', $inventory->id)
                    ->where('custom_field_id', $fieldId)
                    ->update(['value' => $value]);
            } else {
                DB::table('custom_fields_data')->insert([
                    'model' => 'Modules\Purchase\Entities\PurchaseInventory',
                    'model_id' => $inventory->id,
                    'custom_field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        }
        $updated++;
        if ($updated % 50 == 0) echo "Updated $updated records...\n";
    }
}

echo "Done! Processed $count inventories. Updated $updated records with missing CF data.\n";
