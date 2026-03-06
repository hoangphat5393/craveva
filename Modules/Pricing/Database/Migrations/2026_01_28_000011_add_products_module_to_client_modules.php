<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Add 'products' module for client
            $exists = ModuleSetting::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                ->where('module_name', 'products')
                ->where('type', 'client')
                ->where('company_id', $company->id)
                ->exists();

            if (! $exists) {
                ModuleSetting::create([
                    'module_name' => 'products',
                    'type' => 'client',
                    'company_id' => $company->id,
                    'status' => 'active',
                    'is_allowed' => 1,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ModuleSetting::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('module_name', 'products')
            ->where('type', 'client')
            ->delete();
    }
};
