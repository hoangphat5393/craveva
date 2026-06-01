<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\VarExporter\VarExporter;

/**
 * Title-case short UI labels in LanguagePack EN (not full sentences / errors).
 */
$base = dirname(__DIR__) . '/Modules/LanguagePack/Languages';

/** @var list<string> */
$acronyms = ['GRN', 'SO', 'PO', 'SKU', 'UOM', 'BOM', 'API', 'PDF', 'SMS', 'ERP', 'FG', 'RM', 'AI', 'URL', 'IP', 'WMS'];

$skipFiles = '/(messages|email|placeholders|validation|installer_messages|passwords)\.php$/';

$files = array_merge(
    glob($base . '/modules/*/en/app.php') ?: [],
    glob($base . '/modules/*/en/modules.php') ?: [],
    glob($base . '/modules/*/en/deliveryOrder.php') ?: [],
    glob($base . '/modules/*/en/reports.php') ?: [],
    [$base . '/app/en/app.php'],
);

function shouldSkipFile(string $path, string $skipPattern): bool
{
    return (bool) preg_match($skipPattern, $path);
}

function shouldSkipKey(int|string $key): bool
{
    if (! is_string($key)) {
        return false;
    }

    return (bool) preg_match(
        '/^(err_|success_|warning_|info_)|Help$|Hint$|Intro$|Info$|Text\d*$|Popover/i',
        $key
    );
}

function isLabelCandidate(string $value): bool
{
    $len = strlen($value);
    if ($len < 2 || $len > 80) {
        return false;
    }
    if (str_contains($value, '<') || str_contains($value, "\n") || str_contains($value, ':')) {
        return false;
    }
    if (preg_match('/\.$/', $value) && $len > 45) {
        return false;
    }
    if (preg_match('/^(e\.g\.|eg\.|i\.e\.)/i', $value)) {
        return false;
    }
    if (preg_match('/\b(please|cannot|must|should|will|would|have been|has been|do not|is not|are not|try again)\b/i', $value)) {
        return false;
    }

    return (bool) preg_match('/[a-z]/', $value);
}

function restoreAcronyms(string $text): string
{
    foreach (['GRN', 'SO', 'PO', 'SKU', 'UOM', 'BOM', 'API', 'PDF', 'SMS', 'ERP', 'FG', 'RM', 'AI', 'URL', 'IP', 'WMS'] as $acro) {
        $pattern = '/\b' . preg_quote(strtolower($acro), '/') . '\b/i';
        $text = (string) preg_replace($pattern, $acro, $text);
    }

    return (string) preg_replace(
        ['/\bSales Do\b/', '/\bQuickbooks\b/i', '/\bIos\b/', '/\bIphone\b/'],
        ['Sales DO', 'QuickBooks', 'iOS', 'iPhone'],
        $text
    );
}

function titleCaseLabel(string $text): string
{
    if (! isLabelCandidate($text)) {
        return $text;
    }

    $lower = mb_strtolower($text, 'UTF-8');

    return restoreAcronyms(mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8'));
}

/**
 * @param array<mixed> $array
 * @return array{mixed, int}
 */
function walk(array $array, int|string|null $parentKey = null): array
{
    $changes = 0;
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            [$array[$key], $sub] = walk($value, $key);
            $changes += $sub;

            continue;
        }
        if (! is_string($value) || shouldSkipKey($key)) {
            continue;
        }
        $new = titleCaseLabel($value);
        if ($new !== $value) {
            $array[$key] = $new;
            $changes++;
        }
    }

    return [$array, $changes];
}

$totalChanges = 0;
$totalFiles = 0;

foreach ($files as $file) {
    if (! is_file($file) || shouldSkipFile($file, $skipFiles)) {
        continue;
    }

    /** @var array<mixed> $data */
    $data = include $file;
    if (! is_array($data)) {
        continue;
    }

    [$updated, $changes] = walk($data);
    if ($changes === 0) {
        continue;
    }

    $exported = VarExporter::export($updated);
    file_put_contents($file, "<?php\n\nreturn {$exported};\n");

    $lint = shell_exec('php -l ' . escapeshellarg($file) . ' 2>&1');
    if ($lint === null || ! str_contains($lint, 'No syntax errors')) {
        echo "SYNTAX ERROR: {$file}\n{$lint}\n";
        exit(1);
    }

    $relative = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file);
    echo "{$relative}: {$changes}\n";
    $totalChanges += $changes;
    $totalFiles++;
}

echo "Done. {$totalFiles} files, {$totalChanges} strings.\n";
