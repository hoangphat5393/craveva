<?php

namespace Modules\Purchase\Jobs;

use App\Traits\ExcelImportable;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\StockMovementService;

class ImportInventoryJob implements ShouldQueue
{
    use Batchable, Dispatchable, ExcelImportable, InteractsWithQueue, Queueable, SerializesModels;

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
        if (! $this->company) {
            $this->failJobWithMessage(__('messages.invalidData') . ': Company context is required for import.');

            return;
        }
        company($this->company);

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
            $sku = $this->cleanImportString((string) $this->getColumnValue('sku'));
            if ($sku !== '') {
                $product = PurchaseProduct::where('company_id', $this->company->id)
                    ->where('sku', $sku)
                    ->first();
            }
        }

        if (! $product && $this->isColumnExists('product_name')) {
            $productName = $this->cleanImportString((string) $this->getColumnValue('product_name'));

            if (! empty($productName)) {
                $product = PurchaseProduct::where('company_id', $this->company->id)
                    ->where('name', $productName)
                    ->first();

                if (! $product) {
                    $product = new PurchaseProduct;
                    $product->name = $productName;
                    $product->sku = $sku;
                    $product->price = 0;
                    $product->purchase_price = 0;
                    $product->track_inventory = 1;
                    $product->company_id = $this->company->id;

                    // Set Unit ID if provided or default
                    if ($this->isColumnExists('unit')) {
                        $unitName = $this->getColumnValue('unit');
                        if (! empty($unitName)) {
                            $unit = \App\Models\UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                            if (! $unit) {
                                $unit = new \App\Models\UnitType;
                                $unit->company_id = $this->company->id;
                                $unit->unit_type = $unitName;
                                $unit->default = 0;
                                $unit->save();
                            }
                            $product->unit_id = $unit->id;
                        }
                    }

                    if (! $product->unit_id) {
                        $defaultUnit = \App\Models\UnitType::where('company_id', $this->company->id)->where('default', 1)->first();
                        if (! $defaultUnit) {
                            $defaultUnit = \App\Models\UnitType::where('company_id', $this->company->id)->first();
                        }

                        // If no unit exists in the system, create a default one
                        if (! $defaultUnit) {
                            $defaultUnit = new \App\Models\UnitType;
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
            if (! $product->unit_id && $this->isColumnExists('unit')) {
                $unitName = $this->getColumnValue('unit');
                if (! empty($unitName)) {
                    $unit = \App\Models\UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                    if (! $unit) {
                        // Optional: Create unit if not exists? For now, just skip or only match existing.
                        // Let's create it if it doesn't exist to be helpful, as per "fill missing fields" intent.
                        $unit = new \App\Models\UnitType;
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
                if (! empty($specification)) {
                    $product->description = $specification;
                    $product->save();
                }
            }

            $warehouseId = $this->resolveWarehouseId();

            DB::beginTransaction();
            try {
                $type = 'quantity';
                if ($this->isColumnExists('type')) {
                    $typeValue = $this->getColumnValue('type');
                    $type = ! empty($typeValue) ? strtolower($typeValue) : 'quantity';
                }

                $inventory = new PurchaseInventory;
                $inventory->date = $date;
                $inventory->type = $type;
                $inventory->warehouse_id = $warehouseId;
                $inventory->added_by = user() ? user()->id : null;
                $inventory->save();

                $addStock = new PurchaseStockAdjustment;
                $addStock->type = $type;
                $addStock->date = $date;
                $addStock->inventory_id = $inventory->id;
                $addStock->product_id = $product->id;
                $addStock->warehouse_id = $warehouseId;
                $addStock->batch_number = $this->isColumnExists('batch_number') ? $this->getColumnValue('batch_number') : null;
                $addStock->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;

                // Add Date Fields
                if ($this->isColumnExists('manufacturing_date')) {
                    $mDateValue = $this->getColumnValue('manufacturing_date');
                    if (! empty($mDateValue)) {
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
                    if (! empty($eDateValue)) {
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
                    if ($this->isColumnExists('ending_inventory') && $this->getColumnValue('ending_inventory') !== null && $this->getColumnValue('ending_inventory') !== '') {
                        $quantity = $this->parseImportNumber($this->getColumnValue('ending_inventory'));
                    } else {
                        $quantity = $this->isColumnExists('quantity')
                            ? $this->parseImportNumber($this->getColumnValue('quantity'))
                            : 0.0;
                    }

                    $addStock->net_quantity = $quantity;
                    $addStock->quantity_adjustment = $quantity;
                    if (Schema::hasColumn('purchase_stock_adjustments', 'reserved_quantity')) {
                        $addStock->reserved_quantity = $this->isColumnExists('reserved_quantity')
                            ? $this->parseImportNumber($this->getColumnValue('reserved_quantity'))
                            : 0;
                    }
                } else {
                    $costPrice = $this->isColumnExists('cost_price')
                        ? $this->parseImportNumber($this->getColumnValue('cost_price'))
                        : 0.0;
                    $addStock->changed_value = $costPrice;
                    $addStock->adjusted_value = $costPrice;
                }

                $addStock->save();

                if ($type == 'quantity' && $warehouseId && class_exists(StockMovementService::class)) {
                    $currentWarehouseQty = (float) (WarehouseProductStock::where('warehouse_id', $warehouseId)
                        ->where('product_id', $product->id)
                        ->value('quantity') ?? 0);
                    $this->syncWarehouseStockFromAbsoluteQuantity(
                        app(StockMovementService::class),
                        $inventory,
                        (int) $warehouseId,
                        (int) $product->id,
                        $currentWarehouseQty,
                        (float) $quantity,
                        $addStock->expiration_date,
                        $addStock->manufacturing_date
                    );
                }

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

                        if (! is_null($value) && $value !== '') {
                            if ($customField->type == 'date' && ! empty($value)) {
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

                            // Skip date fields with empty value to prevent Carbon::createFromFormat crash
                            if ($customField->type !== 'date' || ! empty($value)) {
                                $customFieldsData['field_' . $customField->id] = $value;
                            }
                        }
                    }
                    if (! empty($customFieldsData)) {
                        $inventory->updateCustomFieldData($customFieldsData, $this->company->id);
                    }
                }
                if ($type == 'value' && ! is_null($addStock->changed_value) && $product) {
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

    /**
     * Strip UTF-8 BOM and trim (CSV/Excel exports from external ERPs).
     */
    protected function cleanImportString(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            $value = substr($value, 3);
        }

        return trim($value);
    }

    /**
     * Parse quantities/prices: supports "1,220" and "1 220,50" style thousands separators.
     */
    protected function parseImportNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = str_replace(["\xC2\xA0", ' '], '', (string) $value);
        $s = str_replace(',', '', $s);
        if ($s === '' || ! is_numeric($s)) {
            return 0.0;
        }

        return (float) $s;
    }

    protected function resolveWarehouseId(): ?int
    {
        if (! class_exists(\Modules\Warehouse\Entities\Warehouse::class) || ! Schema::hasTable('warehouses')) {
            return null;
        }

        $code = $this->isColumnExists('warehouse_code') ? trim((string) $this->getColumnValue('warehouse_code')) : '';
        $name = $this->isColumnExists('warehouse_name') ? trim((string) $this->getColumnValue('warehouse_name')) : '';

        $baseQuery = \Modules\Warehouse\Entities\Warehouse::query()
            ->where('company_id', $this->company->id);

        if ($code !== '') {
            $warehouse = (clone $baseQuery)->where('code', $code)->first();
            if ($warehouse) {
                return (int) $warehouse->id;
            }
        }

        if ($name !== '') {
            $warehouse = (clone $baseQuery)->where('name', $name)->first();
            if ($warehouse) {
                return (int) $warehouse->id;
            }
        }

        return null;
    }

    protected function syncWarehouseStockFromAbsoluteQuantity(
        StockMovementService $movementService,
        PurchaseInventory $inventory,
        int $warehouseId,
        int $productId,
        float $currentQuantity,
        float $targetQuantity,
        ?string $expiryDate = null,
        ?string $manufacturingDate = null
    ): void {
        $delta = round($targetQuantity - $currentQuantity, 6);
        if (abs($delta) < 0.000001) {
            return;
        }

        $payload = [
            'company_id' => $inventory->company_id,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'batch_number' => null,
            'expiry_date' => $expiryDate ?: null,
            'manufacturing_date' => $manufacturingDate ?: null,
            'reference_type' => PurchaseInventory::class,
            'reference_id' => $inventory->id,
        ];

        if ($delta > 0) {
            $movementService->recordInbound($payload + ['quantity' => $delta]);
        } else {
            $movementService->recordOutbound($payload + ['quantity' => abs($delta)]);
        }
    }
}
