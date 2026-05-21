<?php

/**
 * Build docs/platform-help/00-URL-INDEX.md from _route_dump.json
 *
 * Usage: php docs/platform-help/scripts/build-url-index.php
 */

$root = dirname(__DIR__, 3);
$dumpPath = dirname(__DIR__) . '/_route_dump.json';
$outPath = dirname(__DIR__) . '/00-URL-INDEX.md';

$routes = loadRoutes($root, $dumpPath);
if (! is_array($routes)) {
    fwrite(STDERR, "Could not load routes JSON\n");
    exit(1);
}

/**
 * @return array<int, array<string, mixed>>|null
 */
function loadRoutes(string $root, string $dumpPath): ?array
{
    if (is_file($dumpPath)) {
        $raw = file_get_contents($dumpPath);
        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-16');
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    $cmd = 'php ' . escapeshellarg($root . '/artisan') . ' route:list --path=account --json';
    $output = shell_exec($cmd);
    if (! is_string($output) || $output === '') {
        return null;
    }

    file_put_contents($dumpPath, $output);

    $decoded = json_decode($output, true);

    return is_array($decoded) ? $decoded : null;
}

$excludeNameParts = [
    'quick_action',
    'apply_quick',
    'datatable',
    'widget',
    'export',
    'import',
    'download',
    'searchquery',
    'searchQuery',
    'apply-quick',
    'refresh',
    'change_status',
    'change-status',
    'toggle',
    'archive',
    'duplicate',
];

$screens = [];

foreach ($routes as $r) {
    $method = $r['method'] ?? '';
    if (! str_contains($method, 'GET')) {
        continue;
    }

    $uri = $r['uri'] ?? '';
    $name = $r['name'] ?? '';
    if ($uri === '' || $name === '') {
        continue;
    }

    if (! str_starts_with($uri, 'account')) {
        continue;
    }

    $nameLower = strtolower($name);
    foreach ($excludeNameParts as $part) {
        if (str_contains($nameLower, $part)) {
            continue 2;
        }
    }

    if (! isIndexScreen($name)) {
        continue;
    }

    $path = '/' . $uri;
    $module = guessModule($name, $r['action'] ?? '');
    $folder = guessFolder($name, $path);
    $resource = resourceStem($name);
    $slug = str_replace('.', '-', $resource);
    $docFile = "pages/{$folder}/{$slug}.md";

    $related = relatedRoutesForResource($routes, $resource);

    if (! isset($screens[$resource])) {
        $screens[$resource] = [
            'uri' => $path,
            'name' => $name,
            'module' => $module,
            'doc' => $docFile,
            'phase' => guessPhase($folder),
            'related' => $related,
        ];
    }
}

uksort($screens, fn($a, $b) => strcmp($screens[$a]['uri'], $screens[$b]['uri']));

$lines = [
    '# URL Index — Craveva ERP',
    '',
    'Index of main GET screens under `/account/` (one row per resource `.index`). Regenerate: `php docs/platform-help/scripts/build-url-index.php`.',
    '',
    '| URL | Route name | Module | Doc file | Phase | Status |',
    '|-----|------------|--------|----------|-------|--------|',
];

foreach ($screens as $s) {
    $lines[] = sprintf(
        '| `%s` | `%s` | %s | [%s](%s) | %s | draft |',
        $s['uri'],
        $s['name'],
        $s['module'],
        basename($s['doc']),
        $s['doc'],
        $s['phase']
    );
}

function isIndexScreen(string $name): bool
{
    if (str_ends_with($name, '.index')) {
        return true;
    }

    $standalone = [
        'dashboard',
        'super_admin_dashboard',
        'settings.index',
        'profile-settings.index',
    ];

    return in_array($name, $standalone, true) || str_contains($name, 'dashboard');
}

function resourceStem(string $name): string
{
    if (str_contains($name, '.')) {
        return explode('.', $name)[0];
    }

    return $name;
}

/**
 * @param  array<int, array<string, mixed>>  $routes
 * @return list<string>
 */
function relatedRoutesForResource(array $routes, string $stem): array
{
    $names = [];
    foreach ($routes as $r) {
        $n = $r['name'] ?? '';
        if (str_starts_with($n, $stem . '.')) {
            $names[] = $n;
        }
    }

    return array_values(array_unique($names));
}

$lines[] = '';
$lines[] = '**Note:** AJAX-only and POST routes omitted. Status: `draft` → `reviewed` after QA. Corpus is English-only; see [README.md](README.md).';
$lines[] = '';

file_put_contents($outPath, implode("\n", $lines));
echo 'Wrote ' . count($screens) . ' rows to ' . $outPath . PHP_EOL;

function guessModule(string $name, string $action): string
{
    if (str_starts_with($name, 'production.')) {
        return 'production';
    }
    if (str_starts_with($name, 'pricing.')) {
        return 'pricing';
    }
    if (str_contains($action, 'Modules\\Purchase')) {
        return 'purchase';
    }
    if (str_contains($action, 'Modules\\Warehouse')) {
        return 'warehouse';
    }
    if (str_contains($action, 'Modules\\Payroll')) {
        return 'payroll';
    }
    if (str_contains($action, 'Modules\\Recruit')) {
        return 'recruit';
    }

    $prefix = explode('.', $name)[0];

    return $prefix ?: 'core';
}

function guessFolder(string $name, string $path): string
{
    if (str_contains($path, '/settings')) {
        return 'settings';
    }
    if (str_starts_with($name, 'production.') || str_contains($path, '/production/')) {
        return 'operations';
    }
    if (str_starts_with($name, 'pricing.') || str_contains($path, '/pricing/')) {
        return 'operations';
    }
    if (preg_match('/purchase|vendor|delivery|sales-shipment|grn|bill|warehouse|stock|inventory|adjustment|production|pricing/i', $name . $path)) {
        return 'operations';
    }
    if (preg_match('/employee|leave|attendance|holiday|payroll|recruit|designation|department/i', $name . $path)) {
        return 'hr';
    }
    if (preg_match('/invoice|payment|expense|bank|credit|estimate|order|deal|lead|client|proposal|quotation/i', $name . $path)) {
        if (preg_match('/expense|bank|payment/i', $name)) {
            return 'finance';
        }

        return 'sales';
    }
    if (preg_match('/webhook|zoom|biometric|einvoice|qr|biolink|line/i', $name . $path)) {
        return 'integrations';
    }
    if (str_contains($name, 'super_admin')) {
        return 'super-admin';
    }

    return 'core';
}

function guessPhase(string $folder): string
{
    return match ($folder) {
        'core', 'sales', 'finance' => '1-2',
        'operations' => '3-4',
        'hr' => '5',
        'settings', 'integrations', 'super-admin' => '6',
        default => '1-2',
    };
}
