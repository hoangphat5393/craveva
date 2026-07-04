<?php

namespace App\Imports;

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Maatwebsite\Excel\Concerns\ToArray;

class ClientImport implements ToArray
{
    protected array $processedData = [];

    public static function fields(): array
    {
        return [
            ['id' => 'client_code', 'name' => __('modules.client.clientCode'), 'required' => 'No'],
            ['id' => 'name', 'name' => __('modules.client.clientName'), 'required' => 'Yes'],
            ['id' => 'salutation', 'name' => __('modules.client.salutation'), 'required' => 'No'],
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
            ['id' => 'gst_number', 'name' => __('app.gstNumber').' ('.__('app.taxId').')', 'required' => 'No'],
            // Core commercial fields stored on client_details. Client custom fields are appended dynamically from Settings.
            ['id' => 'customer_grade', 'name' => __('modules.client.customerGrade'), 'required' => 'No'],
            ['id' => 'channel_type', 'name' => __('modules.client.channelType'), 'required' => 'No'],
            ['id' => 'business_type', 'name' => __('modules.client.businessType'), 'required' => 'No'],
            ['id' => 'payment_terms', 'name' => __('modules.client.paymentTerms'), 'required' => 'No'],
            ['id' => 'business_closure_date', 'name' => __('modules.client.businessClosureDate'), 'required' => 'No'],
            ['id' => 'designated_warehouse_code', 'name' => 'Designated Warehouse Code', 'required' => 'No'],
            ['id' => 'designated_warehouse_name', 'name' => 'Designated Warehouse Name', 'required' => 'No'],
        ];
    }

    /**
     * Resolve company id for import mapping (avoid company()?->id when company() is false — skips merge silently).
     */
    public static function resolveImportCompanyId(): ?int
    {
        $co = company();
        if ($co instanceof Company) {
            return (int) $co->id;
        }

        $user = function_exists('user') ? user() : null;
        if ($user && ($user->company_id ?? null)) {
            return (int) $user->company_id;
        }

        return null;
    }

    /**
     * Append Client module custom fields from DB so import mapping dropdown lists them (slug = field name).
     */
    public static function mergeDynamicColumns(array $columns): array
    {
        $companyId = self::resolveImportCompanyId();
        if (! $companyId) {
            return $columns;
        }

        $group = CustomFieldGroup::where('name', 'Client')
            ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
            ->where('company_id', $companyId)
            ->first();

        if (! $group) {
            return $columns;
        }

        $existingIds = collect($columns)->pluck('id')->flip();
        $customFields = CustomField::where('custom_field_group_id', $group->id)
            ->orderBy('sort_order')
            ->get();

        foreach ($customFields as $cf) {
            if ($existingIds->has($cf->name)) {
                continue;
            }
            $columns[] = [
                'id' => $cf->name,
                'name' => $cf->label,
                'required' => strtolower((string) $cf->required) === 'yes' ? 'Yes' : 'No',
            ];
        }

        return $columns;
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
