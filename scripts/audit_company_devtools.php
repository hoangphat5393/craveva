<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Models\SuperAdmin\Package;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$companyId = (int) ($argv[1] ?? 20);

$company = Company::with('package')->find($companyId);

if (! $company) {
    fwrite(STDERR, "Company {$companyId} not found.\n");
    exit(1);
}

echo "=== Company #{$company->id} ({$company->company_name}) ===\n";
echo 'package_id: ' . ($company->package_id ?? 'null') . "\n";

if (! $company->package) {
    echo "ERROR: No package relation loaded.\n";
    exit(1);
}

$pkg = $company->package;
echo "Package #{$pkg->id} ({$pkg->name}) max_employees={$pkg->max_employees}\n";
echo 'module_in_package (first 500 chars): ' . substr((string) $pkg->module_in_package, 0, 500) . "\n";

$names = CompanyObserver::packageModuleNamesFromJson($pkg->module_in_package ?? '[]');
echo 'Normalized modules count: ' . count($names) . "\n";
echo 'developertools in package JSON: ' . (in_array('developertools', $names, true) ? 'YES' : 'NO') . "\n";

echo "\n=== module_settings developertools (no global scope) ===\n";
$rows = ModuleSetting::withoutGlobalScope(CompanyScope::class)
    ->where('company_id', $companyId)
    ->where('module_name', 'developertools')
    ->get(['id', 'type', 'status', 'is_allowed']);

if ($rows->isEmpty()) {
    echo "NO ROWS for developertools — run migrate/backfill and packages:modules activate.\n";
} else {
    foreach ($rows as $row) {
        echo sprintf(
            "id=%s type=%s status=%s is_allowed=%s\n",
            $row->id,
            $row->type,
            $row->status,
            $row->is_allowed
        );
    }
}

$admin = $rows->firstWhere('type', 'admin');
if ($admin) {
    $ok = $admin->status === 'active' && (int) $admin->is_allowed === 1;
    echo "\nAdmin row allows access (active + is_allowed=1): " . ($ok ? 'YES' : 'NO') . "\n";
}

echo "\n=== Cache: company_{$companyId}_valid_package ===\n";
$cacheKey = 'company_' . $companyId . '_valid_package';
$cached = cache()->get($cacheKey);
echo 'cached value: ' . (is_bool($cached) ? ($cached ? 'true' : 'false') : var_export($cached, true)) . "\n";

if ($company->employees_count) {
    echo "employees_count (if loaded): {$company->employees_count}\n";
}
