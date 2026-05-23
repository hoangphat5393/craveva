<?php

declare(strict_types=1);

namespace Modules\Purchase\Console;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Modules\Purchase\Services\ProductionFgInventoryLedgerSync;

class BackfillProductionFgInventoryLedgerCommand extends Command
{
    protected $signature = 'production:backfill-fg-inventory-ledger
                            {--dry-run : Report only, do not write}
                            {--company= : Process a single company ID}';

    protected $description = 'Create or refresh Purchase Inventory ledger lines for posted Production FG outputs';

    public function handle(ProductionFgInventoryLedgerSync $sync): int
    {
        if (! $sync->canSync()) {
            $this->error('Purchase inventory or warehouse tables are not available.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run — no database writes.');
        }

        $query = Company::withoutGlobalScope(CompanyScope::class)
            ->where('status', 'active')
            ->orderBy('id');

        if ($this->option('company')) {
            $query->where('id', (int) $this->option('company'));
        }

        $companies = $query->get(['id', 'company_name']);

        if ($companies->isEmpty()) {
            $this->warn('No matching active companies.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($companies as $company) {
            $results = $sync->backfillPostedOutputsForCompany((int) $company->id, $dryRun);

            foreach ($results as $result) {
                $rows[] = [
                    (int) $company->id,
                    (string) $company->company_name,
                    $result['output_id'],
                    $result['product_id'],
                    $result['warehouse_id'],
                    $result['action'],
                    $result['note'],
                ];
            }
        }

        if ($rows === []) {
            $this->info('No posted FG outputs found.');

            return self::SUCCESS;
        }

        $this->table(
            ['company_id', 'company', 'output_id', 'product_id', 'warehouse_id', 'action', 'note'],
            $rows
        );

        $this->info('Processed ' . count($rows) . ' output row(s).');

        return self::SUCCESS;
    }
}
