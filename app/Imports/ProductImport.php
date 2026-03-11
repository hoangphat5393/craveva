<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'Yes'],
            ['id' => 'price', 'name' => __('app.price'), 'required' => 'No'],
            ['id' => 'unit_type', 'name' => __('modules.unitType.unitType'), 'required' => 'No'],
            ['id' => 'product_category', 'name' => __('modules.productCategory.productCategory'), 'required' => 'No'],
            ['id' => 'product_sub_category', 'name' => __('modules.productCategory.productSubCategory'), 'required' => 'No'],
            ['id' => 'sku', 'name' => __('app.sku'), 'required' => 'No'],
            ['id' => 'description', 'name' => __('app.description'), 'required' => 'No'],
            ['id' => 'specification', 'name' => __('app.specification'), 'required' => 'No'],
            ['id' => 'storage_condition', 'name' => 'Storage Condition', 'required' => 'No'],
            ['id' => 'certification', 'name' => 'Certification', 'required' => 'No'],
            ['id' => 'wholesale_price', 'name' => 'Wholesale Price', 'required' => 'No'],
            ['id' => 'price_per_box', 'name' => 'Price Per Box', 'required' => 'No'],
            ['id' => 'employee_price', 'name' => 'Employee Price', 'required' => 'No'],
            ['id' => 'shelf_life_days', 'name' => __('app.shelfLifeDays'), 'required' => 'No'],
            ['id' => 'track_inventory', 'name' => 'Track Inventory (Yes/No)', 'required' => 'No'],
            ['id' => 'inventory_type', 'name' => 'Inventory Type', 'required' => 'No'],
            ['id' => 'status', 'name' => 'Status (Active/Inactive)', 'required' => 'No'],
            ['id' => 'standard_price', 'name' => 'Standard Price', 'required' => 'No'],
            ['id' => 'product_grade', 'name' => __('app.productGrade'), 'required' => 'No'],
            ['id' => 'product_source', 'name' => __('app.productSource'), 'required' => 'No'],
            ['id' => 'brand', 'name' => __('app.brand'), 'required' => 'No'],
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
