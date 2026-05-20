<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ModuleSetting;
use App\Scopes\CompanyScope;

final class EstimatesPhase1Review
{
    public const MODULE_NAME = 'estimates_phase1_review';

    /**
     * Tenant flag: OEM internal review (President + VP), BOM on quotation, convert SO gate.
     * When no row exists, defaults to disabled (safe for multi-tenant).
     */
    public static function enabled(): bool
    {
        $companyId = (int) (company()?->id ?? 0);
        if ($companyId <= 0) {
            return false;
        }

        $setting = ModuleSetting::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('module_name', self::MODULE_NAME)
            ->where('type', 'admin')
            ->first();

        if ($setting === null) {
            return false;
        }

        return $setting->status === 'active' && (int) ($setting->is_allowed ?? 0) === 1;
    }

    public static function ensureModuleRowsForCompany(int $companyId, string $status = 'deactive'): void
    {
        if ($companyId <= 0) {
            return;
        }

        foreach (['admin', 'employee'] as $type) {
            ModuleSetting::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'module_name' => self::MODULE_NAME,
                    'type' => $type,
                ],
                [
                    'status' => $status,
                    'is_allowed' => 1,
                ],
            );
        }
    }
}
