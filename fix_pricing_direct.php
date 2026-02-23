<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;

echo "Starting DIRECT FIX for Pricing Module Settings...\n";

// Get all companies
$companies = Company::withoutGlobalScope(ActiveScope::class)->get();

echo "Found " . $companies->count() . " companies.\n";

$moduleName = 'pricing';
$types = ['admin', 'employee', 'client'];

foreach ($companies as $company) {
    echo "Processing Company ID: {$company->id} ({$company->company_name})\n";

    foreach ($types as $type) {
        $setting = ModuleSetting::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('module_name', $moduleName)
            ->where('type', $type)
            ->first();

        if (!$setting) {
            echo " - Creating setting for type: {$type}... ";
            try {
                ModuleSetting::create([
                    'company_id' => $company->id,
                    'module_name' => $moduleName,
                    'type' => $type,
                    'status' => 'active',
                    'is_allowed' => 1
                ]);
                echo "OK\n";
            } catch (\Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
            }
        } else {
            echo " - Setting exists for type: {$type}. Status: {$setting->status}, Allowed: {$setting->is_allowed}\n";
            if ($setting->is_allowed != 1 || $setting->status != 'active') {
                echo "   Updating to active/allowed... ";
                $setting->update(['status' => 'active', 'is_allowed' => 1]);
                echo "OK\n";
            }
        }
    }
}

echo "Clearing cache...\n";
try {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "Cache cleared.\n";
} catch (\Exception $e) {
    echo "Cache clear failed: " . $e->getMessage() . "\n";
}

echo "Fix Complete.\n";
