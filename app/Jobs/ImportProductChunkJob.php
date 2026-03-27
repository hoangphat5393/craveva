<?php

namespace App\Jobs;

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\Models\Product;
use App\Models\UnitType;
use App\Traits\EmployeeActivityTrait;
use App\Traits\StoresImportBatchMetrics;
use App\Traits\UniversalSearchTrait;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Processes a chunk of product import rows in one job to reduce queue overhead and DB lookups.
 * Use with ImportExcel::importJobProcessChunked() for faster bulk import (e.g. 1000 products).
 */
class ImportProductChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, EmployeeActivityTrait, InteractsWithQueue, Queueable, SerializesModels, StoresImportBatchMetrics, UniversalSearchTrait;

    /** @var array<int, array> */
    private array $rows;

    private array $columns;

    private $company;

    private int $chunkStartIndex;

    /** User-selected default unit id (from import UI). Khi có giá trị thì dùng luôn, không query. */
    private ?int $defaultUnitId = null;

    /** Cache for unit_type name => id within this chunk. */
    private array $unitTypeCache = [];

    /** Cache for category name => id. */
    private array $categoryCache = [];

    /** Cache for sub_category "categoryId|name" => id. */
    private array $subCategoryCache = [];

    /** Cached fallback unit type id: khi không có unit_type và không có defaultUnitId thì dùng unit đầu tiên, chỉ query 1 lần/chunk. */
    private $fallbackUnitId = null;

    /** Cache SKU đã tồn tại trong DB (và trong chunk) để tránh N query exists() – key = sku, O(1) lookup. */
    private array $existingSkus = [];

    public function __construct(array $rows, array $columns, $company = null, int $chunkStartIndex = 0, array $options = [])
    {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->company = $company;
        $this->chunkStartIndex = $chunkStartIndex;
        $this->defaultUnitId = isset($options['default_unit_id']) && $options['default_unit_id'] ? (int) $options['default_unit_id'] : null;
    }

    public function handle(): void
    {
        if ($this->company) {
            company($this->company);
        }

        // Cache SKU đã tồn tại trong DB (1 query/chunk) để tránh exists() từng dòng.
        if ($this->company) {
            $this->existingSkus = Product::where('company_id', $this->company->id)
                ->whereNotNull('sku')
                ->pluck('sku')
                ->flip()
                ->all();
        }

        $failures = [];
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($this->rows as $index => $row) {
            try {
                $normalizedRow = $this->normalizeRow($row);
                $created = DB::transaction(function () use ($normalizedRow, $index) {
                    return $this->processRow($normalizedRow, $this->chunkStartIndex + $index);
                });
                if (! $created) {
                    $skippedCount++;
                    continue;
                }
                $createdCount++;
            } catch (Exception $e) {
                $fileRow = $this->chunkStartIndex + $index + 2; // +2: 1-based and header row
                $failures[] = 'Row ' . $fileRow . ': ' . $e->getMessage();
            }
        }

        $this->storeBatchMetrics([
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'skipped_missing_required' => 0,
            'invalid_status' => 0,
        ]);

        if ($failures !== []) {
            $msg = implode("\n", array_slice($failures, 0, 50));
            if (count($failures) > 50) {
                $msg .= "\n… and " . (count($failures) - 50) . ' more';
            }
            $this->fail($msg);
        }
    }

    /**
     * @return bool true if product was created, false if row skipped (duplicate SKU)
     */
    private function processRow(array $row, int $index): bool
    {
        if (! $this->columnExists('product_name')) {
            throw new Exception(__('messages.invalidData'));
        }
        $priceVal = $this->columnExists('standard_price') ? $this->getValue($row, 'standard_price') : $this->getValue($row, 'price');
        if (! $this->columnExists('price') && ! $this->columnExists('standard_price')) {
            throw new Exception(__('messages.invalidData'));
        }
        $cleanedPrice = is_scalar($priceVal) ? preg_replace('/[^\d.]/', '', (string) $priceVal) : '';
        if (! is_numeric($cleanedPrice)) {
            throw new Exception(__('messages.invalidData'));
        }

        $sku = $this->columnExists('sku') ? $this->getValue($row, 'sku') : null;
        $skuTrimmed = $sku !== null && $sku !== '' ? trim((string) $sku) : null;
        if ($skuTrimmed !== null && isset($this->existingSkus[$skuTrimmed])) {
            return false;
        }

        $product = new Product;
        $product->company_id = $this->company?->id;
        $product->name = $this->getValue($row, 'product_name');
        $product->price = (float) $cleanedPrice;
        $product->description = $this->columnExists('description') ? $this->getValue($row, 'description') : null;
        $product->specification = $this->columnExists('specification') ? $this->getValue($row, 'specification') : null;
        $product->product_source = $this->columnExists('product_source') ? $this->getValue($row, 'product_source') : null;
        $product->brand = $this->columnExists('brand') ? $this->getValue($row, 'brand') : null;
        $product->product_grade = $this->columnExists('product_grade') ? $this->getValue($row, 'product_grade') : null;
        $product->sku = $skuTrimmed ?? $sku;
        $product->storage_condition = $this->columnExists('storage_condition') ? $this->getValue($row, 'storage_condition') : null;
        $product->certification = $this->columnExists('certification') ? $this->getValue($row, 'certification') : null;
        $product->wholesale_price = $this->columnExists('wholesale_price') ? $this->getValue($row, 'wholesale_price') : null;
        $product->price_per_box = $this->columnExists('price_per_box') ? $this->getValue($row, 'price_per_box') : null;
        $product->employee_price = $this->columnExists('employee_price') ? $this->getValue($row, 'employee_price') : null;

        if ($this->columnExists('shelf_life_days')) {
            $v = $this->getValue($row, 'shelf_life_days');
            $product->shelf_life_days = ($v !== null && $v !== '' && is_numeric(trim((string) $v))) ? (int) trim((string) $v) : null;
        }

        if ($this->columnExists('track_inventory')) {
            $v = strtolower((string) $this->getValue($row, 'track_inventory'));
            $product->track_inventory = ($v === 'yes' || $v === '1' || $v === 'true') ? 1 : 0;
        }

        $product->inventory_type = $this->columnExists('inventory_type') ? $this->getValue($row, 'inventory_type') : null;

        if ($this->columnExists('status')) {
            $status = strtolower((string) $this->getValue($row, 'status'));
            $product->status = ($status === 'active') ? 'active' : 'inactive';
        }

        $product->allow_purchase = true;

        $product->unit_id = $this->resolveUnitId($row);
        $product->category_id = $this->resolveCategoryId($row);
        $product->sub_category_id = $this->resolveSubCategoryId($row, $product->category_id);
        $product->added_by = user() ? user()->id : null;

        $product->save();

        if ($skuTrimmed !== null) {
            $this->existingSkus[$skuTrimmed] = true;
        }

        $customFieldsData = $this->buildProductCustomFieldsData($row);
        if ($customFieldsData !== []) {
            $product->updateCustomFieldData($customFieldsData);
        }

        if (user()) {
            self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');
        }

        return true;
    }

    /**
     * Build custom_fields_data array for Product from row. Only for custom fields still in use;
     * product_grade, product_source, brand are now DB columns, not custom fields.
     *
     * @return array<string, mixed> keys like 'field_123' => value
     */
    private function buildProductCustomFieldsData(array $row): array
    {
        $customFieldNames = [];
        $companyId = $this->company?->id;
        if (! $companyId) {
            return [];
        }

        $group = CustomFieldGroup::where('company_id', $companyId)->where('model', Product::class)->first();
        if (! $group) {
            return [];
        }

        $fields = CustomField::where('custom_field_group_id', $group->id)->whereIn('name', $customFieldNames)->get();
        if ($fields->isEmpty()) {
            return [];
        }

        $data = [];
        foreach ($fields as $field) {
            if (! $this->columnExists($field->name)) {
                continue;
            }
            $value = $this->getValue($row, $field->name);
            if ($value !== null && $value !== '') {
                $data['field_' . $field->id] = is_scalar($value) ? trim((string) $value) : (string) $value;
            }
        }

        return $data;
    }

    private function getValue(array $row, string $fieldId)
    {
        $keys = array_keys($this->columns, $fieldId);

        return ! empty($keys) ? ($row[$keys[0]] ?? null) : null;
    }

    private function columnExists(string $fieldId): bool
    {
        return in_array($fieldId, $this->columns, true);
    }

    private function resolveUnitId(array $row): ?int
    {
        $getFallbackOnce = function (): ?int {
            if ($this->fallbackUnitId === null) {
                $query = DB::table('unit_types')->orderBy('id');
                if ($this->company) {
                    $query->where(function ($q) {
                        $q->where('company_id', $this->company->id)->orWhereNull('company_id');
                    });
                }
                $this->fallbackUnitId = $query->value('id');
            }

            return $this->fallbackUnitId;
        };

        if (! $this->columnExists('unit_type')) {
            return $this->defaultUnitId ?? $getFallbackOnce();
        }

        $name = trim((string) $this->getValue($row, 'unit_type'));
        if ($name === '') {
            return $this->defaultUnitId ?? $getFallbackOnce();
        }

        if (! array_key_exists($name, $this->unitTypeCache)) {
            $ut = $this->findUnitTypeByName($name);
            if ($ut !== null) {
                $this->unitTypeCache[$name] = $ut->id;
            } else {
                $newUnit = $this->createUnitType($name);
                $this->unitTypeCache[$name] = $newUnit?->id;
            }
        }

        $id = $this->unitTypeCache[$name] ?? null;
        if ($id !== null) {
            return $id;
        }

        return $this->defaultUnitId ?? $getFallbackOnce();
    }

    /**
     * Find unit type by name (company-scoped or global).
     */
    private function findUnitTypeByName(string $name): ?object
    {
        $query = DB::table('unit_types')->where('unit_type', $name);
        if ($this->company) {
            $query->where(function ($q) {
                $q->where('company_id', $this->company->id)->orWhereNull('company_id');
            })->orderByRaw('company_id IS NOT NULL DESC');
        }

        return $query->first();
    }

    /**
     * Create new unit type when not found in DB (import auto-create).
     */
    private function createUnitType(string $name): ?UnitType
    {
        $companyId = $this->company?->id;
        $existsQuery = UnitType::where('unit_type', $name);
        if ($companyId === null) {
            $existsQuery->whereNull('company_id');
        } else {
            $existsQuery->where('company_id', $companyId);
        }
        $existing = $existsQuery->first();
        if ($existing !== null) {
            return $existing;
        }

        return UnitType::create([
            'unit_type' => $name,
            'company_id' => $companyId,
            'default' => 0,
        ]);
    }

    private function resolveCategoryId(array $row): ?int
    {
        if (! $this->columnExists('product_category')) {
            return null;
        }

        $name = trim((string) $this->getValue($row, 'product_category'));
        if ($name === '') {
            return null;
        }

        if (! array_key_exists($name, $this->categoryCache)) {
            $cat = DB::table('product_category')->where('category_name', $name)->first();
            $this->categoryCache[$name] = $cat ? $cat->id : null;
        }

        return $this->categoryCache[$name];
    }

    private function resolveSubCategoryId(array $row, ?int $categoryId): ?int
    {
        if (! $this->columnExists('product_sub_category')) {
            return null;
        }

        $name = trim((string) $this->getValue($row, 'product_sub_category'));
        if ($name === '' || $categoryId === null) {
            return null;
        }

        $cacheKey = $categoryId . '|' . $name;
        if (! array_key_exists($cacheKey, $this->subCategoryCache)) {
            $sub = DB::table('product_sub_category')
                ->where('category_name', $name)
                ->where('category_id', $categoryId)
                ->first();
            $this->subCategoryCache[$cacheKey] = $sub ? $sub->id : null;
        }

        return $this->subCategoryCache[$cacheKey];
    }

    private function normalizeRow(array $row): array
    {
        $result = [];
        foreach ($row as $key => $value) {
            if ($value === null || is_scalar($value)) {
                $result[$key] = $value;
            } else {
                $result[$key] = $this->cellToScalar($value);
            }
        }

        return $result;
    }

    private function cellToScalar($value)
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

    private function storeBatchMetrics(array $delta): void
    {
        $this->mergeImportBatchMetrics($this->batchId, $delta);
    }
}
