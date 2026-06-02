<?php

declare(strict_types=1);

/**
 * One-off heuristic: controllers with no matching route action string.
 * NOT proof of dead code — review FUNC_REPORT/LEGACY_PHP_CODE_CANDIDATES audit.
 */
$routeJsonPath = $argv[1] ?? '';
if ($routeJsonPath === '' || ! is_readable($routeJsonPath)) {
    fwrite(STDERR, "Usage: php audit_orphan_controllers.php <route-list.json>\n");

    exit(1);
}

/** @var list<array<string, mixed>> $routes */
$routes = json_decode((string) file_get_contents($routeJsonPath), true, 512, JSON_THROW_ON_ERROR);

$actionNeedles = [];
foreach ($routes as $route) {
    $action = (string) ($route['action'] ?? '');
    if ($action === '' || $action === 'Closure') {
        continue;
    }
    $actionNeedles[] = $action;
    if (preg_match('/([A-Za-z0-9_\\\\]+Controller)/', $action, $m)) {
        $actionNeedles[] = $m[1];
        $normalized = str_replace('\\\\', '\\', $m[1]);
        $actionNeedles[] = substr($normalized, (int) strrpos($normalized, '\\') + 1);
    }
}

function scanControllers(string $base): array
{
    $files = [];
    if (! is_dir($base)) {
        return $files;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        $path = $file->getPathname();
        if (str_ends_with($path, 'Controller.php')) {
            $files[] = $path;
        }
    }

    return $files;
}

$files = scanControllers(__DIR__.'/../app/Http/Controllers');
foreach (glob(__DIR__.'/../Modules/*/Http/Controllers', GLOB_ONLYDIR) ?: [] as $modControllers) {
    $files = array_merge($files, scanControllers($modControllers));
}

$orphan = [];
foreach ($files as $path) {
    $content = (string) file_get_contents($path);
    if (! preg_match('/class\s+(\w+)/', $content, $cm)) {
        continue;
    }
    $class = $cm[1];
    $fq = $class;
    if (preg_match('/namespace\s+([^;]+);/', $content, $nm)) {
        $fq = trim($nm[1]).'\\'.$class;
    }
    $hit = false;
    foreach ($actionNeedles as $act) {
        if (str_contains($act, $class) || str_contains($act, $fq)) {
            $hit = true;
            break;
        }
    }
    if (! $hit) {
        $orphan[] = str_replace('\\', '/', $path);
    }
}

echo 'Routes: '.count($routes)."\n";
echo 'Controllers scanned: '.count($files)."\n";
echo 'Possibly unreferenced (heuristic): '.count($orphan)."\n\n";
foreach ($orphan as $o) {
    echo $o."\n";
}
