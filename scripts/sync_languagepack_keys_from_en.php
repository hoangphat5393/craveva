<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\VarExporter\VarExporter;

/**
 * Backfill missing LanguagePack keys from en into other locales.
 *
 * Usage:
 *   php scripts/sync_languagepack_keys_from_en.php --dry-run
 *   php scripts/sync_languagepack_keys_from_en.php
 *   php scripts/sync_languagepack_keys_from_en.php --locales=ko,vi,zh-CN
 */

$options = getopt('', ['dry-run', 'locales::']);
$dryRun = array_key_exists('dry-run', $options);
$onlyLocales = isset($options['locales']) && $options['locales'] !== ''
    ? array_map('trim', explode(',', (string) $options['locales']))
    : null;

$base = dirname(__DIR__) . '/Modules/LanguagePack/Languages';
$sourceLocale = 'en';
$skipLocales = ['en', 'eng'];

/** @return array<string, mixed> */
function flattenKeys(array $array, string $prefix = ''): array
{
    $flat = [];

    foreach ($array as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

        if (is_array($value)) {
            $flat = array_merge($flat, flattenKeys($value, $path));
        } else {
            $flat[$path] = $value;
        }
    }

    return $flat;
}

/** @param array<string, mixed> $array */
function setNestedValue(array &$array, string $path, mixed $value): void
{
    $parts = explode('.', $path);
    $ref = &$array;

    foreach ($parts as $index => $part) {
        if ($index === count($parts) - 1) {
            $ref[$part] = $value;

            return;
        }

        if (! isset($ref[$part]) || ! is_array($ref[$part])) {
            $ref[$part] = [];
        }

        $ref = &$ref[$part];
    }
}

/** @param array<string, mixed> $array */
function writeLangFile(string $path, array $array): void
{
    $exported = VarExporter::export($array);
    $content = "<?php\n\nreturn {$exported};\n";
    file_put_contents($path, $content);
}

/** @return list<string> */
function discoverEnLangFiles(string $base): array
{
    $files = [];

    foreach (glob($base . '/app/en/*.php') ?: [] as $file) {
        $files[] = $file;
    }

    foreach (glob($base . '/modules/*/en/*.php') ?: [] as $file) {
        $files[] = $file;
    }

    return $files;
}

/** @return list<string> */
function localesForFile(string $enFile, string $base, ?array $onlyLocales, array $skipLocales): array
{
    $parent = dirname($enFile);
    $grandParent = dirname($parent);
    $locales = [];

    foreach (glob($grandParent . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $locale = basename($dir);

        if (in_array($locale, $skipLocales, true)) {
            continue;
        }

        if ($onlyLocales !== null && ! in_array($locale, $onlyLocales, true)) {
            continue;
        }

        $locales[] = $locale;
    }

    sort($locales);

    return $locales;
}

$enFiles = discoverEnLangFiles($base);
$stats = [
    'files_touched' => 0,
    'files_created' => 0,
    'keys_added' => 0,
    'by_locale' => [],
];

foreach ($enFiles as $enFile) {
    $enData = include $enFile;

    if (! is_array($enData)) {
        continue;
    }

    $enFlat = flattenKeys($enData);
    $fileName = basename($enFile);
    $locales = localesForFile($enFile, $base, $onlyLocales, $skipLocales);

    foreach ($locales as $locale) {
        $targetDir = dirname(dirname($enFile)) . '/' . $locale;
        $targetFile = $targetDir . '/' . $fileName;

        if (! is_dir($targetDir)) {
            if ($dryRun) {
                $stats['keys_added'] += count($enFlat);
                $stats['files_created']++;
                $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + count($enFlat);

                continue;
            }

            mkdir($targetDir, 0777, true);
        }

        if (! file_exists($targetFile)) {
            if ($dryRun) {
                $stats['keys_added'] += count($enFlat);
                $stats['files_created']++;
                $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + count($enFlat);

                continue;
            }

            writeLangFile($targetFile, $enData);
            $stats['files_created']++;
            $stats['files_touched']++;
            $stats['keys_added'] += count($enFlat);
            $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + count($enFlat);

            continue;
        }

        $localeData = include $targetFile;

        if (! is_array($localeData)) {
            $localeData = [];
        }

        $localeFlat = flattenKeys($localeData);
        $addedForFile = 0;

        foreach ($enFlat as $path => $value) {
            if (array_key_exists($path, $localeFlat)) {
                continue;
            }

            setNestedValue($localeData, $path, $value);
            $addedForFile++;
        }

        if ($addedForFile === 0) {
            continue;
        }

        if ($dryRun) {
            $stats['keys_added'] += $addedForFile;
            $stats['files_touched']++;
            $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + $addedForFile;

            continue;
        }

        writeLangFile($targetFile, $localeData);
        $stats['keys_added'] += $addedForFile;
        $stats['files_touched']++;
        $stats['by_locale'][$locale] = ($stats['by_locale'][$locale] ?? 0) + $addedForFile;
    }
}

ksort($stats['by_locale']);

echo ($dryRun ? '[DRY RUN] ' : '') . "LanguagePack backfill from {$sourceLocale}\n";
echo "EN source files scanned: " . count($enFiles) . "\n";
echo "Target files touched: {$stats['files_touched']}\n";
echo "Target files created: {$stats['files_created']}\n";
echo "Keys added: {$stats['keys_added']}\n";

if ($stats['by_locale'] !== []) {
    echo "\nBy locale:\n";

    foreach ($stats['by_locale'] as $locale => $count) {
        echo "  {$locale}: {$count}\n";
    }
}
