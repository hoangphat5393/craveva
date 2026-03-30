<?php

namespace App\Jobs;

use App\Models\Product;
use App\Traits\EmployeeActivityTrait;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportProductJob implements ShouldQueue
{
    use Batchable, Dispatchable, EmployeeActivityTrait, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;

    private $columns;

    private $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->company) {
            company($this->company);
        }

        if (! $this->isColumnExists('sku')) {
            return;
        }
        $skuRaw = $this->getColumnValue('sku');
        if ($skuRaw === null || trim((string) $skuRaw) === '') {
            return;
        }

        if ($this->isColumnExists('product_name')) {

            $cleanedPrice = $this->resolveImportPriceForRow();

            if ($cleanedPrice === null) {
                $this->failJob(__('messages.importProductPriceNotNumeric'));

                return;
            }

            DB::beginTransaction();
            try {
                $product = new Product;
                $product->company_id = $this->company?->id;
                $product->name = $this->getColumnValue('product_name');

                $product->price = (float) $cleanedPrice;

                $product->description = $this->isColumnExists('description') ? $this->getColumnValue('description') : null;
                $product->sku = $this->isColumnExists('sku') ? $this->getColumnValue('sku') : null;

                // New fields
                $product->storage_condition = $this->isColumnExists('storage_condition') ? $this->getColumnValue('storage_condition') : null;
                $product->certification = $this->isColumnExists('certification') ? $this->getColumnValue('certification') : null;
                $product->wholesale_price = $this->isColumnExists('wholesale_price') ? $this->getColumnValue('wholesale_price') : null;
                $product->price_per_box = $this->isColumnExists('price_per_box') ? $this->getColumnValue('price_per_box') : null;
                $product->employee_price = $this->isColumnExists('employee_price') ? $this->getColumnValue('employee_price') : null;

                if ($this->isColumnExists('track_inventory')) {
                    $trackInventory = strtolower($this->getColumnValue('track_inventory'));
                    $product->track_inventory = ($trackInventory == 'yes' || $trackInventory == '1' || $trackInventory == 'true') ? 1 : 0;
                }

                $product->inventory_type = $this->isColumnExists('inventory_type') ? $this->getColumnValue('inventory_type') : null;

                if ($this->isColumnExists('status')) {
                    $rawStatus = $this->getColumnValue('status');
                    if ($rawStatus === null || trim((string) $rawStatus) === '') {
                        $product->status = 'active';
                    } else {
                        $status = strtolower(trim((string) $rawStatus));
                        $product->status = ($status === 'active') ? 'active' : 'inactive';
                    }
                } else {
                    $product->status = 'active';
                }

                if ($this->isColumnExists('allow_purchase')) {
                    $rawAllow = $this->getColumnValue('allow_purchase');
                    if ($rawAllow === null || trim((string) $rawAllow) === '') {
                        $product->allow_purchase = true;
                    } else {
                        $v = strtolower(trim((string) $rawAllow));
                        $product->allow_purchase = in_array($v, ['yes', '1', 'true', 'y'], true);
                    }
                } else {
                    $product->allow_purchase = true;
                }

                // Unit type: có cột thì tra theo tên; không có hoặc trống thì dùng unit đầu tiên (theo id), chỉ query 1 lần
                $product->unit_id = $this->resolveUnitIdForRow();

                // Check if category and sub category exists
                if ($this->isColumnExists('product_category')) {
                    $categoryName = $this->getColumnValue('product_category');
                    $category = DB::table('product_category')->where('category_name', $categoryName)->first();
                    $product->category_id = $category ? $category->id : null;
                } else {
                    $product->category_id = null;
                }

                if ($this->isColumnExists('product_sub_category')) {
                    $subCategoryName = $this->getColumnValue('product_sub_category');
                    $subCategory = DB::table('product_sub_category')->where('category_name', $subCategoryName)->first();

                    if ($subCategory) {
                        // Check if the sub-category's parent category matches the selected category
                        if ($subCategory->category_id == $product->category_id) {
                            $product->sub_category_id = $subCategory->id;
                        } else {
                            // Handle the mismatch case, e.g., set to null or throw an exception
                            $product->sub_category_id = null;
                        }
                    } else {
                        $product->sub_category_id = null;
                    }
                } else {
                    $product->sub_category_id = null;
                }

                $product->added_by = user() ? user()->id : null;

                $product->save();

                // Create activity
                if (user()) {
                    self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');
                }
                DB::commit();
            } catch (InvalidFormatException $e) {
                DB::rollBack();
                $this->failJob(__('messages.invalidData'));
            } catch (Exception $e) {
                DB::rollBack();
                $this->failJobWithMessage($e->getMessage());
            }
        } else {
            $this->failJob(__('messages.importProductMissingProductName'));
        }
    }

    /**
     * @return string|null numeric string for price, or null if value is non-numeric garbage
     */
    private function resolveImportPriceForRow(): ?string
    {
        $hasStandard = $this->isColumnExists('standard_price');
        $hasPrice = $this->isColumnExists('price');

        if (! $hasStandard && ! $hasPrice) {
            return '0';
        }

        $priceVal = $hasStandard ? $this->getColumnValue('standard_price') : $this->getColumnValue('price');
        $cleaned = preg_replace('/[^\d.]/', '', (string) $priceVal);

        if ($cleaned !== '' && is_numeric($cleaned)) {
            return $cleaned;
        }

        if ($priceVal === null || trim((string) $priceVal) === '') {
            return '0';
        }

        return null;
    }

    /** Cache unit_id của unit type đầu tiên (theo id), chỉ query 1 lần. */
    private static $firstUnitIdCache = null;

    /** Unit type: có cột thì tra theo tên; không có hoặc trống/không tìm thấy thì dùng unit đầu tiên (theo id). */
    private function resolveUnitIdForRow(): ?int
    {
        if (! $this->isColumnExists('unit_type')) {
            return $this->getFirstUnitId();
        }

        $name = trim((string) $this->getColumnValue('unit_type'));
        if ($name === '') {
            return $this->getFirstUnitId();
        }

        $unitType = DB::table('unit_types')->where('unit_type', $name)->first();

        return $unitType ? $unitType->id : $this->getFirstUnitId();
    }

    private function getFirstUnitId(): ?int
    {
        if (self::$firstUnitIdCache === null) {
            self::$firstUnitIdCache = DB::table('unit_types')->orderBy('id')->value('id');
        }

        return self::$firstUnitIdCache;
    }
}
