<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::select('id')->get();
        $customFieldGroup = [];

        foreach ($companies as $company) {
            $customFieldGroup = [
                [
                    'name' => 'Order',
                    'model' => Order::CUSTOM_FIELD_MODEL,
                    'company_id' => $company->id,
                ],
            ];
        }

        CustomFieldGroup::insert($customFieldGroup);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
