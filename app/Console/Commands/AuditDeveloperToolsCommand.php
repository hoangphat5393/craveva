<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\DeveloperTools\Entities\DeveloperToolsCredential;
use Nwidart\Modules\Facades\Module as NwidartModule;

class AuditDeveloperToolsCommand extends Command
{
    protected $signature = 'developertools:audit';

    protected $description = 'Audit Developer Tools: nwidart enable state, module_settings vs package, credentials';

    public function handle(): int
    {
        $module = NwidartModule::find('DeveloperTools');
        $nwidartEnabled = $module && $module->isEnabled();
        $this->line('Nwidart module DeveloperTools enabled: '.($nwidartEnabled ? 'yes' : 'no'));

        if (! Schema::hasTable('module_settings')) {
            $this->warn('Table module_settings missing.');

            return self::SUCCESS;
        }

        $adminRows = ModuleSetting::withoutGlobalScope(CompanyScope::class)
            ->where('module_name', 'developertools')
            ->where('type', 'admin')
            ->get();

        $activeOk = $adminRows->filter(fn ($r) => (int) $r->is_allowed === 1 && $r->status === 'active');
        $this->line('module_settings (admin, developertools): total '.$adminRows->count().', active+allowed '.$activeOk->count());

        $mismatchPackageHasDtButAdminNotOk = 0;
        if (Schema::hasTable('companies') && Schema::hasTable('packages')) {
            foreach (Company::query()->with('package')->cursor() as $company) {
                $package = $company->package;
                if (! $package) {
                    continue;
                }
                $names = CompanyObserver::packageModuleNamesFromJson($package->module_in_package ?? '[]');
                if (! in_array('developertools', $names, true)) {
                    continue;
                }
                $row = $adminRows->firstWhere('company_id', $company->id);
                if (! $row || (int) $row->is_allowed !== 1 || $row->status !== 'active') {
                    $mismatchPackageHasDtButAdminNotOk++;
                }
            }
        }

        if ($mismatchPackageHasDtButAdminNotOk > 0) {
            $this->warn('Companies with package containing developertools but admin module_settings not active/allowed: '.$mismatchPackageHasDtButAdminNotOk.' (run packages:modules activate --module=developertools or fix package sync).');
        } else {
            $this->line('Package vs admin module_settings: no mismatch detected.');
        }

        if (Schema::hasTable('developer_tools_credentials')) {
            $credCount = DeveloperToolsCredential::query()->count();
            $this->line('developer_tools_credentials rows: '.$credCount);
        }

        $statusesPath = storage_path('app/modules_statuses.json');
        if (is_file($statusesPath)) {
            $this->line('modules_statuses.json: present');
        } else {
            $this->line('modules_statuses.json: missing (nwidart may use defaults)');
        }

        return self::SUCCESS;
    }
}
