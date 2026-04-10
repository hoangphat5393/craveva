<?php

/**
 * Audit migration files vs `migrations` table: duplicate filenames, orphan DB rows, pending files.
 *
 * Usage: php database/scripts/audit_migrations_registry.php
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Finder;

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$root = realpath(__DIR__ . '/../..');
if ($root === false) {
    fwrite(STDERR, "Cannot resolve project root.\n");
    exit(1);
}

$paths = [
    $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations',
];

foreach (glob($root . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations', GLOB_ONLYDIR) ?: [] as $moduleMigrations) {
    $paths[] = $moduleMigrations;
}

/** @var array<string, list<string>> migration name (no .php) => list of absolute paths */
$basenameToFiles = [];
foreach ($paths as $dir) {
    if (! is_dir($dir)) {
        continue;
    }
    $finder = Finder::create()->files()->in($dir)->name('*.php')->depth(0);
    foreach ($finder as $file) {
        $base = $file->getFilename();
        $migrationName = str_ends_with($base, '.php') ? substr($base, 0, -4) : $base;
        $basenameToFiles[$migrationName] ??= [];
        $basenameToFiles[$migrationName][] = $file->getPathname();
    }
}

$duplicates = array_filter($basenameToFiles, fn(array $files): bool => count($files) > 1);

$dbNames = DB::table('migrations')->orderBy('id')->pluck('migration')->all();
$dbSet = array_flip($dbNames);

$diskNames = array_keys($basenameToFiles);
$diskSet = array_flip($diskNames);

$inDbNotOnDisk = array_values(array_filter($dbNames, fn(string $n): bool => ! isset($diskSet[$n])));
$onDiskNotInDb = array_values(array_filter($diskNames, fn(string $n): bool => ! isset($dbSet[$n])));

$exit = 0;
fwrite(STDOUT, "=== Migration registry audit ===\n");
fwrite(STDOUT, 'DB connection: ' . config('database.default') . "\n");
fwrite(STDOUT, 'Rows in `migrations` table: ' . count($dbNames) . "\n");
fwrite(STDOUT, 'Migration files found: ' . count($diskNames) . "\n\n");

if ($duplicates !== []) {
    $exit = 1;
    fwrite(STDOUT, "[FAIL] Duplicate migration filenames (same name in multiple paths — Laravel only stores basename in DB):\n");
    foreach ($duplicates as $base => $files) {
        fwrite(STDOUT, "  {$base}\n");
        foreach ($files as $path) {
            fwrite(STDOUT, '    - ' . str_replace($root . DIRECTORY_SEPARATOR, '', $path) . "\n");
        }
    }
    fwrite(STDOUT, "\n");
} else {
    fwrite(STDOUT, "[OK] No duplicate migration basenames across scanned paths.\n\n");
}

if ($inDbNotOnDisk !== []) {
    fwrite(STDOUT, '[INFO] In DB but no matching file on disk (' . count($inDbNotOnDisk) . ") — historical SaaS / removed files; normal for long-lived apps. Rollback of these names is not possible from this repo snapshot.\n");
    foreach (array_slice($inDbNotOnDisk, 0, 15) as $name) {
        fwrite(STDOUT, "  - {$name}\n");
    }
    if (count($inDbNotOnDisk) > 15) {
        fwrite(STDOUT, '  ... and ' . (count($inDbNotOnDisk) - 15) . " more (omit full list)\n");
    }
    fwrite(STDOUT, "\n");
} else {
    fwrite(STDOUT, "[OK] Every DB migration name has a file on disk.\n\n");
}

if ($onDiskNotInDb !== []) {
    fwrite(STDOUT, '[INFO] On disk but not in DB (' . count($onDiskNotInDb) . ") — pending `php artisan migrate`:\n");
    foreach (array_slice($onDiskNotInDb, 0, 30) as $name) {
        fwrite(STDOUT, "  - {$name}\n");
    }
    if (count($onDiskNotInDb) > 30) {
        fwrite(STDOUT, '  ... and ' . (count($onDiskNotInDb) - 30) . " more\n");
    }
    fwrite(STDOUT, "\n");
} else {
    fwrite(STDOUT, "[OK] All on-disk migrations are recorded in DB.\n\n");
}

// Batch monotonicity: same batch should not run "later" filename before "earlier" in sort order (heuristic).
$batches = DB::table('migrations')->select(['batch', 'migration'])->orderBy('batch')->orderBy('migration')->get()->groupBy('batch');
$batchIssues = [];
foreach ($batches as $batch => $rows) {
    $names = $rows->pluck('migration')->all();
    $sorted = $names;
    sort($sorted, SORT_STRING);
    if ($names !== $sorted) {
        $batchIssues[(int) $batch] = $names;
    }
}
if ($batchIssues !== []) {
    fwrite(STDOUT, '[INFO] Batches where migration names are not lexicographically sorted (unusual but not always wrong): ' . count($batchIssues) . " batch(es).\n\n");
}

fwrite(STDOUT, "Run `php artisan migrate:status` for full Pending/Ran list.\n");
fwrite(STDOUT, "Run `php artisan migrate --pretend` to preview SQL for pending migrations.\n\n");

$criticalTables = ['migrations', 'sales_dos', 'sales_do_items', 'grns', 'grn_items'];
$missingTables = [];
foreach ($criticalTables as $table) {
    if (! Schema::hasTable($table)) {
        $missingTables[] = $table;
    }
}
if ($missingTables !== []) {
    fwrite(STDERR, '[FAIL] Missing expected tables (run migrations or repair DB): ' . implode(', ', $missingTables) . "\n");
    exit(2);
}
fwrite(STDOUT, '[OK] Critical warehouse / Sales DO / GRN tables exist: ' . implode(', ', $criticalTables) . ".\n");

exit($exit);
