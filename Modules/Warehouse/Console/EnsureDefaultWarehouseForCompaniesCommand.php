<?php

declare(strict_types=1);

namespace Modules\Warehouse\Console;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use Modules\Warehouse\Services\EnsureDefaultWarehouseService;

class EnsureDefaultWarehouseForCompaniesCommand extends Command
{
    protected $signature = 'warehouse:ensure-default-for-companies
                            {--dry-run : Report only, do not write}
                            {--company= : Process a single company ID}';

    protected $description = 'Ensure each active company has exactly one default active warehouse (create or assign)';

    public function handle(EnsureDefaultWarehouseService $service): int
    {
        if (! $service->warehousesTableExists()) {
            $this->error('Table warehouses does not exist. Run migrations first.');

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
            $rows[] = $service->ensureForCompany(
                (int) $company->id,
                (string) $company->company_name,
                $dryRun
            );
        }

        $this->table(
            ['company_id', 'company_name', 'action', 'warehouse_id', 'warehouse_name', 'note'],
            array_map(static fn (array $row): array => [
                $row['company_id'],
                $row['company_name'],
                $row['action'],
                $row['warehouse_id'] ?? '—',
                $row['warehouse_name'] ?? '—',
                $row['note'],
            ], $rows)
        );

        $created = count(array_filter($rows, static fn (array $r): bool => $r['action'] === 'created'));
        $setDefault = count(array_filter($rows, static fn (array $r): bool => $r['action'] === 'set_default'));
        $alreadyOk = count(array_filter($rows, static fn (array $r): bool => $r['action'] === 'already_ok'));

        $this->info("Companies processed: {$companies->count()}. created={$created}, set_default={$setDefault}, already_ok={$alreadyOk}.");

        return self::SUCCESS;
    }
}
