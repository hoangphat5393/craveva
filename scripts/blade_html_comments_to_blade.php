<?php

declare(strict_types=1);

/**
 * Convert standalone HTML comments in Blade files to Blade comments (not sent to the browser).
 *
 *   <!-- SETTINGS SEARCH START -->  →  {{-- SETTINGS SEARCH START --}}
 *
 * Inner text is trimmed; by default it is uppercased (Worksuite-style section markers).
 *
 * Usage:
 *   php scripts/blade_html_comments_to_blade.php --dry-run
 *   php scripts/blade_html_comments_to_blade.php
 *   php scripts/blade_html_comments_to_blade.php --path=resources/views/components
 *   php scripts/blade_html_comments_to_blade.php --path=Modules/Purchase/Resources/views --no-upper
 *
 * Options:
 *   --dry-run       Print changes without writing files
 *   --path=<dir>    Root to scan (default: resources/views and each Modules/.../Resources/views)
 *   --no-upper      Keep comment inner text casing as-is (only trim)
 *   --help          Show this help
 */

$projectRoot = dirname(__DIR__);

$options = parseArguments($argv);

if ($options['help']) {
    echo "blade_html_comments_to_blade.php — use --dry-run first. See script docblock.\n";
    exit(0);
}

$files = collectBladeFiles($projectRoot, $options['path']);

$files = array_values(array_unique($files));
sort($files);

$changedFiles = 0;
$changedLines = 0;

foreach ($files as $filePath) {
    $original = file_get_contents($filePath);
    if ($original === false) {
        fwrite(STDERR, "Could not read: {$filePath}\n");

        continue;
    }

    [$converted, $lineChanges] = convertFile($original, $options['upper']);

    if ($lineChanges === 0) {
        continue;
    }

    $changedFiles++;
    $changedLines += $lineChanges;

    $relative = relativePath($projectRoot, $filePath);
    echo ($options['dry_run'] ? '[dry-run] ' : '') . "{$relative} ({$lineChanges} line(s))\n";

    if (! $options['dry_run']) {
        file_put_contents($filePath, $converted);
    }
}

echo PHP_EOL . sprintf(
    'Done. %d file(s), %d line(s) %s.',
    $changedFiles,
    $changedLines,
    $options['dry_run'] ? 'would change' : 'changed'
) . PHP_EOL;

exit(0);

/**
 * @return array{help: bool, dry_run: bool, upper: bool, path: string|null}
 */
function parseArguments(array $argv): array
{
    $options = [
        'help' => false,
        'dry_run' => false,
        'upper' => true,
        'path' => null,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $options['help'] = true;
        } elseif ($arg === '--dry-run') {
            $options['dry_run'] = true;
        } elseif ($arg === '--no-upper') {
            $options['upper'] = false;
        } elseif (str_starts_with($arg, '--path=')) {
            $options['path'] = substr($arg, strlen('--path='));
        } else {
            fwrite(STDERR, "Unknown option: {$arg}\n");

            exit(1);
        }
    }

    return $options;
}

/**
 * @return list<string>
 */
function collectBladeFiles(string $projectRoot, ?string $path): array
{
    if ($path !== null && $path !== '') {
        $resolved = resolveProjectPath($projectRoot, $path);

        if (is_file($resolved) && str_ends_with($resolved, '.blade.php')) {
            return shouldSkipPath($resolved) ? [] : [$resolved];
        }

        return scanDirectoryForBladeFiles($resolved);
    }

    $files = [];
    foreach (defaultScanRoots($projectRoot) as $root) {
        $files = array_merge($files, scanDirectoryForBladeFiles($root));
    }

    return $files;
}

function resolveProjectPath(string $projectRoot, string $path): string
{
    if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    return $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

/**
 * @return list<string>
 */
function scanDirectoryForBladeFiles(string $root): array
{
    if (! is_dir($root)) {
        fwrite(STDERR, "Skip missing directory: {$root}\n");

        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $pathname = $file->getPathname();
        if (shouldSkipPath($pathname)) {
            continue;
        }

        $files[] = $pathname;
    }

    return $files;
}

/**
 * @return list<string>
 */
function defaultScanRoots(string $projectRoot): array
{
    $roots = [
        $projectRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views',
    ];

    $modulesDir = $projectRoot . DIRECTORY_SEPARATOR . 'Modules';
    if (is_dir($modulesDir)) {
        foreach (scandir($modulesDir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $views = $modulesDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views';
            if (is_dir($views)) {
                $roots[] = $views;
            }
        }
    }

    return array_values($roots);
}

function shouldSkipPath(string $pathname): bool
{
    $normalized = str_replace('\\', '/', $pathname);

    return (bool) preg_match('#/(vendor|node_modules|storage/framework/views)/#', $normalized);
}

function relativePath(string $root, string $path): string
{
    $root = rtrim(str_replace('\\', '/', $root), '/') . '/';
    $path = str_replace('\\', '/', $path);

    return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
}

/**
 * @return array{0: string, 1: int}
 */
function convertFile(string $content, bool $upper): array
{
    $lines = preg_split('/\R/', $content) ?: [];
    $changes = 0;

    foreach ($lines as $index => $line) {
        $converted = convertLine($line, $upper);
        if ($converted !== null) {
            $lines[$index] = $converted;
            $changes++;
        }
    }

    $eol = str_contains($content, "\r\n") ? "\r\n" : "\n";

    return [implode($eol, $lines), $changes];
}

function convertLine(string $line, bool $upper): ?string
{
    if (preg_match('/\{\{--/', $line)) {
        return null;
    }

    if (! preg_match('/^(\s*)<!--(.+?)-->\s*$/', $line, $matches)) {
        return null;
    }

    $inner = trim($matches[2]);

    if ($inner === '' || str_starts_with($inner, '[if')) {
        return null;
    }

    if ($upper) {
        $inner = mb_strtoupper($inner, 'UTF-8');
    }

    return $matches[1] . '{{-- ' . $inner . ' --}}';
}
