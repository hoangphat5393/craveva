<?php

use App\Models\Company;
use App\Models\Module as AppModule;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Scopes\CompanyScope;
use Illuminate\Database\Migrations\Migration;
use Nwidart\Modules\Facades\Module as NwidartModule;

return new class extends Migration
{
    public function up(): void
    {
        $normalizedNwidartNames = collect(array_keys(NwidartModule::all()))
            ->map(function (string $name): string {
                $lower = strtolower(trim($name));

                // Keep historical DB naming used by existing logic.
                return $lower === 'subdomain' ? 'custom_domain' : $lower;
            })
            ->unique()
            ->values();

        $existingDbNames = AppModule::withoutGlobalScopes()
            ->pluck('module_name')
            ->map(fn($name) => strtolower((string) $name))
            ->values();

        $missingDbNames = $normalizedNwidartNames
            ->diff($existingDbNames)
            ->values();

        foreach ($missingDbNames as $moduleName) {
            AppModule::withoutGlobalScopes()->firstOrCreate(
                ['module_name' => $moduleName],
                ['description' => ucfirst(str_replace('_', ' ', $moduleName)) . ' Module']
            );
        }

        if ($missingDbNames->isEmpty()) {
            return;
        }

        $packageModules = Package::query()
            ->get(['id', 'module_in_package'])
            ->mapWithKeys(function ($package) {
                $decoded = json_decode($package->module_in_package, true);
                $modules = collect(is_array($decoded) ? $decoded : [])
                    ->map(fn($value) => strtolower(trim((string) $value)))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return [$package->id => $modules];
            })
            ->all();

        $companies = Company::query()->get(['id', 'package_id']);
        $types = ['admin', 'employee'];

        foreach ($companies as $company) {
            $modulesInPackage = $packageModules[$company->package_id] ?? [];

            foreach ($missingDbNames as $moduleName) {
                $isAllowed = in_array($moduleName, $modulesInPackage, true);

                foreach ($types as $type) {
                    $exists = ModuleSetting::withoutGlobalScope(CompanyScope::class)
                        ->where('module_name', $moduleName)
                        ->where('type', $type)
                        ->where('company_id', $company->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    ModuleSetting::create([
                        'module_name' => $moduleName,
                        'type' => $type,
                        'company_id' => $company->id,
                        'status' => $isAllowed ? 'active' : 'deactive',
                        'is_allowed' => $isAllowed ? 1 : 0,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Non-destructive by design for production safety.
    }
};
