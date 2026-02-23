<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Module;
use App\Models\ModuleSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove from modules table (SuperAdmin list)
        Module::where('module_name', 'onboarding')->delete();
        Module::where('module_name', 'Onboarding')->delete();

        // Remove from module_settings table (Company settings)
        ModuleSetting::where('module_name', 'onboarding')->delete();
        ModuleSetting::where('module_name', 'Onboarding')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot restore the deleted record easily without the original data.
        // This is a cleanup migration.
    }
};
