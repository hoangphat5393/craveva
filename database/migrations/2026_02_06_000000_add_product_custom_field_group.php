<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Company;
use App\Models\CustomFieldGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure models are loaded
        if (class_exists(Company::class) && class_exists(CustomFieldGroup::class)) {
            $companies = Company::all();
            
            foreach ($companies as $company) {
                // Check if Product group exists for this company
                $exists = CustomFieldGroup::where('company_id', $company->id)
                    ->where('model', 'App\Models\Product')
                    ->exists();
                    
                if (!$exists) {
                    $group = new CustomFieldGroup();
                    $group->company_id = $company->id;
                    $group->name = 'Product';
                    $group->model = 'App\Models\Product';
                    $group->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: remove it again if needed
        // CustomFieldGroup::where('model', 'App\Models\Product')->delete();
    }
};
