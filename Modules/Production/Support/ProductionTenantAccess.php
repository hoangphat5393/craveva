<?php

namespace Modules\Production\Support;

use App\Models\ModuleSetting;

final class ProductionTenantAccess
{
    /**
     * Whether the authenticated tenant/session may load Production UX (menus use user_modules cache;
     * this matches DB tenant flags when cache is stale).
     */
    /**
     * Whether a company has the Production module enabled (admin or employee row).
     * Use for server-side gates that must not depend on the current user's cached modules.
     */
    public static function productionEnabledForCompanyId(int $companyId): bool
    {
        if ($companyId <= 0) {
            return false;
        }

        return ModuleSetting::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('module_name', 'production')
            ->where('is_allowed', 1)
            ->where('status', 'active')
            ->whereIn('type', ['admin', 'employee'])
            ->exists();
    }

    public static function tenantMayUseProduction(): bool
    {
        if (in_array('production', user_modules() ?: [], true)) {
            return true;
        }

        $companyId = company()?->id ?? null;
        if ($companyId === null) {
            return false;
        }

        if (self::productionEnabledForCompanyId((int) $companyId)) {
            return true;
        }

        $types = [];
        foreach (user_roles() ?? [] as $roleName) {
            $r = strtolower((string) $roleName);
            if ($r === 'admin') {
                $types[] = 'admin';
            }
            if ($r === 'employee') {
                $types[] = 'employee';
            }
        }
        $types = array_values(array_unique($types));
        if ($types === []) {
            return false;
        }

        return ModuleSetting::withoutGlobalScopes()
            ->where('company_id', (int) $companyId)
            ->where('module_name', 'production')
            ->where('is_allowed', 1)
            ->where('status', 'active')
            ->whereIn('type', $types)
            ->exists();
    }
}
