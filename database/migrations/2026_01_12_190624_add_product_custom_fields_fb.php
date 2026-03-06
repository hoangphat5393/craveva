<?php

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure we don't break if Company table is empty or doesn't exist yet (though it should)
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            // 1. Get or Create Group for Product
            $group = CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Product',
                    'model' => 'App\Models\Product',
                    'company_id' => $company->id,
                ]
            );

            // 2. Storage Condition (Select)
            if (! CustomField::where('custom_field_group_id', $group->id)->where('name', 'storage_condition')->exists()) {
                $field = new CustomField;
                $field->custom_field_group_id = $group->id;
                $field->company_id = $company->id;
                $field->label = 'Storage Condition';
                $field->name = 'storage_condition';
                $field->type = 'select';
                $field->values = json_encode(['Frozen', 'Chilled', 'Ambient']);
                $field->required = 'no';
                $field->export = 1;
                $field->save();
            }

            // 3. Certification (Text)
            if (! CustomField::where('custom_field_group_id', $group->id)->where('name', 'certification')->exists()) {
                $field = new CustomField;
                $field->custom_field_group_id = $group->id;
                $field->company_id = $company->id;
                $field->label = 'Certification';
                $field->name = 'certification';
                $field->type = 'text';
                $field->required = 'no';
                $field->export = 1;
                $field->save();
            }
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
            $group = CustomFieldGroup::where('name', 'Product')
                ->where('model', 'App\Models\Product')
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->whereIn('name', ['storage_condition', 'certification'])
                    ->delete();
            }
        }
    }
};
