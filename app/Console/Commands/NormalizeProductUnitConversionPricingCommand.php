<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeProductUnitConversionPricingCommand extends Command
{
    protected $signature = 'product-unit-conversions:normalize-uom-pricing
                            {--dry-run : Report row counts without writing}
                            {--force : Apply without confirmation (for staging/hub SSH)}';

    protected $description = 'Set all UOM rows for_sale=0; move selling_price into cost_price and clear selling_price (run per environment after migrate)';

    public function handle(): int
    {
        if (! Schema::hasTable('product_unit_conversions')) {
            $this->error('Table product_unit_conversions does not exist on this database.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('product_unit_conversions', 'cost_price')) {
            $this->error('Column cost_price is missing. Run migrations first: php artisan migrate');

            return self::FAILURE;
        }

        $total = (int) DB::table('product_unit_conversions')->count();
        $withSellingPrice = (int) DB::table('product_unit_conversions')
            ->whereNotNull('selling_price')
            ->count();
        $forSaleEnabled = (int) DB::table('product_unit_conversions')
            ->where('for_sale', true)
            ->orWhere('for_sale', 1)
            ->count();

        $this->info('Environment: ' . config('app.env'));
        $this->info('Database: ' . config('database.connections.' . config('database.default') . '.database'));
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total UOM rows', (string) $total],
                ['Rows with selling_price set', (string) $withSellingPrice],
                ['Rows with for_sale = 1', (string) $forSaleEnabled],
            ]
        );

        if ($total === 0) {
            $this->warn('Nothing to update.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — no changes written.');
            $this->line('Would run: for_sale=0 on all rows; cost_price=COALESCE(selling_price,cost_price); selling_price=NULL.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apply updates to ALL product_unit_conversions rows on this database?', true)) {
            $this->warn('Aborted.');

            return self::SUCCESS;
        }

        $affected = DB::update('
            UPDATE product_unit_conversions
            SET cost_price = COALESCE(selling_price, cost_price),
                selling_price = NULL,
                for_sale = 0
        ');

        $remainingSelling = (int) DB::table('product_unit_conversions')
            ->whereNotNull('selling_price')
            ->count();
        $remainingForSale = (int) DB::table('product_unit_conversions')
            ->where('for_sale', true)
            ->orWhere('for_sale', 1)
            ->count();

        $this->info("Updated {$affected} row(s).");
        $this->table(
            ['After', 'Count'],
            [
                ['Rows with selling_price still set', (string) $remainingSelling],
                ['Rows with for_sale = 1', (string) $remainingForSale],
            ]
        );

        if ($remainingSelling > 0 || $remainingForSale > 0) {
            $this->error('Verification failed — re-check the table.');

            return self::FAILURE;
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
