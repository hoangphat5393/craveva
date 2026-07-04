<?php

/**
 * Generate a MySQL-only fresh-install migration set with one file per table.
 *
 * The source database must already be a verified clean installation. This
 * script exports schema only; it never exports table rows.
 *
 * Usage:
 * DB_DATABASE=clean_database php database/scripts/generate_fresh_table_migrations.php \
 *   --output=database/migration-build/full_YYYYMMDD \
 *   --profile=full
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\ConnectionInterface;

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', ['output:', 'connection::', 'profile::']);
$outputOption = str_replace('\\', '/', (string) ($options['output'] ?? ''));
$connectionName = (string) ($options['connection'] ?? config('database.default'));
$profile = (string) ($options['profile'] ?? 'full');

if ($outputOption === '') {
    fwrite(STDERR, "Missing required --output option.\n");
    exit(1);
}

$allowedPrefixes = ['database/migration-build/'];
$isAllowedOutput = false;
foreach ($allowedPrefixes as $allowedPrefix) {
    $isAllowedOutput = $isAllowedOutput || str_starts_with($outputOption, $allowedPrefix);
}
if (! $isAllowedOutput || str_contains($outputOption, '..')) {
    fwrite(STDERR, "Output must be under database/migration-build.\n");
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

$sourceDatabase = $connection->getDatabaseName();
$tables = $connection->select(
    "SELECT TABLE_NAME
     FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = ?
       AND TABLE_TYPE = 'BASE TABLE'
       AND TABLE_NAME <> 'migrations'
     ORDER BY TABLE_NAME",
    [$sourceDatabase]
);

$migrationRows = $connection->table('migrations')->count();
$schemaHashContext = hash_init('sha256');
$generatedFiles = [];

$prepareFile = '2000_01_01_000000_prepare_fresh_schema.php';
file_put_contents($outputPath.DIRECTORY_SEPARATOR.$prepareFile, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::enableForeignKeyConstraints();
    }
};
PHP);
$generatedFiles[] = $prepareFile;

$tableTemplate = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
{{CREATE_SQL}}
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists({{TABLE_LITERAL}});
    }
};
PHP;

$sequence = 1;
foreach ($tables as $tableRow) {
    $table = (string) $tableRow->TABLE_NAME;
    $wrappedTable = $connection->getQueryGrammar()->wrapTable($table);
    $createRow = (array) $connection->selectOne("SHOW CREATE TABLE {$wrappedTable}");
    $createSql = (string) ($createRow['Create Table'] ?? '');

    if ($createSql === '') {
        fwrite(STDERR, "Cannot read CREATE TABLE for {$table}.\n");
        exit(1);
    }

    // Do not carry environment-specific row counters into a clean install.
    $createSql = preg_replace('/\sAUTO_INCREMENT=\d+\b/i', '', $createSql) ?? $createSql;
    $createSql .= ';';
    hash_update($schemaHashContext, $table."\n".$createSql."\n");

    $slug = preg_replace('/[^a-z0-9_]+/i', '_', strtolower($table)) ?: 'table';
    $filename = sprintf('2000_01_01_%06d_create_%s_baseline.php', $sequence, $slug);
    $contents = str_replace(
        ['{{CREATE_SQL}}', '{{TABLE_LITERAL}}'],
        [$createSql, var_export($table, true)],
        $tableTemplate
    );

    file_put_contents($outputPath.DIRECTORY_SEPARATOR.$filename, $contents);
    $generatedFiles[] = $filename;
    $sequence++;
}

$finalizeFile = sprintf('2000_01_01_%06d_finalize_fresh_schema.php', $sequence);
file_put_contents($outputPath.DIRECTORY_SEPARATOR.$finalizeFile, <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
    }
};
PHP);
$generatedFiles[] = $finalizeFile;

$manifest = [
    'generated_at' => date(DATE_ATOM),
    'profile' => $profile,
    'connection' => $connectionName,
    'source_database' => $sourceDatabase,
    'source_migration_rows' => $migrationRows,
    'table_count' => count($tables),
    'migration_file_count' => count($generatedFiles),
    'schema_sha256' => hash_final($schemaHashContext),
    'data_exported' => false,
];

file_put_contents(
    $outputPath.DIRECTORY_SEPARATOR.'_manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
);

$seedOption = 'database/seeders/data/'.basename($outputOption);
file_put_contents($outputPath.DIRECTORY_SEPARATOR.'_README.md', <<<MD
# Consolidated migration profile: {$profile}

Generated from a verified clean MySQL database. Each table has one baseline
migration containing its final schema. No table rows are exported.

Run only on a new empty database:

```bash
php artisan migrate --force --path={$outputOption} --schema-path="do not run schema path"
php database/scripts/import_fresh_seed_data.php --input={$seedOption}
php artisan fresh-install:create-superadmin your-admin@example.com
```

Do not combine this path with `database/migrations` or module migration paths.
Do not use this baseline to upgrade an existing installation.
MD);

fwrite(STDOUT, 'Generated '.count($tables)." table migrations in {$outputOption}.\n");
fwrite(STDOUT, 'Schema SHA-256: '.$manifest['schema_sha256']."\n");
