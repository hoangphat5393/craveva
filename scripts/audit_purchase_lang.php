<?php

/**
 * Audit / apply ERP wording for Purchase module via LanguagePack (source of truth).
 *
 * Usage (from project root):
 *   php scripts/audit_purchase_lang.php                    # report only
 *   php scripts/audit_purchase_lang.php --apply            # apply glossary replacements (vi + en)
 *   php scripts/audit_purchase_lang.php --apply --locale=vi
 *   php scripts/audit_purchase_lang.php --csv=out/report.csv
 *   php scripts/audit_purchase_lang.php --patterns-only    # scan bad_patterns regex only
 *
 * Workflow (also wrapped by scripts/purchase_lang_erp.ps1):
 *   1. php scripts/audit_purchase_lang.php --apply
 *   2. php artisan languagepack:sync-keys --paths=Modules/Purchase   (optional: new keys from code)
 *   3. php artisan languagepack:publish-translation                    (publish to lang/)
 *
 * Glossary: FUNC_LOGIC/GLOSSARY_PURCHASE_ERP_VI.json
 */

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

if (! is_file($projectRoot . '/artisan')) {
    fwrite(STDERR, "Run from Laravel project root (artisan not found).\n");
    exit(1);
}

$options = getopt('', ['apply', 'locale::', 'csv::', 'patterns-only', 'glossary::', 'module::', 'help']);

if (isset($options['help'])) {
    echo <<<'HELP'
audit_purchase_lang.php — LanguagePack Purchase ERP wording

  --apply              Write glossary replacements (default: report only)
  --locale=vi|en|all   Locale to apply (default: all glossary locales)
  --csv=path           Write CSV report (Current,Suggested,Key,File,Priority,Reason)
  --patterns-only      Only scan bad_patterns; skip glossary apply/compare
  --glossary=path      Override glossary JSON path

Language Pack CLI (no separate "translate" command — use UI Auto Translate or edit JSON):
  php artisan languagepack:sync-keys --paths=Modules/Purchase
  php artisan languagepack:publish-translation

HELP;
    exit(0);
}

$module = $options['module'] ?? 'Purchase';
$glossaryPath = $options['glossary'] ?? $projectRoot . '/FUNC_LOGIC/GLOSSARY_' . strtoupper($module) . '_ERP_VI.json';
if (! is_file($glossaryPath)) {
    fwrite(STDERR, "Glossary not found: {$glossaryPath}\n");
    exit(1);
}

$glossary = json_decode((string) file_get_contents($glossaryPath), true, 512, JSON_THROW_ON_ERROR);
$languagePackBase = $projectRoot . '/Modules/LanguagePack/Languages/modules/' . ($glossary['module'] ?? $module);

$apply = isset($options['apply']);
$patternsOnly = isset($options['patterns-only']);
$localeOpt = $options['locale'] ?? 'all';
$csvPath = $options['csv'] ?? null;

$rows = [];

