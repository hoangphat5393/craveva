<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Check if group exists for this company
            $exists = CustomFieldGroup::where('company_id', $company->id)
                ->where('model', 'App\\Models\\OrderDelivery')
                ->exists();

            if (!$exists) {
                CustomFieldGroup::create([
                    'company_id' => $company->id,
                    'name' => 'Delivery Order',
                    'model' => 'App\\Models\\OrderDelivery',
                ]);
            }
        }
    }

    public function down(): void
    {
        // Optional: Remove the group
    }
};
