<?php

namespace Modules\Pricing\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ClientProductPricingImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'customer_code', 'name' => 'Customer Code', 'required' => 'Yes'],
            ['id' => 'email', 'name' => 'Email', 'required' => 'No'],
            ['id' => 'product_sku', 'name' => 'Product SKU', 'required' => 'Yes'],
            ['id' => 'custom_price', 'name' => 'Custom Price', 'required' => 'No'],
            ['id' => 'discount_type', 'name' => 'Discount Type', 'required' => 'No'],
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
