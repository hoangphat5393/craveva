<?php

/**
 * Export non-sensitive seed/reference rows from a verified clean database.
 * One JSON file is generated per non-empty table.
 *
 * Usage:
 * DB_DATABASE=clean_database php database/scripts/generate_fresh_seed_data.php \
 *   --output=database/seeders/data/full_YYYYMMDD \
 *   --exclude=users,user_auths
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['output:', 'exclude::', 'connection::', 'profile::']);
$outputOption = str_replace('\\', '/', (string) ($options['output'] ?? ''));
$connectionName = (string) ($options['connection'] ?? config('database.default'));
$profile = (string) ($options['profile'] ?? 'full');
$excludedTables = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) ($options['exclude'] ?? 'users,user_auths'))
)));

if ($outputOption === '') {
    fwrite(STDERR, "Missing required --output option.\n");
    exit(1);
}

$allowedPrefixes = ['database/seeders/data/'];
$isAllowedOutput = false;
foreach ($allowedPrefixes as $allowedPrefix) {
    $isAllowedOutput = $isAllowedOutput || str_starts_with($outputOption, $allowedPrefix);
}
if (! $isAllowedOutput || str_contains($outputOption, '..')) {
    fwrite(STDERR, "Output must be under database/seeders/data.\n");
    exit(1);
}

$projectRoot = realpath(__DIR__.'/../..');
if ($projectRoot === false) {
    fwrite(STDERR, "Cannot resolve project root.\n");
    exit(1);
}

$outputPath = $projectRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $outputOption);
if (is_dir($outputPath) && iterator_count(new FilesystemIterator($outputPath, FilesystemIterator::SKIP_DOTS)) > 0) {
    fwrite(STDERR, "Output directory is not empty; refusing to overwrite: {$outputOption}\n");
    exit(1);
}

if (! is_dir($outputPath) && ! mkdir($outputPath, 0755, true) && ! is_dir($outputPath)) {
    fwrite(STDERR, "Cannot create output directory: {$outputOption}\n");
    exit(1);
}

/** @var ConnectionInterface $connection */
$connection = app('db')->connection($connectionName);
if ($connection->getDriverName() !== 'mysql') {
    fwrite(STDERR, "Only MySQL connections are supported.\n");
    exit(1);
}

$database = $connection->getDatabaseName();
$tableRows = $connection->select(
    "SELECT TABLE_NAME
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = ?
       AND TABLE_TYPE = 'BASE TABLE'
       AND TABLE_NAME <> 'migrations'
     ORDER BY TABLE_NAME",
    [$database]
);

$sensitiveColumns = $connection->select(
    "SELECT TABLE_NAME, COLUMN_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = ?
       AND TABLE_NAME <> 'migrations'
       AND COLUMN_NAME REGEXP '(password|secret|token|api.?key|private.?key|purchase.?code|credential)'
     ORDER BY TABLE_NAME, COLUMN_NAME",
    [$database]
);

foreach ($sensitiveColumns as $columnRow) {
    $table = (string) $columnRow->TABLE_NAME;
    $column = (string) $columnRow->COLUMN_NAME;
    if (in_array($table, $excludedTables, true)) {
        continue;
    }

    $wrapped = $connection->getQueryGrammar()->wrap($column);
    $count = $connection->table($table)
        ->whereNotNull($column)
        ->whereRaw("CAST({$wrapped} AS CHAR) <> ''")
        ->count();

    if ($count > 0) {
        fwrite(STDERR, "Refusing to export non-empty sensitive column {$table}.{$column}.\n");
        exit(2);
    }
}

$manifestTables = [];
$sequence = 1;
$totalRows = 0;
$dataHashContext = hash_init('sha256');

foreach ($tableRows as $tableRow) {
    $table = (string) $tableRow->TABLE_NAME;
    if (in_array($table, $excludedTables, true)) {
        continue;
    }

    $columnRows = $connection->select(
        'SELECT COLUMN_NAME, EXTRA
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
         ORDER BY ORDINAL_POSITION',
        [$database, $table]
    );
    $columns = [];
    foreach ($columnRows as $columnRow) {
        if (! str_contains(strtoupper((string) $columnRow->EXTRA), 'GENERATED')) {
            $columns[] = (string) $columnRow->COLUMN_NAME;
        }
    }

    $query = $connection->table($table)->select($columns);
    $primaryKeyRows = $connection->select(
        "SELECT COLUMN_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'
         ORDER BY ORDINAL_POSITION",
        [$database, $table]
    );
    foreach ($primaryKeyRows as $primaryKeyRow) {
        $query->orderBy((string) $primaryKeyRow->COLUMN_NAME);
    }

    $rows = $query->get()->map(static fn (object $row): array => (array) $row)->all();
    if ($rows === []) {
        continue;
    }

    $payload = [
        'table' => $table,
        'columns' => $columns,
        'rows' => $rows,
    ];
    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    ).PHP_EOL;

    $slug = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($table)) ?: 'table';
    $filename = sprintf('%04d_%s.json', $sequence, $slug);
    file_put_contents($outputPath.DIRECTORY_SEPARATOR.$filename, $json);
    hash_update($dataHashContext, $filename."\n".$json);

    $rowCount = count($rows);
    $manifestTables[$table] = ['file' => $filename, 'rows' => $rowCount];
    $totalRows += $rowCount;
    $sequence++;
}

$manifest = [
    'generated_at' => date(DATE_ATOM),
    'profile' => $profile,
    'source_database' => $database,
    'excluded_tables' => $excludedTables,
    'table_count' => count($manifestTables),
    'row_count' => $totalRows,
    'data_sha256' => hash_final($dataHashContext),
    'tables' => $manifestTables,
];

file_put_contents(
    $outputPath.DIRECTORY_SEPARATOR.'_manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL
);

file_put_contents($outputPath.DIRECTORY_SEPARATOR.'_README.md', <<<MD
# Consolidated reference data: {$profile}

Generated from a clean migration replay followed by the production installer
seeders. User accounts and authentication credentials are excluded.

Import only after the matching consolidated schema has been migrated:

```bash
php database/scripts/import_fresh_seed_data.php --input={$outputOption}
```

The importer refuses non-empty target tables and verifies the manifest checksum
and imported rows. Do not import this data into an existing installation.
MD);

fwrite(STDOUT, 'Generated '.count($manifestTables)." seed files with {$totalRows} rows.\n");
fwrite(STDOUT, 'Data SHA-256: '.$manifest['data_sha256']."\n");
