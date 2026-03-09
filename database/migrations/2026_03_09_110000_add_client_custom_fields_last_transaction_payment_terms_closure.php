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
     * Add custom fields for Client (temporary for demo):
     * - Last Transaction Date
     * - Payment Terms
     * - Business Closure Date (khi có giá trị → set User.status = inactive, xử lý ở import/controller)
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
                'name' => 'last_transaction_at',
                'label' => 'Last Transaction Date',
                'type' => 'date',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'payment_terms',
                'label' => 'Payment Terms',
                'type' => 'text',
            ]);

            $this->createFieldIfMissing($group, $company, [
                'name' => 'business_closure_date',
                'label' => 'Business Closure Date',
                'type' => 'date',
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
                    ->whereIn('name', ['last_transaction_at', 'payment_terms', 'business_closure_date'])
                    ->delete();
            }
        }
    }
};
