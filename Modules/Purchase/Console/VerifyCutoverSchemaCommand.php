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

    protected $description = 'Verify Sales DO + GRN cutover tables (and core warehouse tables) exist';

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

        $hints = [
            'sales_dos' => '2026_03_30_190000_create_sales_do_tables',
            'sales_do_items' => '2026_03_30_190000_create_sales_do_tables',
            'grns' => '2026_03_30_191000_create_grn_tables',
            'grn_items' => '2026_03_30_191000_create_grn_tables',
            'warehouses' => 'Modules/Warehouse Database 2026_01_19_083640_create_warehouses_table',
            'stock_movements' => '2026_01_16_000002_create_stock_movements_table_fb',
            'warehouse_product_stock' => '2026_01_19_083641_create_warehouse_product_stock_table',
        ];

        $missing = collect($required)->filter(fn(string $table) => ! Schema::hasTable($table))->values()->all();

        if ($missing === []) {
            $this->info('OK: all required cutover / warehouse tables exist.');
            $this->line('Checked: ' . implode(', ', $required));

            return self::SUCCESS;
        }

        $this->error('Missing table(s): ' . implode(', ', $missing));
        foreach ($missing as $table) {
            $hint = $hints[$table] ?? '(see migrate:status)';
            $this->line("  - {$table} ← migration hint: {$hint}");
        }
        $this->newLine();
        $this->warn('Fix: php artisan migrate (use sudo -u www-data on server).');
        $this->line('If migrate says "Nothing to migrate" but tables are still missing, the `migrations` row may be out of sync:');
        $this->line('  DELETE FROM migrations WHERE migration = \'2026_03_30_190000_create_sales_do_tables\';');
        $this->line('  DELETE FROM migrations WHERE migration = \'2026_03_30_191000_create_grn_tables\';');
        $this->line('then run migrate again (dev DB only — backup production first).');

        return self::FAILURE;
    }
}
