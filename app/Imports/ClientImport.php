<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ClientImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'name', 'name' => __('modules.client.clientName'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('app.email'), 'required' => 'No'],
            ['id' => 'mobile', 'name' => __('app.mobile'), 'required' => 'No'],
            ['id' => 'gender', 'name' => __('modules.employees.gender'), 'required' => 'No'],
            ['id' => 'company_name', 'name' => __('modules.client.companyName'), 'required' => 'No'],
            ['id' => 'address', 'name' => __('modules.accountSettings.companyAddress'), 'required' => 'No'],
            ['id' => 'city', 'name' => __('modules.stripeCustomerAddress.city'), 'required' => 'No'],
            ['id' => 'state', 'name' => __('modules.stripeCustomerAddress.state'), 'required' => 'No'],
            ['id' => 'country_id', 'name' => __('modules.stripeCustomerAddress.country'), 'required' => 'No'],
            ['id' => 'postal_code', 'name' => __('modules.stripeCustomerAddress.postalCode'), 'required' => 'No'],
            ['id' => 'company_phone', 'name' => __('modules.client.officePhoneNumber'), 'required' => 'No'],
            ['id' => 'company_website', 'name' => __('modules.client.website'), 'required' => 'No'],
            ['id' => 'gst_number', 'name' => __('app.gstNumber'), 'required' => 'No'],
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
