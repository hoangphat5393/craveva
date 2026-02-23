<?php

namespace Modules\Purchase\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Traits\ExcelImportable;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Exception;

class ImportInventoryJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExcelImportable;

    private $row;
    private $columns;
    private $company;

    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle()
    {
        if ($this->company) {
            company($this->company);
        }

        $date = null;
        if ($this->isColumnExists('date')) {
            $dateValue = $this->getColumnValue('date');
            try {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->format('Y-m-d');
            } catch (\Throwable $e) {
                try {
                    $date = Carbon::parse($dateValue)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $date = Carbon::now()->format('Y-m-d');
                }
            }
        } else {
            $date = Carbon::now()->format('Y-m-d');
        }

        $product = null;
        $productName = null;
        $sku = null;

        if ($this->isColumnExists('sku')) {
            $sku = $this->getColumnValue('sku');
            if (!empty($sku)) {
                $product = PurchaseProduct::where('sku', $sku)->first();
            }
        }

        if (!$product && $this->isColumnExists('product_name')) {
            $productName = $this->getColumnValue('product_name');

            if (!empty($productName)) {
                $product = PurchaseProduct::where('name', $productName)->first();

                if (!$product) {
                    $product = new PurchaseProduct();
                    $product->name = $productName;
                    $product->sku = $sku;
                    $product->price = 0;
                    $product->purchase_price = 0;
                    $product->track_inventory = 1;
                    $product->company_id = $this->company->id;

                    // Set Unit ID if provided or default
                    if ($this->isColumnExists('unit')) {
                        $unitName = $this->getColumnValue('unit');
                        if (!empty($unitName)) {
                            $unit = \App\Models\UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                            if (!$unit) {
                                $unit = new \App\Models\UnitType();
                                $unit->company_id = $this->company->id;
                                $unit->unit_type = $unitName;
                                $unit->default = 0;
                                $unit->save();
                            }
                            $product->unit_id = $unit->id;
                        }
                    }

                    if (!$product->unit_id) {
                        $defaultUnit = \App\Models\UnitType::where('company_id', $this->company->id)->where('default', 1)->first();
                        if (!$defaultUnit) {
                            $defaultUnit = \App\Models\UnitType::where('company_id', $this->company->id)->first();
                        }

                        // If no unit exists in the system, create a default one
                        if (!$defaultUnit) {
                            $defaultUnit = new \App\Models\UnitType();
                            $defaultUnit->company_id = $this->company->id;
                            $defaultUnit->unit_type = 'pc';
                            $defaultUnit->default = 1;
                            $defaultUnit->save();
                        }

                        if ($defaultUnit) {
                            $product->unit_id = $defaultUnit->id;
                        }
                    }

                    $product->save();
                }
            }
        }

        if ($product) {

            // Enable inventory tracking if disabled
            if ($product->track_inventory != 1) {
                $product->track_inventory = 1;
                $product->save();
            }

            // Update Product Unit if missing (for existing products)
            if (!$product->unit_id && $this->isColumnExists('unit')) {
                $unitName = $this->getColumnValue('unit');
                if (!empty($unitName)) {
                    $unit = \App\Models\UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                    if (!$unit) {
                        // Optional: Create unit if not exists? For now, just skip or only match existing.
                        // Let's create it if it doesn't exist to be helpful, as per "fill missing fields" intent.
                        $unit = new \App\Models\UnitType();
                        $unit->company_id = $this->company->id;
                        $unit->unit_type = $unitName;
                        $unit->default = 0;
                        $unit->save();
                    }
                    $product->unit_id = $unit->id;
                    $product->save();
                }
            }

            // Update Product Description if missing (using specification)
            if (empty($product->description) && $this->isColumnExists('specification')) {
                $specification = $this->getColumnValue('specification');
                if (!empty($specification)) {
                    $product->description = $specification;
                    $product->save();
                }
            }

            // Find Warehouse - Logic Removed as per user request (unstable feature)
            // Warehouse fields now handled via Custom Fields (warehouse_code, warehouse_name)
            $warehouseId = null;

            DB::beginTransaction();
            try {
                $type = 'quantity';
                if ($this->isColumnExists('type')) {
                    $typeValue = $this->getColumnValue('type');
                    $type = !empty($typeValue) ? strtolower($typeValue) : 'quantity';
                }

                $inventory = new PurchaseInventory();
                $inventory->date = $date;
                $inventory->type = $type;
                $inventory->added_by = user() ? user()->id : null;
                $inventory->save();

                $addStock = new PurchaseStockAdjustment();
                $addStock->type = $type;
                $addStock->date = $date;
                $addStock->inventory_id = $inventory->id;
                $addStock->product_id = $product->id;
                $addStock->warehouse_id = $warehouseId;
                $addStock->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;

                // Add Date Fields
                if ($this->isColumnExists('manufacturing_date')) {
                    $mDateValue = $this->getColumnValue('manufacturing_date');
                    if (!empty($mDateValue)) {
                        try {
                            $addStock->manufacturing_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($mDateValue))->format('Y-m-d');
                        } catch (\Throwable $e) {
                            try {
                                $addStock->manufacturing_date = Carbon::parse($mDateValue)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                // ignore invalid date
                            }
                        }
                    }
                }

                if ($this->isColumnExists('expiration_date')) {
                    $eDateValue = $this->getColumnValue('expiration_date');
                    if (!empty($eDateValue)) {
                        try {
                            $addStock->expiration_date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($eDateValue))->format('Y-m-d');
                        } catch (\Throwable $e) {
                            try {
                                $addStock->expiration_date = Carbon::parse($eDateValue)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                // ignore invalid date
                            }
                        }
                    }
                }

                $addStock->status = 'converted';

                if ($type == 'quantity') {
                    // Prioritize Ending Inventory if provided, otherwise fallback to Quantity
                    if ($this->isColumnExists('ending_inventory') && !is_null($this->getColumnValue('ending_inventory'))) {
                        $quantity = $this->getColumnValue('ending_inventory');
                    } else {
                        $quantity = $this->isColumnExists('quantity') ? $this->getColumnValue('quantity') : 0;
                    }

                    $addStock->net_quantity = $quantity;
                    $addStock->quantity_adjustment = $quantity;
                } else {
                    $costPrice = $this->isColumnExists('cost_price') ? $this->getColumnValue('cost_price') : 0;
                    $addStock->changed_value = $costPrice;
                    $addStock->adjusted_value = $costPrice;
                }

                $addStock->save();

                // Save Custom Fields
                $customFields = \App\Models\CustomFieldGroup::where('model', PurchaseInventory::CUSTOM_FIELD_MODEL)
                    ->where('company_id', $this->company->id)
                    ->with('customField')
                    ->first();

                if ($customFields) {
                    $customFieldsData = [];
                    foreach ($customFields->customField as $customField) {
                        $value = null;

                        if ($this->isColumnExists('field_' . $customField->id)) {
                            $value = $this->getColumnValue('field_' . $customField->id);
                        }

                        // Map hardcoded system fields to custom fields if they match by label
                        if (empty($value)) {
                            $label = __($customField->label);
                            if ($label == __('purchase::modules.inventory.endingInventory') && $this->isColumnExists('ending_inventory')) {
                                $value = $this->getColumnValue('ending_inventory');
                            } elseif ($label == __('purchase::modules.inventory.specification') && $this->isColumnExists('specification')) {
                                $value = $this->getColumnValue('specification');
                            } elseif ($label == __('purchase::modules.inventory.manufacturingDate') && $this->isColumnExists('manufacturing_date')) {
                                $value = $this->getColumnValue('manufacturing_date');
                            } elseif ($label == __('purchase::modules.inventory.expirationDate') && $this->isColumnExists('expiration_date')) {
                                $value = $this->getColumnValue('expiration_date');
                            }
                        }

                        if (!is_null($value)) {
                            if ($customField->type == 'date' && !empty($value)) {
                                try {
                                    $dateObj = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
                                } catch (\Throwable $e) {
                                    try {
                                        $dateObj = Carbon::parse($value);
                                    } catch (\Throwable $e) {
                                        $dateObj = Carbon::now();
                                    }
                                }
                                // Format to match company setting so updateCustomFieldData trait doesn't crash
                                $value = $dateObj->format(companyOrGlobalSetting()->date_format);
                            }

                            $customFieldsData['field_' . $customField->id] = $value;
                        }
                    }
                    if (!empty($customFieldsData)) {
                        $inventory->updateCustomFieldData($customFieldsData, $this->company->id);
                    }
                }
                if ($type == 'value' && !is_null($addStock->changed_value) && $product) {
                    $product->price = $addStock->changed_value;
                    $product->save();
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->failJob($e->getMessage());
            }
        } else {
            // Skip empty rows or rows without product identifier (prevent footer errors)
            $sku = $this->isColumnExists('sku') ? $this->getColumnValue('sku') : null;
            $productName = $this->isColumnExists('product_name') ? $this->getColumnValue('product_name') : null;

            if (empty($sku) && empty($productName)) {
                return;
            }

            $this->failJob(__('messages.invalidData') . ': Product not found. Row: ');
        }
    }
}
