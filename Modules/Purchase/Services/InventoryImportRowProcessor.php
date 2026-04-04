<?php

namespace Modules\Purchase\Services;

use App\Models\CustomFieldGroup;
use App\Models\UnitType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseProductStock;
use Modules\Warehouse\Services\StockMovementService;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use RuntimeException;

/**
 * Single-row inventory import logic shared by ImportInventoryJob and ImportInventoryChunkJob.
 */
final class InventoryImportRowProcessor
{
    /** @param  array<int|string, mixed>  $row */
    public function __construct(
        private array $row,
        private array $columns,
        private object $company
    ) {}

    /**
     * Convert row values to scalars for queue-safe import (PhpSpreadsheet cells, RichText).
     *
     * @param  array<int|string, mixed>  $row
     * @return array<int|string, mixed>
     */
    public static function normalizeRowForJob(array $row): array
    {
        $result = [];
        foreach ($row as $key => $value) {
            if ($value === null || is_scalar($value)) {
                $result[$key] = $value;
            } else {
                $result[$key] = self::cellToScalar($value);
            }
        }

        return $result;
    }

    private static function cellToScalar($value): ?string
    {
        try {
            if (is_object($value) && method_exists($value, 'getFormattedValue')) {
                $v = $value->getFormattedValue();

                return $v === null ? null : (string) $v;
            }
            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }

            return $value === null ? null : (string) $value;
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * @return bool true if one inventory line was written; false if row skipped (empty identifiers)
     *
     * @throws RuntimeException on business/DB errors
     */
    public function run(): bool
    {
        $date = null;
        if ($this->isColumnExists('date')) {
            $dateValue = $this->getColumnValue('date');
            try {
                $date = Carbon::instance(Date::excelToDateTimeObject($dateValue))->format('Y-m-d');
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

                    if ($this->isColumnExists('unit')) {
                        $unitName = $this->getColumnValue('unit');
                        if (! empty($unitName)) {
                            $unit = UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                            if (! $unit) {
                                $unit = new UnitType;
                                $unit->company_id = $this->company->id;
                                $unit->unit_type = $unitName;
                                $unit->default = 0;
                                $unit->save();
                            }
                            $product->unit_id = $unit->id;
                        }
                    }

                    if (! $product->unit_id) {
                        $defaultUnit = UnitType::where('company_id', $this->company->id)->where('default', 1)->first();
                        if (! $defaultUnit) {
                            $defaultUnit = UnitType::where('company_id', $this->company->id)->first();
                        }

                        if (! $defaultUnit) {
                            $defaultUnit = new UnitType;
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

        if (! $product) {
            $skuVal = $this->isColumnExists('sku') ? $this->getColumnValue('sku') : null;
            $nameVal = $this->isColumnExists('product_name') ? $this->getColumnValue('product_name') : null;

            if (empty($skuVal) && empty($nameVal)) {
                return false;
            }

            throw new RuntimeException(__('messages.invalidData') . ': Product not found. Row: ');
        }

        if ($product->track_inventory != 1) {
            $product->track_inventory = 1;
            $product->save();
        }

        if (! $product->unit_id && $this->isColumnExists('unit')) {
            $unitName = $this->getColumnValue('unit');
            if (! empty($unitName)) {
                $unit = UnitType::where('unit_type', $unitName)->where('company_id', $this->company->id)->first();
                if (! $unit) {
                    $unit = new UnitType;
                    $unit->company_id = $this->company->id;
                    $unit->unit_type = $unitName;
                    $unit->default = 0;
                    $unit->save();
                }
                $product->unit_id = $unit->id;
                $product->save();
            }
        }

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
                $type = ! empty($typeValue) ? strtolower((string) $typeValue) : 'quantity';
            }

            $inventory = new PurchaseInventory;
            $inventory->company_id = $this->company->id;
            $inventory->date = $date;
            $inventory->type = $type;
            $inventory->warehouse_id = $warehouseId;
            $inventory->added_by = user() ? user()->id : null;
            $inventory->save();

            $addStock = new PurchaseStockAdjustment;
            $addStock->company_id = $this->company->id;
            $addStock->type = $type;
            $addStock->date = $date;
            $addStock->inventory_id = $inventory->id;
            $addStock->product_id = $product->id;
            $addStock->warehouse_id = $warehouseId;
            $addStock->batch_number = $this->isColumnExists('batch_number') ? $this->getColumnValue('batch_number') : null;
            $addStock->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;

            if ($this->isColumnExists('manufacturing_date')) {
                $mDateValue = $this->getColumnValue('manufacturing_date');
                if (! empty($mDateValue)) {
                    try {
                        $addStock->manufacturing_date = Carbon::instance(Date::excelToDateTimeObject($mDateValue))->format('Y-m-d');
                    } catch (\Throwable $e) {
                        try {
                            $addStock->manufacturing_date = Carbon::parse($mDateValue)->format('Y-m-d');
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            if ($this->isColumnExists('expiration_date')) {
                $eDateValue = $this->getColumnValue('expiration_date');
                if (! empty($eDateValue)) {
                    try {
                        $addStock->expiration_date = Carbon::instance(Date::excelToDateTimeObject($eDateValue))->format('Y-m-d');
                    } catch (\Throwable $e) {
                        try {
                            $addStock->expiration_date = Carbon::parse($eDateValue)->format('Y-m-d');
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            $addStock->status = 'converted';

            $quantity = 0.0;
            if ($type == 'quantity') {
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

            $customFields = CustomFieldGroup::where('model', PurchaseInventory::CUSTOM_FIELD_MODEL)
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
                                $dateObj = Carbon::instance(Date::excelToDateTimeObject($value));
                            } catch (\Throwable $e) {
                                try {
                                    $dateObj = Carbon::parse($value);
                                } catch (\Throwable $e) {
                                    $dateObj = Carbon::now();
                                }
                            }
                            $value = $dateObj->format(companyOrGlobalSetting()->date_format);
                        }

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
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return true;
    }

    private function getColumnValue(string $column): mixed
    {
        return $this->isColumnExists($column) ? $this->row[array_keys($this->columns, $column)[0]] : null;
    }

    private function isColumnExists(string $column): bool
    {
        return ! empty(array_keys($this->columns, $column));
    }

    private function cleanImportString(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            $value = substr($value, 3);
        }

        return trim($value);
    }

    private function parseImportNumber($value): float
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

    private function resolveWarehouseId(): ?int
    {
        if (! class_exists(Warehouse::class) || ! Schema::hasTable('warehouses')) {
            return null;
        }

        $code = $this->isColumnExists('warehouse_code') ? trim((string) $this->getColumnValue('warehouse_code')) : '';
        $name = $this->isColumnExists('warehouse_name') ? trim((string) $this->getColumnValue('warehouse_name')) : '';

        $baseQuery = Warehouse::query()
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

    private function syncWarehouseStockFromAbsoluteQuantity(
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
            'company_id' => (int) $this->company->id,
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
