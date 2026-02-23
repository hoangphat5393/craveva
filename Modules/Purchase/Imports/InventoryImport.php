<?php

namespace Modules\Purchase\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class InventoryImport implements ToArray
{

    protected array $processedData = [];

    public static function fields(): array
    {
        $fields = array(
            array('id' => 'sku', 'name' => __('app.sku'), 'required' => 'No'),
            array('id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'No'),
            array('id' => 'date', 'name' => __('app.date'), 'required' => 'No'),
            array('id' => 'type', 'name' => __('app.type'), 'required' => 'No'),
            array('id' => 'quantity', 'name' => __('purchase::modules.product.quantity'), 'required' => 'No'),
            array('id' => 'ending_inventory', 'name' => __('purchase::modules.inventory.endingInventory'), 'required' => 'No'),
            array('id' => 'cost_price', 'name' => __('app.price'), 'required' => 'No'),
            array('id' => 'description', 'name' => __('app.description'), 'required' => 'No'),
            array('id' => 'unit', 'name' => __('modules.unitType.unitType'), 'required' => 'No'),
            array('id' => 'specification', 'name' => __('purchase::modules.inventory.specification'), 'required' => 'No'),
            array('id' => 'manufacturing_date', 'name' => __('purchase::modules.inventory.manufacturingDate'), 'required' => 'No'),
            array('id' => 'expiration_date', 'name' => __('purchase::modules.inventory.expirationDate'), 'required' => 'No'),
        );

        if (request()->user()) {
            $customFields = \App\Models\CustomFieldGroup::where('model', 'Modules\Purchase\Entities\PurchaseInventory')
                ->where('company_id', company()->id)
                ->with('customField')
                ->first();

            if ($customFields) {
                $systemFieldNames = array_map(function ($field) {
                    return $field['name'];
                }, $fields);

                foreach ($customFields->customField as $customField) {
                    if (!in_array(__($customField->label), $systemFieldNames)) {
                        $fields[] = array('id' => 'field_' . $customField->id, 'name' => __($customField->label), 'required' => 'No');
                    }
                }
            }
        }

        return $fields;
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
