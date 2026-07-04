<?php

/**
 * Import per-table JSON seed files into an empty fresh-install schema.
 *
 * Usage:
 * DB_DATABASE=target_database php database/scripts/import_fresh_seed_data.php \
 *   --input=database/seeders/data/full_YYYYMMDD
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['input:', 'connection::']);
$inputOption = str_replace('\\', '/', (string) ($options['input'] ?? ''));
$connectionName = (string) ($options['connection'] ?? config('database.default'));

$allowedPrefixes = ['database/seeders/data/'];
$isAllowedInput = false;
foreach ($allowedPrefixes as $allowedPrefix) {
    $isAllowedInput = $isAllowedInput || str_starts_with($inputOption, $allowedPrefix);
}
if ($inputOption === '' || ! $isAllowedInput || str_contains($inputOption, '..')) {
    fwrite(STDERR, "Input must be under database/seeders/data.\n");
    exit(1);
}

$projectRoot = realpath(__DIR__.'/../..');
$inputPath = $projectRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $inputOption);
if (! is_dir($inputPath) || ! is_file($inputPath.DIRECTORY_SEPARATOR.'_manifest.json')) {
    fwrite(STDERR, "Seed directory or manifest not found: {$inputOption}\n");
    exit(1);
}

/** @var ConnectionInterface $connection */
$connection = app('db')->connection($connectionName);
if ($connection->getDriverName() !== 'mysql') {
    fwrite(STDERR, "Only MySQL connections are supported.\n");
    exit(1);
}

$files = glob($inputPath.DIRECTORY_SEPARATOR.'[0-9][0-9][0-9][0-9]_*.json') ?: [];
sort($files, SORT_STRING);
$manifest = json_decode(
    file_get_contents($inputPath.DIRECTORY_SEPARATOR.'_manifest.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$expectedFileCount = (int) ($manifest['table_count'] ?? -1);
$expectedRowCount = (int) ($manifest['row_count'] ?? -1);
$expectedHash = (string) ($manifest['data_sha256'] ?? '');

if ($expectedFileCount !== count($files) || $expectedHash === '') {
    fwrite(STDERR, "Seed manifest does not match the files in the directory.\n");
    exit(1);
}

$hashContext = hash_init('sha256');
foreach ($files as $file) {
    hash_update($hashContext, basename($file)."\n".file_get_contents($file));
}
if (! hash_equals($expectedHash, hash_final($hashContext))) {
    fwrite(STDERR, "Seed checksum verification failed.\n");
    exit(1);
}

$importedRows = 0;

$connection->statement('SET FOREIGN_KEY_CHECKS=0');
try {
    foreach ($files as $file) {
        $payload = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        $table = (string) ($payload['table'] ?? '');
        $columns = $payload['columns'] ?? null;
        $rows = $payload['rows'] ?? null;
        if ($table === '' || ! is_array($columns) || ! is_array($rows)) {
            throw new RuntimeException('Invalid seed file: '.basename($file));
        }

        if (! $connection->getSchemaBuilder()->hasTable($table)) {
            throw new RuntimeException("Target table does not exist: {$table}");
        }
        if ($connection->table($table)->exists()) {
            throw new RuntimeException("Target table is not empty: {$table}");
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            $connection->table($table)->insert($chunk);
            $importedRows += count($chunk);
        }

        $actualRows = $connection->table($table)
            ->select($columns)
            ->get()
            ->map(static fn (object $row): array => (array) $row)
            ->all();
        $canonicalize = static function (array $items): array {
            $encoded = array_map(
                static fn (array $row): string => json_encode(
                    $row,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
                $items
            );
            sort($encoded, SORT_STRING);

            return $encoded;
        };
        if ($canonicalize($rows) !== $canonicalize($actualRows)) {
            throw new RuntimeException("Imported data verification failed for table: {$table}");
        }
    }
} finally {
    $connection->statement('SET FOREIGN_KEY_CHECKS=1');
}

if ($importedRows !== $expectedRowCount) {
    fwrite(STDERR, "Imported row count does not match the manifest.\n");
    exit(1);
}

fwrite(STDOUT, 'Imported and verified '.count($files)." seed files with {$importedRows} rows.\n");
