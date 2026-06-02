<?php

namespace App\Models;

use App\Scopes\CompanyScope;
use App\Traits\HasCompany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\ModuleSetting
 *
 * @property int $id
 * @property string $module_name
 * @property string $status
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereUpdatedAt($value)
 *
 * @property int|null $company_id
 * @property-read Company|null $company
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ModuleSetting whereCompanyId($value)
 *
 * @mixin \Eloquent
 */
class ModuleSetting extends BaseModel
{
    use HasCompany;

    const CLIENT_MODULES = [
        'projects',
        'tickets',
        'invoices',
        'estimates',
        'events',
        'messages',
        'tasks',
        'timelogs',
        'contracts',
        'notices',
        'payments',
        'orders',
        'knowledgebase',
    ];

    /**
     * Feature toggles shown in Module Settings even when not tied to SaaS package modules.
     *
     * @var list<string>
     */
    public const TENANT_FEATURE_MODULES = [
        'estimates_phase1_review',
    ];

    const OTHER_MODULES = [
        'clients',
        'developertools',
        'employees',
        'attendance',
        'expenses',
        'leaves',
        'leads',
        'holidays',
        'products',
        'reports',
        'settings',
        'bankaccount',
        'pricing',
        'warehouse',
    ];

    protected $guarded = ['id'];

    /**
     * Rows for the tenant Module Settings UI (admin / employee tabs).
     * Includes tenant feature toggles (e.g. estimates_phase1_review) even when not in package,
     * so admins can enable rollout flags per company. Package modules (pricing, developertools, …)
     * appear only when is_allowed = 1.
     *
     * @return Collection<int, ModuleSetting>
     */
    public static function forTenantModuleSettingsIndex(int $companyId, string $type): Collection
    {
        $allowed = self::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('module_name', '<>', 'settings')
            ->where('is_allowed', 1)
            ->where('type', $type)
            ->get();

        if ($type === 'client') {
            return $allowed;
        }

        $extras = self::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('type', $type)
            ->whereIn('module_name', self::TENANT_FEATURE_MODULES)
            ->get()
            ->unique('module_name');

        return $allowed
            ->concat($extras)
            ->unique('module_name')
            ->values();
    }

    public static function checkModule($moduleName)
    {

        $module = ModuleSetting::where('module_name', $moduleName);

        if (in_array('admin', user_roles())) {
            $module = $module->where('type', 'admin');
        } elseif (in_array('client', user_roles())) {
            $module = $module->where('type', 'client');
        } elseif (in_array('employee', user_roles())) {
            $module = $module->where('type', 'employee');
        }

        $module = $module->where('status', 'active');

        $module = $module->first();

        return (bool) $module;
    }

    public static function addCompanyIdToNullModule($company, $module)
    {
        // This is done for existing module settings. This will update the company id with 1
        // for existing module rather creating new module setting
        $companyId = is_array($company) ? (int) ($company['id'] ?? 0) : (int) $company->id;
        if ($companyId === 1) {
            ModuleSetting::withoutGlobalScope(CompanyScope::class)->where('module_name', $module)
                ->whereNull('company_id')
                ->update(['company_id' => $companyId]);
        }
    }

    public static function createRoleSettingEntry($module, $roles, $company)
    {
        self::addCompanyIdToNullModule($company, $module);

        $companyId = is_array($company) ? (int) ($company['id'] ?? 0) : (int) $company->id;

        $packageJson = is_array($company)
            ? ($company['package']['module_in_package'] ?? $company['module_in_package'] ?? null)
            : ($company->package?->module_in_package);
        $decoded = json_decode((string) ($packageJson ?? '[]'), true);
        $moduleInPackage = collect(is_array($decoded) ? $decoded : []);

        foreach ($roles as $role) {
            $data = ModuleSetting::withoutGlobalScope(CompanyScope::class)
                ->where('module_name', $module)
                ->where('type', $role)
                ->where('company_id', $companyId)
                ->first();

            if (! $data) {
                ModuleSetting::create([
                    'module_name' => $module,
                    'type' => $role,
                    'company_id' => $companyId,
                    'status' => 'active',
                    'is_allowed' => $moduleInPackage->contains($module) ? 1 : 0,
                ]);
            }
        }

        $moduleExists = Module::withoutGlobalScopes()
            ->where('module_name', $module)
            ->exists();

        if ($moduleExists) {
            PermissionRole::insertModuleRolePermission($module, $companyId);
        }
    }
}
