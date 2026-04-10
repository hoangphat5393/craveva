<?php

/**
 * One-off: when `migrations` lists sales_do/grn creates as Ran but tables are missing,
 * remove those rows and run `php artisan migrate` to recreate tables.
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$names = [
    '2026_03_30_190000_create_sales_do_tables',
    '2026_03_30_191000_create_grn_tables',
];

foreach ($names as $migration) {
    $deleted = DB::table('migrations')->where('migration', $migration)->delete();
    fwrite(STDOUT, "{$migration}: deleted {$deleted} row(s)\n");
}
