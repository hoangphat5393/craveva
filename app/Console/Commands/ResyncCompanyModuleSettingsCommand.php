<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Observers\CompanyObserver;
use Illuminate\Console\Command;

class ResyncCompanyModuleSettingsCommand extends Command
{
    protected $signature = 'companies:resync-module-settings
                            {--company= : Only this company ID}';

    protected $description = 'Re-run updateModuleSettings for each company (activates implied warehouse when package has purchase/products).';

    public function handle(CompanyObserver $observer): int
    {
        $query = Company::query()->orderBy('id');
        if ($this->option('company')) {
            $query->where('id', (int) $this->option('company'));
        }

        $count = 0;
        $query->each(function (Company $company) use ($observer, &$count) {
            $observer->updateModuleSettings($company);
            $count++;
            $this->line("  Resynced company #{$company->id}");
        });

        $this->info("Done. {$count} company(ies) resynced.");

        return self::SUCCESS;
    }
}
