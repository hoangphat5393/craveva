<?php

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\CompanyScope;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * developertools is in package JSON and pricing UI but was never in ModuleSetting::OTHER_MODULES,
     * so CompanyObserver::createModuleSettings() never created module_settings rows.
     * updateModuleSettings() only updates existing rows — no insert — so the Settings sidebar
     * guard ModuleSetting::checkModule('developertools') stayed false.
     */
    public function up(): void
    {
        if (! Schema::hasTable('module_settings') || ! Schema::hasTable('companies')) {
            return;
        }

        $packageModules = Package::query()
            ->get(['id', 'module_in_package'])
            ->mapWithKeys(function (Package $package): array {
                $decoded = json_decode($package->module_in_package, true);
                $modules = collect(is_array($decoded) ? $decoded : [])
                    ->map(fn ($value) => strtolower(trim((string) $value)))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [$package->id => $modules];
            })
            ->all();

        foreach (Company::query()->get(['id', 'package_id']) as $company) {
            $modulesInPackage = $packageModules[$company->package_id] ?? [];
            $inPackage = in_array('developertools', $modulesInPackage, true);

            foreach (['admin', 'employee'] as $type) {
                $exists = ModuleSetting::withoutGlobalScope(CompanyScope::class)
                    ->where('company_id', $company->id)
                    ->where('module_name', 'developertools')
                    ->where('type', $type)
                    ->exists();

                if ($exists) {
                    continue;
                }

                ModuleSetting::withoutGlobalScope(CompanyScope::class)->create([
                    'company_id' => $company->id,
                    'module_name' => 'developertools',
                    'type' => $type,
                    'status' => $inPackage ? 'active' : 'deactive',
                    'is_allowed' => $inPackage ? 1 : 0,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: do not delete rows that may already be in use in production.
    }
};
