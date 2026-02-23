<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ModuleSetting;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use try-catch to avoid issues if tables don't exist during fresh install
        try {
            if (!Schema::hasTable('companies') || !Schema::hasTable('module_settings')) {
                return;
            }

            $companies = Company::all();
            $modules = ['purchase', 'products'];

            foreach ($companies as $company) {
                foreach ($modules as $moduleName) {
                    $exists = ModuleSetting::where('company_id', $company->id)
                        ->where('module_name', $moduleName)
                        ->where('type', 'client')
                        ->exists();

                    if (!$exists) {
                        $setting = new ModuleSetting();
                        $setting->company_id = $company->id;
                        $setting->module_name = $moduleName;
                        $setting->status = 'active';
                        $setting->type = 'client';
                        $setting->is_allowed = 1;
                        $setting->save();
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error or ignore
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Safe to skip
    }
};
