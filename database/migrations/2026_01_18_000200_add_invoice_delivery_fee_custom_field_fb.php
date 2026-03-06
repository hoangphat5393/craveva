<?php

use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::firstOrCreate([
                'name' => 'Invoice',
                'model' => 'App\\Models\\Invoice',
                'company_id' => $company->id,
            ]);

            $exists = CustomField::where('custom_field_group_id', $group->id)
                ->where('name', 'delivery_fee')
                ->exists();

            if (! $exists) {
                $field = new CustomField;
                $field->custom_field_group_id = $group->id;
                $field->company_id = $company->id;
                $field->label = 'Delivery Fee';
                $field->name = 'delivery_fee';
                $field->type = 'number';
                $field->required = 'no';
                $field->export = 1;
                $field->save();
            }
        }
    }

    public function down(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $group = CustomFieldGroup::where('name', 'Invoice')
                ->where('model', 'App\\Models\\Invoice')
                ->where('company_id', $company->id)
                ->first();

            if ($group) {
                CustomField::where('custom_field_group_id', $group->id)
                    ->where('name', 'delivery_fee')
                    ->delete();
            }
        }
    }
};
