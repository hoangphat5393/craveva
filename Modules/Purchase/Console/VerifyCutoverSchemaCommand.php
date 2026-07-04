<?php

namespace Modules\Purchase\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Support\GrnRuntime;
use Modules\Purchase\Support\SalesDoRuntime;

/**
 * Sanity check: runtime is pinned to sales_dos + grns; missing tables cause DataTables 1146 errors.
 */
class VerifyCutoverSchemaCommand extends Command
{
    protected $signature = 'purchase:verify-cutover-schema';

    protected $description = 'Verify canonical Sales DO, GRN, and core warehouse tables exist';

    public function handle(): int
    {
        $required = array_values(array_unique(array_merge(
            [
                SalesDoRuntime::headerTable(),
                SalesDoRuntime::itemTable(),
                GrnRuntime::headerTable(),
                GrnRuntime::itemTable(),
            ],
            ['warehouses', 'stock_movements', 'warehouse_product_stock'],
        )));

        $schemaSources = [
            'sales_dos' => 'database/migrations/2000_01_01_000403_create_sales_dos_baseline.php',
            'sales_do_items' => 'database/migrations/2000_01_01_000402_create_sales_do_items_baseline.php',
            'grns' => 'database/migrations/2000_01_01_000149_create_grns_baseline.php',
            'grn_items' => 'database/migrations/2000_01_01_000148_create_grn_items_baseline.php',
            'warehouses' => 'database/migrations/2000_01_01_000491_create_warehouses_baseline.php',
            'stock_movements' => 'database/migrations/2000_01_01_000426_create_stock_movements_baseline.php',
            'warehouse_product_stock' => 'database/migrations/2000_01_01_000489_create_warehouse_product_stock_baseline.php',
        ];

        $missing = collect($required)->filter(fn (string $table) => ! Schema::hasTable($table))->values()->all();

        if ($missing === []) {
            $this->info('OK: all required cutover / warehouse tables exist.');
            $this->line('Checked: '.implode(', ', $required));

            return self::SUCCESS;
        }

        $this->error('Missing table(s): '.implode(', ', $missing));
        foreach ($missing as $table) {
            $source = $schemaSources[$table] ?? '(see migrate:status)';
            $this->line("  - {$table} <- schema source: {$source}");
        }
        $this->newLine();
        $this->warn('Inspect with `php artisan migrate:status` before changing the database.');
        $this->line('Fresh database: rebuild from the current baseline migrations and seed data.');
        $this->line('Existing database: restore a validated backup or add a reviewed forward repair migration.');
        $this->line('Do not delete rows from the `migrations` table to force a baseline migration to rerun.');

        return self::FAILURE;
    }
}
