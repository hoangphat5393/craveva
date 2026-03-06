<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Console\Command;

class FixPricingModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-pricing-module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Pricing module permissions and settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting fix for Pricing module...');

        // 1. Ensure 'pricing' module exists in `modules` table
        $module = Module::firstOrCreate(['module_name' => 'pricing']);
        $module->description = 'Pricing Module';
        $module->save();
        $this->info("Module 'pricing' ensured. ID: ".$module->id);

        // 2. Define permissions
        $permissions = [
            'add_pricing_tiers',
            'view_pricing_tiers',
            'edit_pricing_tiers',
            'delete_pricing_tiers',
            'manage_client_pricing', // General permission for client pricing section
            'view_client_pricing',
            'add_client_pricing',
            'edit_client_pricing',
            'delete_client_pricing',
            'view_client_tiers',
        ];

        $createdPermissions = [];
        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'module_id' => $module->id],
                [
                    'display_name' => ucwords(str_replace('_', ' ', $permName)),
                    'is_custom' => 1,
                    'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5,
                ]
            );
            $createdPermissions[] = $perm;
        }
        $this->info('Permissions ensured.');

        // 3. Fix for specific user/company
        $targetEmail = 'hoangphat5393@gmail.com';
        $user = User::where('email', $targetEmail)->first();

        if (! $user) {
            $this->error("User $targetEmail not found.");

            return;
        }

        $companyId = $user->company_id;
        if (! $companyId) {
            $employeeDetail = \App\Models\EmployeeDetails::where('user_id', $user->id)->first();
            if ($employeeDetail) {
                $companyId = $employeeDetail->company_id;
            }
        }

        if (! $companyId) {
            // Fallback to first company if still not found, assuming single tenant or main company
            $companyId = Company::first()->id ?? null;
        }

        if (! $companyId) {
            $this->error('Company ID could not be determined.');

            return;
        }

        $this->info("Fixing for Company ID: $companyId");

        // 4. Ensure ModuleSetting is active
        $types = ['admin', 'employee', 'client'];
        foreach ($types as $type) {
            $setting = ModuleSetting::where('company_id', $companyId)
                ->where('type', $type)
                ->where('module_name', 'pricing')
                ->first();

            if (! $setting) {
                $setting = new ModuleSetting;
                $setting->company_id = $companyId;
                $setting->module_name = 'pricing';
                $setting->type = $type;
                $setting->status = 'active';
                $setting->is_allowed = 1;
                $setting->save();
                $this->info("Created pricing setting for $type");
            } else {
                $setting->status = 'active';
                $setting->is_allowed = 1;
                $setting->save();
                $this->info("Updated pricing setting for $type");
            }
        }

        // 5. Assign permissions to Admin Role of this company
        $adminRole = Role::where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        if ($adminRole) {
            foreach ($createdPermissions as $perm) {
                PermissionRole::firstOrCreate([
                    'permission_id' => $perm->id,
                    'role_id' => $adminRole->id,
                    'permission_type_id' => 4, // 4 usually means 'All' or similar high privilege
                ]);
            }
            $this->info('Assigned permissions to Admin role.');
        } else {
            $this->warn('Admin role not found for company.');
        }

        // 6. Assign permissions to the user specifically (UserPermission) if needed
        // Sometimes users have direct permissions
        foreach ($createdPermissions as $perm) {
            UserPermission::firstOrCreate([
                'user_id' => $user->id,
                'permission_id' => $perm->id,
                'permission_type_id' => 4,
            ]);
        }
        $this->info('Assigned permissions to User directly.');

        // 7. Clear cache
        cache()->forget('user_modules_'.$user->id);
        $this->info('Cache cleared.');

        $this->info('Fix complete.');
    }
}
