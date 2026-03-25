<?php

/**
 * One-off merge: copy scalar strings from a *source* English tree into `en`.
 * Repo no longer ships `Modules/.../Languages/.../eng`; keep a backup folder locally
 * if you still need to diff against old `eng`.
 *
 * Usage: php scripts/merge_eng_into_en_lang.php [--dry-run] [--file=messages.php] [--app-only]
 * (Edit script to point $langRoot source dirs if using a backup path named `eng`.)
 */

declare(strict_types=1);

$langRoot = dirname(__DIR__) . '/Modules/LanguagePack/Languages';
$onlyApp = in_array('--app-only', $argv, true);

/** Optional: path to a clone of Languages/ that still has app/eng (e.g. old branch or zip). */
$backupRoot = getenv('LANGPACK_ENG_BACKUP_ROOT') ?: '';

$dryRun = in_array('--dry-run', $argv, true);
$fileFilter = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--file=')) {
        $fileFilter = substr($a, 7);
    }
}

/**
 * @param mixed $en
 * @param mixed $eng
 * @return array{0: mixed, 1: int} merged and change count
 */
function mergeRecursive($en, $eng, int &$changes): array
{
    if (!is_array($eng)) {
        return [$en, 0];
    }
    if (!is_array($en)) {
        $changes += is_array($eng) ? count($eng, COUNT_RECURSIVE) : 1;

        return [$eng, $changes];
    }

    foreach ($eng as $key => $engVal) {
        if (!array_key_exists($key, $en)) {
            $en[$key] = $engVal;
            $changes++;
            continue;
        }
        $cur = $en[$key];
        if (is_array($engVal) && is_array($cur)) {
            [$merged,] = mergeRecursive($cur, $engVal, $changes);
            $en[$key] = $merged;
        } elseif (is_array($engVal) && !is_array($cur)) {
            $en[$key] = $engVal;
            $changes++;
        } elseif (is_string($engVal) && is_string($cur) && $cur !== $engVal) {
            $en[$key] = $engVal;
            $changes++;
        } elseif (is_string($engVal) && !is_string($cur)) {
            $en[$key] = $engVal;
            $changes++;
        }
    }

    return [$en, $changes];
}

function exportPhpArray(array $data): string
{
    return "<?php\n\nreturn " . var_export($data, true) . ";\n";
}

$pairs = [];

if ($backupRoot !== '' && is_dir($backupRoot . '/app/eng')) {
    $pairs[] = [$backupRoot . '/app/eng', $langRoot . '/app/en'];
    if (! $onlyApp) {
        foreach (glob($backupRoot . '/modules/*/eng', GLOB_ONLYDIR) ?: [] as $engDir) {
            $moduleName = basename(dirname($engDir));
            $enDir = $langRoot . '/modules/' . $moduleName . '/en';
            if (is_dir($enDir)) {
                $pairs[] = [$engDir, $enDir];
            }
        }
    }
}

if ($pairs === []) {
    fwrite(STDERR, "Nothing to merge: repo no longer has Languages/.../eng. To compare one-off, set LANGPACK_ENG_BACKUP_ROOT to a folder containing app/eng (copy from git history or backup).\n");
    exit(0);
}

$totalFiles = 0;
$totalChangesAll = 0;

foreach ($pairs as [$engDir, $enDir]) {
    $files = glob($engDir . '/*.php') ?: [];
    sort($files);
    foreach ($files as $engFile) {
        $basename = basename($engFile);
        if ($fileFilter !== null && $basename !== $fileFilter) {
            continue;
        }
        $enFile = $enDir . '/' . $basename;
        if (!is_file($enFile)) {
            fwrite(STDERR, "Skip (no en): " . str_replace($langRoot . '/', '', $enFile) . "\n");
            continue;
        }

        /** @var array $engData */
        $engData = include $engFile;
        /** @var array $enData */
        $enData = include $enFile;
        if (!is_array($engData) || !is_array($enData)) {
            fwrite(STDERR, "Skip (non-array root): {$basename}\n");
            continue;
        }

        $changes = 0;
        [$merged,] = mergeRecursive($enData, $engData, $changes);
        if ($changes === 0) {
            continue;
        }
        $totalFiles++;
        $totalChangesAll += $changes;
        $rel = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $enFile);
        echo "{$rel}: {$changes} merge(s)\n";
        if (!$dryRun) {
            $content = exportPhpArray($merged);
            file_put_contents($enFile, $content);
        }
    }
}

echo $dryRun ? "Dry run — no files written.\n" : "Done. Files touched: {$totalFiles}, total merges: {$totalChangesAll}\n";
