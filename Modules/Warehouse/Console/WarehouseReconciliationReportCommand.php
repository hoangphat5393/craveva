<?php

namespace Modules\Warehouse\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Modules\Warehouse\Services\WarehouseReconciliationService;

class WarehouseReconciliationReportCommand extends Command
{
    protected $signature = 'warehouse:reconciliation-report {--date=} {--company_id=}';

    protected $description = 'Generate daily warehouse stock movement reconciliation report';

    public function handle(WarehouseReconciliationService $service): int
    {
        $date = (string) ($this->option('date') ?: now()->toDateString());
        $companyIdOpt = $this->option('company_id');
        $companyId = $companyIdOpt !== null ? (int) $companyIdOpt : null;

        $summary = $service->generateDailySummary($date, $companyId);

        $dir = storage_path('app/warehouse-reconciliation');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $suffix = $companyId ? ('-company-' . $companyId) : '-all';
        $path = $dir . '/warehouse-reconciliation-' . $date . $suffix . '.json';
        File::put($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info('Warehouse reconciliation report generated: ' . $path);
        $this->line('Movements: ' . (string) data_get($summary, 'totals.movements', 0));
        $this->line('Duplicate groups: ' . (string) data_get($summary, 'totals.duplicate_groups', 0));

        return self::SUCCESS;
    }
}
