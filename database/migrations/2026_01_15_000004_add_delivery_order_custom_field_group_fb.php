<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        Company::all()->each(function (Company $company) {
            CustomFieldGroup::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => 'Delivery Order',
                ],
                [
                    'model' => 'App\\Models\\OrderDelivery',
                ]
            );
        });

        CustomFieldGroup::withoutGlobalScopes()->firstOrCreate(
            [
                'company_id' => null,
                'name' => 'Delivery Order',
            ],
            [
                'model' => 'App\\Models\\OrderDelivery',
            ]
        );
    }

    public function down(): void
    {
    }
};

