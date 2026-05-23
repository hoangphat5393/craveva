<?php

declare(strict_types=1);

namespace Modules\Warehouse\Console;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Modules\Purchase\Services\ProductOpeningStockWarehouseSync;

class BackfillOpeningStockToDefaultWarehouseCommand extends Command
{
    protected $signature = 'warehouse:backfill-opening-stock-to-default
                            {--dry-run : Report only, do not write}
                            {--company= : Process a single company ID}';

    protected $description = 'Backfill legacy opening stock lines (missing warehouse_id) into the default warehouse';

    public function handle(ProductOpeningStockWarehouseSync $sync): int
    {
        if (! $sync->canSync()) {
            $this->error('Warehouse tables or StockMovementService are not available. Run migrations and enable the Warehouse module.');

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
            $results = $sync->backfillLegacyLinesForCompany((int) $company->id, $dryRun);

            foreach ($results as $result) {
                $rows[] = [
                    (int) $company->id,
                    (string) $company->company_name,
                    $result['adjustment_id'],
                    $result['product_id'],
                    $result['action'],
                    $result['note'],
                ];
            }
        }

        if ($rows === []) {
            $this->info('No legacy opening stock lines to backfill.');

            return self::SUCCESS;
        }

        $this->table(
            ['company_id', 'company_name', 'adjustment_id', 'product_id', 'action', 'note'],
            $rows
        );

        $synced = count(array_filter($rows, static fn (array $r): bool => $r[4] === 'synced'));
        $wouldSync = count(array_filter($rows, static fn (array $r): bool => $r[4] === 'would_sync'));
        $skipped = count(array_filter($rows, static fn (array $r): bool => $r[4] === 'skipped'));
        $errors = count(array_filter($rows, static fn (array $r): bool => $r[4] === 'error'));

        $this->info('Lines processed: '.count($rows).". synced={$synced}, would_sync={$wouldSync}, skipped={$skipped}, error={$errors}.");

        return self::SUCCESS;
    }
}
