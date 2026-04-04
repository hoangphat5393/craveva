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

    /** SKU => product id trong company (preload + bổ sung khi tạo mới trong chunk). */
    private array $skuToId = [];

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

        if ($this->company) {
            $this->skuToId = Product::where('company_id', $this->company->id)
                ->whereNotNull('sku')
                ->where('sku', '!=', '')
                ->pluck('id', 'sku')
                ->all();
        } else {
            $this->skuToId = [];
        }

        $failures = [];
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($this->rows as $index => $row) {
            try {
                $normalizedRow = $this->normalizeRow($row);
                $result = DB::transaction(function () use ($normalizedRow, $index) {
                    return $this->processRow($normalizedRow, $this->chunkStartIndex + $index);
                });
                if ($result === 'skipped') {
                    $skippedCount++;
                } elseif ($result === 'updated') {
                    $updatedCount++;
                } elseif ($result === 'created') {
                    $createdCount++;
                }
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
     * @return string 'created'|'updated'|'skipped'
     */
    private function processRow(array $row, int $index): string
    {
        $companyId = $this->company?->id;

        if (! $this->columnExists('sku')) {
            return 'skipped';
        }

        $skuRaw = $this->getValue($row, 'sku');
        $skuTrimmed = ($skuRaw !== null && trim((string) $skuRaw) !== '') ? trim((string) $skuRaw) : '';

        if ($skuTrimmed === '') {
            return 'skipped';
        }

        $existingId = $this->skuToId[$skuTrimmed] ?? null;
        if ($existingId !== null) {
            $product = Product::find($existingId);
            if (
                ! $product
                || ($companyId !== null && (int) $product->company_id !== (int) $companyId)
            ) {
                $product = $this->findProductBySkuQuery($skuTrimmed)->first();
            }
            if ($product) {
                return $this->persistProductUpdate($product, $row, $skuTrimmed);
            }

            unset($this->skuToId[$skuTrimmed]);
        }

        $fallbackExisting = $this->findProductBySkuQuery($skuTrimmed)->first();
        if ($fallbackExisting) {
            return $this->persistProductUpdate($fallbackExisting, $row, $skuTrimmed);
        }

        if (! $this->columnExists('product_name')) {
            throw new Exception(__('messages.importProductMissingProductName'));
        }

        $product = new Product;
        $this->applyMappedFieldsToProduct($product, $row, true);
        $product->save();
        $this->skuToId[$skuTrimmed] = $product->id;
        $this->afterSaveCustomFieldsAndActivity($product, $row, true);

        return 'created';
    }

    /**
     * Ghi chỉ các cột đã map trong bước match column. Tạo mới: các cột không map = null/default như trước.
     */
    private function applyMappedFieldsToProduct(Product $product, array $row, bool $isNew): void
    {
        if ($isNew) {
            $product->company_id = $this->company?->id;
            $product->added_by = user() ? user()->id : null;
        } elseif (user()) {
            $product->last_updated_by = user()->id;
        }

        if ($this->columnExists('product_name')) {
            $product->name = (string) $this->getValue($row, 'product_name');
        }

        if ($isNew) {
            $product->price = (float) $this->resolveImportPrice($row);
        } elseif ($this->columnExists('price') || $this->columnExists('standard_price')) {
            $product->price = (float) $this->resolveImportPrice($row);
        }

        $this->setNullableStringField($product, 'description', $row, $isNew);
        $this->setNullableStringField($product, 'specification', $row, $isNew);
        $this->setNullableStringField($product, 'product_source', $row, $isNew);
        $this->setNullableStringField($product, 'brand', $row, $isNew);
        $this->setNullableStringField($product, 'product_grade', $row, $isNew);
        $this->setNullableStringField($product, 'storage_condition', $row, $isNew);
        $this->setNullableStringField($product, 'certification', $row, $isNew);
        $this->setNullableStringField($product, 'wholesale_price', $row, $isNew);
        $this->setNullableStringField($product, 'price_per_box', $row, $isNew);
        $this->setNullableStringField($product, 'employee_price', $row, $isNew);
        $this->setNullableStringField($product, 'inventory_type', $row, $isNew);

        if ($this->columnExists('sku')) {
            $v = $this->getValue($row, 'sku');
            $product->sku = ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;
        } elseif ($isNew) {
            $product->sku = null;
        }

        if ($this->columnExists('shelf_life_days')) {
            $v = $this->getValue($row, 'shelf_life_days');
            $product->shelf_life_days = ($v !== null && $v !== '' && is_numeric(trim((string) $v))) ? (int) trim((string) $v) : null;
        } elseif ($isNew) {
            $product->shelf_life_days = null;
        }

        if ($this->columnExists('track_inventory')) {
            $v = strtolower((string) $this->getValue($row, 'track_inventory'));
            $product->track_inventory = ($v === 'yes' || $v === '1' || $v === 'true') ? 1 : 0;
        }

        if ($this->columnExists('status')) {
            $rawStatus = $this->getValue($row, 'status');
            if ($rawStatus === null || trim((string) $rawStatus) === '') {
                $product->status = 'active';
            } else {
                $status = strtolower(trim((string) $rawStatus));
                $product->status = ($status === 'active') ? 'active' : 'inactive';
            }
        } elseif ($isNew) {
            $product->status = 'active';
        }

        if ($this->columnExists('allow_purchase')) {
            $rawAllow = $this->getValue($row, 'allow_purchase');
            if ($rawAllow === null || trim((string) $rawAllow) === '') {
                $product->allow_purchase = true;
            } else {
                $v = strtolower(trim((string) $rawAllow));
                $product->allow_purchase = in_array($v, ['yes', '1', 'true', 'y'], true);
            }
        } elseif ($isNew) {
            $product->allow_purchase = true;
        }

        if ($isNew) {
            $product->unit_id = $this->resolveUnitId($row);
            $cid = $this->resolveCategoryId($row);
            $product->category_id = $cid;
            $product->sub_category_id = $this->resolveSubCategoryId($row, $cid);
        } else {
            if ($this->columnExists('unit_type')) {
                $product->unit_id = $this->resolveUnitId($row);
            }
            if ($this->columnExists('product_category')) {
                $product->category_id = $this->resolveCategoryId($row);
            }
            if ($this->columnExists('product_sub_category')) {
                $product->sub_category_id = $this->resolveSubCategoryId($row, $product->category_id);
            }
        }
    }

    private function setNullableStringField(Product $product, string $attribute, array $row, bool $isNew): void
    {
        $mapKey = $attribute;
        if (! $this->columnExists($mapKey)) {
            if ($isNew) {
                $product->{$attribute} = null;
            }

            return;
        }
        $v = $this->getValue($row, $mapKey);
        $product->{$attribute} = ($v !== null && $v !== '') ? (is_scalar($v) ? (string) $v : (string) $v) : null;
    }

    private function afterSaveCustomFieldsAndActivity(Product $product, array $row, bool $wasCreated): void
    {
        $customFieldsData = $this->buildProductCustomFieldsData($row);
        if ($customFieldsData !== []) {
            $product->updateCustomFieldData($customFieldsData);
        }

        if (user()) {
            $action = $wasCreated ? 'product-created' : 'product-updated';
            self::createEmployeeActivity(user()->id, $action, $product->id, 'product');
        }
    }

    private function persistProductUpdate(Product $product, array $row, string $skuTrimmed): string
    {
        $this->applyMappedFieldsToProduct($product, $row, false);
        $product->save();
        $this->skuToId[$skuTrimmed] = $product->id;
        $this->afterSaveCustomFieldsAndActivity($product, $row, false);

        return 'updated';
    }

    private function findProductBySkuQuery(string $skuTrimmed)
    {
        $q = Product::query()->where('sku', $skuTrimmed);
        if ($this->company?->id !== null) {
            $q->where('company_id', $this->company->id);
        }

        return $q;
    }

    /**
     * CSV files often omit a price column (e.g. master data only). Default to 0 when no price
     * column is mapped or the cell is empty. If a column is mapped but the value is not numeric, fail clearly.
     */
    private function resolveImportPrice(array $row): string
    {
        $hasStandard = $this->columnExists('standard_price');
        $hasPrice = $this->columnExists('price');

        if (! $hasStandard && ! $hasPrice) {
            return '0';
        }

        $priceVal = $hasStandard ? $this->getValue($row, 'standard_price') : $this->getValue($row, 'price');
        $cleaned = is_scalar($priceVal) ? preg_replace('/[^\d.]/', '', (string) $priceVal) : '';

        if ($cleaned !== '' && is_numeric($cleaned)) {
            return $cleaned;
        }

        if ($priceVal === null || (is_string($priceVal) && trim($priceVal) === '')) {
            return '0';
        }

        throw new Exception(__('messages.importProductPriceNotNumeric'));
    }

    /**
     * Build custom_fields_data array for Product from row. Only for custom fields still in use;
     * product_grade, product_source, brand are now DB columns, not custom fields.
     *
     * @return array<string, mixed> keys like 'field_123' => value
     */
    private function buildProductCustomFieldsData(array $row): array
    {
        $companyId = $this->company?->id;
        if (! $companyId) {
            return [];
        }

        $group = CustomFieldGroup::where('company_id', $companyId)->where('model', Product::CUSTOM_FIELD_MODEL)->first();
        if (! $group) {
            return [];
        }

        $fields = CustomField::where('custom_field_group_id', $group->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
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
