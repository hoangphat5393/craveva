<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\SuperAdmin\Package;
use App\Scopes\CompanyScope;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $moduleName = 'production';

        $module = Module::withoutGlobalScopes()->firstOrCreate(
            ['module_name' => $moduleName],
            ['description' => 'Production Module']
        );

        if (empty($module->description)) {
            $module->description = 'Production Module';
            $module->save();
        }

        $permissions = [
            'view_production_orders' => Permission::ALL_NONE,
            'add_production_orders' => Permission::ALL_NONE,
            'edit_production_orders' => Permission::ALL_NONE,
            'delete_production_orders' => Permission::ALL_NONE,
        ];

        $permissionIds = [];

        foreach ($permissions as $permissionName => $allowedPermissions) {
            $permission = Permission::firstOrNew(['name' => $permissionName]);

            $permission->display_name = $permission->display_name ?: ucwords(str_replace('_', ' ', $permissionName));
            $permission->module_id = $module->id;
            $permission->is_custom = 1;
            $permission->allowed_permissions = $permission->allowed_permissions ?: $allowedPermissions;
            $permission->save();

            $permissionIds[] = $permission->id;
        }

        $adminRoles = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'admin')
            ->get(['id']);

        foreach ($adminRoles as $adminRole) {
            foreach ($permissionIds as $permissionId) {
                PermissionRole::firstOrCreate(
                    [
                        'permission_id' => $permissionId,
                        'role_id' => $adminRole->id,
                    ],
                    ['permission_type_id' => 4]
                );
            }
        }

        $packageModules = [];
        $packages = Package::query()->get(['id', 'module_in_package']);

        foreach ($packages as $package) {
            $decoded = json_decode($package->module_in_package, true);
            $modules = collect(is_array($decoded) ? $decoded : [])
                ->map(fn ($value) => strtolower(trim((string) $value)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $shouldAttach = in_array('warehouse', $modules, true)
                || in_array('purchase', $modules, true)
                || in_array('products', $modules, true);

            if ($shouldAttach && ! in_array($moduleName, $modules, true)) {
                $modules[] = $moduleName;
                $package->module_in_package = json_encode(array_values(array_unique($modules)));
                $package->save();
            }

            $packageModules[$package->id] = $modules;
        }

        $types = ['admin', 'employee'];
        $companies = Company::query()->get(['id', 'package_id']);

        foreach ($companies as $company) {
            $modulesInPackage = $packageModules[$company->package_id] ?? [];
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

    public function down(): void
    {
        // Intentionally left empty to avoid destructive rollback on production data.
    }
};