if (! $patternsOnly) {
    $locales = match ($localeOpt) {
        'vi' => ['vi' => $glossary['vi_replacements'] ?? []],
        'en' => ['en' => $glossary['en_replacements'] ?? []],
        default => [
            'vi' => $glossary['vi_replacements'] ?? [],
            'en' => $glossary['en_replacements'] ?? [],
        ],
    };

    foreach ($locales as $locale => $replacements) {
        if ($replacements === []) {
            continue;
        }

        $localeDir = $languagePackBase . '/' . $locale;
        if (! is_dir($localeDir)) {
            fwrite(STDERR, "Locale dir missing: {$localeDir}\n");
            continue;
        }

        $filesData = [];

        foreach ($replacements as $rule) {
            $file = $rule['file'] ?? 'app.php';
            $path = $rule['path'] ?? '';
            $to = $rule['to'] ?? '';
            $priority = $rule['priority'] ?? 'P1';
            $fullKey = "purchase::" . str_replace('.php', '', $file) . ($path !== '' ? '.' . str_replace('/', '.', $path) : '');

            if (! isset($filesData[$file])) {
                $targetFile = $localeDir . '/' . $file;
                if (! is_file($targetFile)) {
                    $rows[] = [$locale, $fullKey, $file, '', $to, $priority, 'SKIP: file missing', ''];
                    continue;
                }
                $loaded = include $targetFile;
                $filesData[$file] = [
                    'path' => $targetFile,
                    'data' => is_array($loaded) ? $loaded : [],
                ];
            }

            $current = arr_get($filesData[$file]['data'], $path);
            $currentStr = is_string($current) ? $current : (is_scalar($current) ? (string) $current : '');

            if ($currentStr === $to) {
                continue;
            }

            $reason = $currentStr === '' ? 'Key empty or missing' : 'Glossary ERP wording';
            $rows[] = [$locale, $fullKey, $file, $currentStr, $to, $priority, $reason, $apply ? 'applied' : 'pending'];

            if ($apply && $path !== '') {
                arr_set($filesData[$file]['data'], $path, $to);
            }
        }

        if ($apply) {
            foreach ($filesData as $file => $bundle) {
                writePhpLangFile($bundle['path'], $bundle['data']);
                echo "Updated: {$bundle['path']}\n";
            }
        }
    }

    compareLocaleKeys($languagePackBase, 'vi', 'en', $rows);
}

scanBadPatterns($languagePackBase . '/vi', $glossary['bad_patterns'] ?? [], $rows);

if ($csvPath !== null) {
    writeCsv($csvPath, $rows);
    echo "CSV: {$csvPath} (" . count($rows) . " rows)\n";
}

printConsoleTable($rows);

if (! $apply && count($rows) > 0) {
    echo "\nDry-run. Re-run with --apply to write LanguagePack files, then:\n";
    echo "  php artisan languagepack:publish-translation\n";
}

exit(0);

/**
 * @param  array<int, array<int, string>>  $rows
 */
function compareLocaleKeys(string $base, string $primary, string $secondary, array &$rows): void
{
    $primaryDir = $base . '/' . $primary;
    $secondaryDir = $base . '/' . $secondary;
    if (! is_dir($primaryDir) || ! is_dir($secondaryDir)) {
        return;
    }

    foreach (glob($primaryDir . '/*.php') ?: [] as $primaryFile) {
        $basename = basename($primaryFile);
        $secondaryFile = $secondaryDir . '/' . $basename;
        if (! is_file($secondaryFile)) {
            $rows[] = [$primary, "purchase::{$basename}", $basename, '', '', 'P2', "Missing {$secondary} file", ''];

            continue;
        }

        $a = flattenLangArray(include $primaryFile, '');
        $b = flattenLangArray(include $secondaryFile, '');

        foreach (array_diff_key($a, $b) as $dotKey => $val) {
            $rows[] = [$primary, 'purchase::' . pathinfo($basename, PATHINFO_FILENAME) . '.' . $dotKey, $basename, $val, '', 'P2', "Key in {$primary} missing in {$secondary}", ''];
        }
    }
}

/**
 * @return array<string, string>
 */
function flattenLangArray(array $data, string $prefix): array
{
    $out = [];
    foreach ($data as $k => $v) {
        $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
        if (is_array($v)) {
            $out = array_merge($out, flattenLangArray($v, $key));
        } else {
            $out[$key] = (string) $v;
        }
    }

    return $out;
}

/**
 * @param  array<int, array{regex?: string, reason?: string}>  $patterns
 * @param  array<int, array<int, string>>  $rows
 */
