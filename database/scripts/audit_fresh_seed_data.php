<?php

/**
 * Audit non-empty seed data and potentially sensitive values in a clean DB.
 * Values are never printed; only table/column names and row counts are shown.
 *
 * Usage:
 * DB_DATABASE=clean_database php database/scripts/audit_fresh_seed_data.php
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$connectionName = (string) config('database.default');
/** @var ConnectionInterface $connection */
$connection = app('db')->connection($connectionName);
if ($connection->getDriverName() !== 'mysql') {
    fwrite(STDERR, "Only MySQL connections are supported.\n");
    exit(1);
}

$database = $connection->getDatabaseName();
$tables = $connection->select(
    "SELECT TABLE_NAME
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = ?
       AND TABLE_TYPE = 'BASE TABLE'
       AND TABLE_NAME <> 'migrations'
     ORDER BY TABLE_NAME",
    [$database]
);

$nonEmpty = [];
$totalRows = 0;
foreach ($tables as $tableRow) {
    $table = (string) $tableRow->TABLE_NAME;
    $count = $connection->table($table)->count();
    if ($count > 0) {
        $nonEmpty[$table] = $count;
        $totalRows += $count;
    }
}

$sensitiveColumns = $connection->select(
    "SELECT TABLE_NAME, COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ?
       AND TABLE_NAME <> 'migrations'
       AND COLUMN_NAME REGEXP '(password|secret|token|api.?key|private.?key|purchase.?code|credential)'
     ORDER BY TABLE_NAME, COLUMN_NAME",
    [$database]
);

$nonEmptySensitive = [];
foreach ($sensitiveColumns as $columnRow) {
    $table = (string) $columnRow->TABLE_NAME;
    $column = (string) $columnRow->COLUMN_NAME;
    $wrappedColumn = $connection->getQueryGrammar()->wrap($column);
    $count = $connection->table($table)
        ->whereNotNull($column)
        ->whereRaw("CAST({$wrappedColumn} AS CHAR) <> ''")
        ->count();

    if ($count > 0) {
        $nonEmptySensitive["{$table}.{$column}"] = $count;
    }
}

fwrite(STDOUT, 'Tables with rows: '.count($nonEmpty)."\n");
fwrite(STDOUT, "Total rows across non-empty tables: {$totalRows}\n");
foreach ($nonEmpty as $table => $count) {
    fwrite(STDOUT, "  {$table}: {$count}\n");
}

fwrite(STDOUT, 'Potentially sensitive non-empty columns: '.count($nonEmptySensitive)."\n");
foreach ($nonEmptySensitive as $column => $count) {
    fwrite(STDOUT, "  {$column}: {$count} row(s)\n");
}

if ($nonEmptySensitive !== []) {
    exit(2);
}
