<?php

use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('leads');
echo "Columns for 'leads' table:\n";
print_r($columns);

$columnsDeals = Schema::getColumnListing('deals');
echo "\nColumns for 'deals' table:\n";
print_r($columnsDeals);
