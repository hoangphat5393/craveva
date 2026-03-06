<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! class_exists(Company::class)) {
            return;
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            CustomFieldGroup::firstOrCreate(
                [
                    'name' => 'Purchase Order',
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseOrder',
                    'company_id' => $company->id,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
