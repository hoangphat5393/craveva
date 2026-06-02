<?php

use App\Models\ModuleSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_settings')) {
            return;
        }

        ModuleSetting::withoutGlobalScopes()
            ->whereIn('module_name', ModuleSetting::TENANT_FEATURE_MODULES)
            ->whereIn('type', ['admin', 'employee'])
            ->update(['is_allowed' => 1]);
    }

    public function down(): void
    {
        // Intentionally no-op: this migration repairs tenant feature toggles
        // and should not re-lock them when rolling back.
    }
};
