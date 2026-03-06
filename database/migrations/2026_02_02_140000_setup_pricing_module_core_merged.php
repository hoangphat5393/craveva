<?php

use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\SuperAdmin\Package;
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
        // Use Console Output for debugging
        $out = new \Symfony\Component\Console\Output\ConsoleOutput;
        $out->writeln('<info>>>> STARTING PRICING MODULE CORE SETUP (MERGED) <<<</info>');

        // 1. Create Module Entry
        $out->writeln('<comment>1. Creating/Updating Module Entry...</comment>');
        $module = Module::withoutGlobalScopes()->where('module_name', 'pricing')->first() ?: new Module;
        $module->module_name = 'pricing';
        $module->description = 'Pricing module for companies';
        $module->is_superadmin = 0;
        $module->save();

        // 2. Create Permissions
        $out->writeln('<comment>2. Creating Permissions...</comment>');
        $permissionsList = [
            ['name' => 'add_pricing_tiers', 'display_name' => 'Add Pricing Tiers', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_pricing_tiers', 'display_name' => 'View Pricing Tiers', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ['name' => 'edit_pricing_tiers', 'display_name' => 'Edit Pricing Tiers', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ['name' => 'delete_pricing_tiers', 'display_name' => 'Delete Pricing Tiers', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ['name' => 'add_client_pricing', 'display_name' => 'Add Client Pricing', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_client_pricing', 'display_name' => 'View Client Pricing', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ['name' => 'edit_client_pricing', 'display_name' => 'Edit Client Pricing', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ['name' => 'view_client_tiers', 'display_name' => 'View Client Tiers', 'is_custom' => 0, 'allowed_permissions' => Permission::ALL_ADDED_NONE],
        ];

        foreach ($permissionsList as $permData) {
            Permission::updateOrCreate(
                ['module_id' => $module->id, 'name' => $permData['name']],
                [
                    'display_name' => $permData['display_name'],
                    'is_custom' => $permData['is_custom'],
                    'allowed_permissions' => $permData['allowed_permissions'],
                ]
            );
        }

        // 3. Update Packages
        if (class_exists(Package::class)) {
            $out->writeln('<comment>3. Updating Packages...</comment>');
            $packages = Package::all();
            foreach ($packages as $package) {
                $modules = json_decode($package->module_in_package, true);
                if (is_array($modules) && ! in_array('pricing', $modules)) {
                    $modules[] = 'pricing';
                    $package->module_in_package = json_encode($modules);
                    $package->save();
                }
            }
        }

        // 4. Company Setup (Settings, Permissions, Cache)
        $out->writeln('<comment>4. Configuring Companies...</comment>');
        $companies = Company::all();
        $types = ['admin', 'employee', 'client'];

        foreach ($companies as $company) {
            $out->writeln("   - Company ID: {$company->id} ({$company->company_name})");

            // 4a. Module Settings
            foreach ($types as $type) {
                $setting = ModuleSetting::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('module_name', 'pricing')
                    ->where('type', $type)
                    ->first();

                if (! $setting) {
                    ModuleSetting::create([
                        'company_id' => $company->id,
                        'module_name' => 'pricing',
                        'type' => $type,
                        'status' => 'active',
                        'is_allowed' => 1,
                    ]);
                } else {
                    if ($setting->status !== 'active' || $setting->is_allowed !== 1) {
                        $setting->update(['status' => 'active', 'is_allowed' => 1]);
                    }
                }
            }

            // 4b. Assign Permissions to Admin
            $adminRole = Role::withoutGlobalScopes([CompanyScope::class])
                ->where('company_id', $company->id)
                ->where('name', 'admin')
                ->first();

            if ($adminRole) {
                $pricingPermissions = Permission::where('module_id', $module->id)->get();
                if ($pricingPermissions->isEmpty()) {
                    // Fallback search
                    $pricingPermissions = Permission::where('name', 'like', 'view_pricing%')
                        ->orWhere('name', 'like', '%client_tiers%')
                        ->get();
                }

                foreach ($pricingPermissions as $perm) {
                    $exists = PermissionRole::where('role_id', $adminRole->id)
                        ->where('permission_id', $perm->id)
                        ->exists();

                    if (! $exists) {
                        PermissionRole::create([
                            'permission_id' => $perm->id,
                            'role_id' => $adminRole->id,
                            'permission_type_id' => 4, // All
                        ]);
                    }
                }
            }

            // 4c. Clear User Cache
            $users = User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
                ->where('company_id', $company->id)
                ->get();

            foreach ($users as $user) {
                cache()->forget('user_modules_'.$user->id);
                cache()->forget('sidebar_user_perms_'.$user->id);
            }
        }

        $out->writeln('<info>>>> PRICING MODULE CORE SETUP COMPLETED <<<</info>');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No reverse action to prevent accidental data loss
    }
};
