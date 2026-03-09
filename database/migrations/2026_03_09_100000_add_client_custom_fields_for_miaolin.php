<?php

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add custom fields for Client (Miaolin import columns, excluding Customer Grade).
     */
    public function up(): void
    {
        if (! class_exists(Company::class) || ! class_exists(CustomFieldGroup::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::where('name', 'Client')
                ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
                ->where('company_id', $company->id)
                ->first();

            if (! $group) {
                continue;
            }

            $this->createFieldIfMissing($group, $company, [
                'name' => 'salesperson',
                'label' => 'Salesperson',
                'type' => 'text',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'department',
                'label' => 'Department',
                'type' => 'text',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'sales_assistant_name',
                'label' => 'Sales Assistant Name',
                'type' => 'text',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'channel_type',
                'label' => 'Channel Type',
                'type' => 'text',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'business_type',
                'label' => 'Business Type',
                'type' => 'text',
            ]);
        }
    }

    private function createFieldIfMissing(CustomFieldGroup $group, Company $company, array $definition): void
    {
        if (! CustomField::where('custom_field_group_id', $group->id)->where('name', $definition['name'])->exists()) {
            $field = new CustomField;
            $field->custom_field_group_id = $group->id;
            $field->company_id = $company->id;
            $field->label = $definition['label'];
            $field->name = $definition['name'];
            $field->type = $definition['type'];
            $field->values = null;
            $field->required = 'no';
            $field->export = 1;
            $field->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::where('name', 'Client')
                ->where('model', ClientDetails::CUSTOM_FIELD_MODEL)
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->whereIn('name', [
                        'salesperson',
                        'department',
                        'sales_assistant_name',
                        'channel_type',
                        'business_type',
                    ])
                    ->delete();
            }
        }
    }
};
