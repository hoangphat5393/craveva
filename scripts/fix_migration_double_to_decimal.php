<?php

/**
 * One-off: Laravel 11 Blueprint::double() chỉ nhận 1 tham số.
 * Thay ->double('col', total, places) -> ->decimal('col', total, places) trong migration.
 */

$root = dirname(__DIR__);
$dirs = [
    $root . '/database/migrations',
    $root . '/Modules',
];

$patterns = [
    '/->double\((\'[^\']+\')\s*,\s*(\d+)\s*,\s*(\d+)\)/' => '->decimal($1, $2, $3)',
    '/->double\((\'[^\']+\')\s*,\s*\[\s*(\d+)\s*,\s*(\d+)\s*]\)/' => '->decimal($1, $2, $3)',
    '/->double\((\'[^\']+\')\s*,\s*(\d+)\)/' => '->decimal($1, $2, 2)',
];

$updated = [];
foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (! $f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $path = $f->getPathname();
        $norm = str_replace('\\', '/', $path);
        // Chỉ file trong .../migrations/ hoặc .../Database/Migrations/
        if (
            ! str_contains($norm, '/database/migrations/')
            && ! str_contains($norm, '/Database/Migrations/')
        ) {
            continue;
        }
        $c = file_get_contents($path);
        $n = $c;
        foreach ($patterns as $re => $replacement) {
            $n = preg_replace($re, $replacement, $n);
        }
        if ($c !== $n) {
            file_put_contents($path, $n);
            $updated[] = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
        }
    }
}

echo count($updated) . " files updated:\n";
foreach ($updated as $p) {
    echo "  - $p\n";
}
