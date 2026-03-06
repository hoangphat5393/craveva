<?php

namespace Modules\Pricing\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class PricingTierItemsImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'tier_name', 'name' => 'Tier Name', 'required' => 'Yes'],
            ['id' => 'product_sku', 'name' => 'Product SKU', 'required' => 'Yes'],
            ['id' => 'discount_type', 'name' => 'Discount Type', 'required' => 'Yes'],
            ['id' => 'discount_value', 'name' => 'Discount Value', 'required' => 'No'],
        ];
    }

    public function array(array $array): array
    {
        $this->processedData = $array;

        return $array;
    }

    public function getProcessedData(): array
    {
        return $this->processedData;
    }
}
