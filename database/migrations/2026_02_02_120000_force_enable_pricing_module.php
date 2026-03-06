<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
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
        if (! Schema::hasTable('companies') || ! Schema::hasTable('module_settings')) {
            return;
        }

        $companies = Company::all();
        $moduleName = 'pricing';
        $types = ['admin', 'employee', 'client'];

        foreach ($companies as $company) {
            foreach ($types as $type) {
                // Update existing or create new
                ModuleSetting::withoutGlobalScopes()
                    ->updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'module_name' => $moduleName,
                            'type' => $type,
                        ],
                        [
                            'status' => 'active',
                            'is_allowed' => 1,
                        ]
                    );
            }

            // Clear cache for users of this company
            User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
                ->where('company_id', $company->id)->each(function ($user) {
                    cache()->forget('user_modules_'.$user->id);
                    cache()->forget('sidebar_user_perms_'.$user->id);
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