function scanBadPatterns(string $viDir, array $patterns, array &$rows): void
{
    if ($patterns === [] || ! is_dir($viDir)) {
        return;
    }

    foreach (glob($viDir . '/*.php') ?: [] as $file) {
        $data = include $file;
        if (! is_array($data)) {
            continue;
        }
        $flat = flattenLangArray($data, '');
        $basename = basename($file);
        foreach ($flat as $dotKey => $value) {
            foreach ($patterns as $pattern) {
                $regex = $pattern['regex'] ?? '';
                if ($regex === '' || ! preg_match('/' . $regex . '/u', $value)) {
                    continue;
                }
                $fullKey = 'purchase::' . pathinfo($basename, PATHINFO_FILENAME) . '.' . $dotKey;
                $rows[] = ['vi', $fullKey, $basename, $value, '', 'P0', $pattern['reason'] ?? 'bad_pattern', 'pattern'];
            }
        }
    }
}

/**
 * @param  array<string, mixed>  $array
 */
function arr_get(array $array, string $key, mixed $default = null): mixed
{
    if ($key === '') {
        return $default;
    }

    $segments = explode('.', $key);
    foreach ($segments as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }

    return $array;
}

/**
 * @param  array<string, mixed>  $array
 */
function arr_set(array &$array, string $key, mixed $value): void
{
    $segments = explode('.', $key);
    $current = &$array;
    $last = array_pop($segments);

    foreach ($segments as $segment) {
        if (! isset($current[$segment]) || ! is_array($current[$segment])) {
            $current[$segment] = [];
        }
        $current = &$current[$segment];
    }

    if ($last !== null && $last !== '') {
        $current[$last] = $value;
    }
}

function writePhpLangFile(string $path, array $data): void
{
    $content = "<?php\n\nreturn " . formatPhpArray($data) . ";\n";
    file_put_contents($path, $content);
}

function formatPhpArray(array $arr, int $indent = 0): string
{
    $pad = str_repeat('    ', $indent);
    $lines = ["[\n"];

    foreach ($arr as $k => $v) {
        $key = is_int($k) ? (string) $k : "'" . addslashes((string) $k) . "'";
        if (is_array($v)) {
            $lines[] = $pad . '    ' . $key . ' => ' . formatPhpArray($v, $indent + 1) . ",\n";
        } else {
            $lines[] = $pad . '    ' . $key . ' => ' . "'" . addslashes((string) $v) . "'" . ",\n";
        }
    }

    $lines[] = $pad . ']';

    return implode('', $lines);
}

/**
 * @param  array<int, array<int, string>>  $rows
 */
function writeCsv(string $path, array $rows): void
{
    $dir = dirname($path);
    if ($dir !== '' && $dir !== '.' && ! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $fp = fopen($path, 'w');
    if ($fp === false) {
        return;
    }

    fputcsv($fp, ['Locale', 'Key', 'File', 'Current', 'Suggested', 'Priority', 'Reason', 'Status']);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
}

/**
 * @param  array<int, array<int, string>>  $rows
 */
function printConsoleTable(array $rows): void
{
    if ($rows === []) {
        echo "No issues or pending replacements.\n";

        return;
    }

    $pending = array_filter($rows, fn($r) => ($r[7] ?? '') !== 'applied' && ($r[7] ?? '') !== 'pattern' || ($r[4] ?? '') !== '');
    $patterns = array_filter($rows, fn($r) => ($r[7] ?? '') === 'pattern');

    echo 'Replacements / key gaps: ' . count($pending) . "\n";
    echo 'Bad pattern hits: ' . count($patterns) . "\n\n";

    $shown = 0;
    foreach ($rows as $row) {
        if ($shown >= 40) {
            echo "... and " . (count($rows) - 40) . " more (use --csv= for full list)\n";
            break;
        }
        [$locale, $key, $file, $current, $suggested, $priority, $reason] = array_pad($row, 8, '');
        $cur = mb_strlen($current) > 48 ? mb_substr($current, 0, 45) . '...' : $current;
        $sug = mb_strlen($suggested) > 48 ? mb_substr($suggested, 0, 45) . '...' : $suggested;
        echo "[{$priority}] {$locale} {$key}\n  Current:   {$cur}\n  Suggested: {$sug}\n  Reason:    {$reason}\n\n";
        $shown++;
    }
}
