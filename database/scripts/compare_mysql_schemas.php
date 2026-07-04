<?php

/**
 * Compare table definitions in two MySQL databases on the configured server.
 * This script is read-only and ignores the Laravel migrations table.
 *
 * Usage:
 * php database/scripts/compare_mysql_schemas.php --source=db_one --target=db_two
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['source:', 'target:', 'connection::', 'show-first-diff', 'compare-counts']);
$sourceDatabase = (string) ($options['source'] ?? '');
$targetDatabase = (string) ($options['target'] ?? '');
$baseConnection = (string) ($options['connection'] ?? config('database.default'));

foreach ([$sourceDatabase, $targetDatabase] as $database) {
    if (! preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
        fwrite(STDERR, "Source and target database names are required and must be safe identifiers.\n");
        exit(1);
    }
}

$baseConfig = config("database.connections.{$baseConnection}");
if (! is_array($baseConfig) || ($baseConfig['driver'] ?? null) !== 'mysql') {
    fwrite(STDERR, "The selected base connection must be MySQL.\n");
    exit(1);
}

config([
    'database.connections.schema_compare_source' => array_merge($baseConfig, ['database' => $sourceDatabase]),
    'database.connections.schema_compare_target' => array_merge($baseConfig, ['database' => $targetDatabase]),
]);
DB::purge('schema_compare_source');
DB::purge('schema_compare_target');

/** @var ConnectionInterface $source */
$source = DB::connection('schema_compare_source');
/** @var ConnectionInterface $target */
$target = DB::connection('schema_compare_target');

$readSchemas = static function (ConnectionInterface $connection): array {
    $database = $connection->getDatabaseName();
    $tableRows = $connection->select(
        "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, ROW_FORMAT, CREATE_OPTIONS, TABLE_COMMENT
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = ?
           AND TABLE_TYPE = 'BASE TABLE'
           AND TABLE_NAME <> 'migrations'
         ORDER BY TABLE_NAME",
        [$database]
    );

    $columnRows = $connection->select(
        "SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE,
                DATA_TYPE, COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_KEY,
                EXTRA, GENERATION_EXPRESSION
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME <> 'migrations'
         ORDER BY TABLE_NAME, ORDINAL_POSITION",
        [$database]
    );

    $indexRows = $connection->select(
        "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME, COLLATION,
                SUB_PART, INDEX_TYPE, NULLABLE, EXPRESSION
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME <> 'migrations'
         ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX",
        [$database]
    );

    $foreignKeyRows = $connection->select(
        "SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME, k.ORDINAL_POSITION,
                k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                r.UPDATE_RULE, r.DELETE_RULE
         FROM information_schema.KEY_COLUMN_USAGE k
         JOIN information_schema.REFERENTIAL_CONSTRAINTS r
           ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
          AND r.TABLE_NAME = k.TABLE_NAME
          AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
         WHERE k.TABLE_SCHEMA = ?
           AND k.REFERENCED_TABLE_NAME IS NOT NULL
           AND k.TABLE_NAME <> 'migrations'
         ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION",
        [$database]
    );

    $checkRows = $connection->select(
        "SELECT tc.TABLE_NAME, tc.CONSTRAINT_NAME, cc.CHECK_CLAUSE
         FROM information_schema.TABLE_CONSTRAINTS tc
         JOIN information_schema.CHECK_CONSTRAINTS cc
           ON cc.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
          AND cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
         WHERE tc.TABLE_SCHEMA = ?
           AND tc.CONSTRAINT_TYPE = 'CHECK'
           AND tc.TABLE_NAME <> 'migrations'
         ORDER BY tc.TABLE_NAME, tc.CONSTRAINT_NAME",
        [$database]
    );

    $schemas = [];
    foreach ($tableRows as $row) {
        $table = (string) $row->TABLE_NAME;
        $schemas[$table] = [
            'table' => array_change_key_case((array) $row, CASE_LOWER),
            'columns' => [],
            'indexes' => [],
            'foreign_keys' => [],
            'checks' => [],
        ];
    }

    foreach ([
        'columns' => $columnRows,
        'indexes' => $indexRows,
        'foreign_keys' => $foreignKeyRows,
        'checks' => $checkRows,
    ] as $section => $rows) {
        foreach ($rows as $row) {
            $data = array_change_key_case((array) $row, CASE_LOWER);
            $table = (string) $data['table_name'];
            unset($data['table_name']);
            $schemas[$table][$section][] = $data;
        }
    }

    return $schemas;
};

$sourceSchemas = $readSchemas($source);
$targetSchemas = $readSchemas($target);
$sourceTables = array_keys($sourceSchemas);
$targetTables = array_keys($targetSchemas);
$missingInTarget = array_values(array_diff($sourceTables, $targetTables));
$extraInTarget = array_values(array_diff($targetTables, $sourceTables));
$changed = [];

foreach (array_intersect($sourceTables, $targetTables) as $table) {
    if (json_encode($sourceSchemas[$table]) !== json_encode($targetSchemas[$table])) {
        $changed[] = $table;
    }
}

fwrite(STDOUT, 'Source tables: '.count($sourceTables)."\n");
fwrite(STDOUT, 'Target tables: '.count($targetTables)."\n");
fwrite(STDOUT, 'Missing in target: '.count($missingInTarget)."\n");
fwrite(STDOUT, 'Extra in target: '.count($extraInTarget)."\n");
fwrite(STDOUT, 'Changed definitions: '.count($changed)."\n");

foreach ([
    'Missing in target' => $missingInTarget,
    'Extra in target' => $extraInTarget,
    'Changed definitions' => $changed,
] as $label => $items) {
    if ($items !== []) {
        fwrite(STDOUT, "{$label}: ".implode(', ', array_slice($items, 0, 30))."\n");
    }
}

if ($missingInTarget !== [] || $extraInTarget !== [] || $changed !== []) {
    if (isset($options['show-first-diff']) && $changed !== []) {
        $table = $changed[0];
        fwrite(STDOUT, "\n--- SOURCE {$table} ---\n".json_encode($sourceSchemas[$table], JSON_PRETTY_PRINT)."\n");
        fwrite(STDOUT, "\n--- TARGET {$table} ---\n".json_encode($targetSchemas[$table], JSON_PRETTY_PRINT)."\n");
    }
    exit(2);
}

fwrite(STDOUT, "[OK] Schema definitions are identical.\n");

if (isset($options['compare-counts'])) {
    $countDifferences = [];
    foreach ($sourceTables as $table) {
        $sourceCount = $source->table($table)->count();
        $targetCount = $target->table($table)->count();
        if ($sourceCount !== $targetCount) {
            $countDifferences[$table] = [
                'source' => $sourceCount,
                'target' => $targetCount,
            ];
        }
    }

    fwrite(STDOUT, 'Row-count differences: '.count($countDifferences)."\n");
    foreach (array_slice($countDifferences, 0, 50, true) as $table => $counts) {
        fwrite(STDOUT, "  {$table}: source={$counts['source']}, target={$counts['target']}\n");
    }

    if ($countDifferences !== []) {
        exit(3);
    }

    fwrite(STDOUT, "[OK] Row counts are identical after seeding.\n");
}
