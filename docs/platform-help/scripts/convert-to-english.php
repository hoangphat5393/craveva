<?php

/**
 * Convert all platform-help markdown to English and remove external links.
 *
 * Usage:
 *   php docs/platform-help/scripts/convert-to-english.php           # translate existing files
 *   php docs/platform-help/scripts/convert-to-english.php --regenerate  # EN pages from generator + translate rest
 */
$base = dirname(__DIR__);
$regenerate = in_array('--regenerate', $argv ?? [], true);

if ($regenerate) {
    passthru('php '.escapeshellarg(dirname(__DIR__).'/scripts/generate-pages.php').' --force', $code);
    if ($code !== 0) {
        exit($code);
    }
}

$files = iterator_to_all_md($base);
$count = 0;

foreach ($files as $path) {
    if (str_contains($path, DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    $original = file_get_contents($path);
    $converted = translateContent($original, $path, $base);

    if ($converted !== $original) {
        file_put_contents($path, $converted);
        $count++;
    }
}

echo "Updated {$count} markdown files under docs/platform-help/\n";

/**
 * @return list<string>
 */
function iterator_to_all_md(string $dir): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if ($file->isFile() && str_ends_with($file->getPathname(), '.md')) {
            $out[] = $file->getPathname();
        }
    }

    return $out;
}

function translateContent(string $text, string $path, string $base): string
{
    $text = applyPhraseMap($text);
    $text = rewriteExternalLinks($text, $path, $base);

    return $text;
}

function rewriteExternalLinks(string $text, string $path, string $base): string
{
    $rel = str_replace('\\', '/', substr($path, strlen($base) + 1));
    $prefix = str_repeat('../', substr_count($rel, '/'));

    $replacements = [
        '#\]\(\.\./\.\./FUNC_LOGIC/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/BUSINESS-FLOWS-SUMMARY.md)',
        '#\]\(\.\./\.\./docs/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(\.\./\.\./SPECIFICATION/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(\.\./\.\./Modules/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(\.\./\.\./RAG_agent\.md[^\)]*\)#u' => ']('.$prefix.'README.md)',
        '#\]\(\.\./\.\./FUNC_LOGIC/[^\)]*\)#u' => ']('.$prefix.'REFERENCE/BUSINESS-FLOWS-SUMMARY.md)',
        '#\]\(../../FUNC_LOGIC/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/BUSINESS-FLOWS-SUMMARY.md)',
        '#\]\(../../docs/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(../../SPECIFICATION/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(../../RAG_agent\.md[^\)]*\)#u' => ']('.$prefix.'README.md)',
        '#\]\(../../Modules/[^\)]+\)#u' => ']('.$prefix.'REFERENCE/ERP-SYSTEM-OVERVIEW.md)',
        '#\]\(\.\./\.\./FUNC_LOGIC/\)#u' => ']('.$prefix.'REFERENCE/BUSINESS-FLOWS-SUMMARY.md)',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text) ?? $text;
    }

    $text = preg_replace(
        '#\]\((?:\.\./)*((?:REFERENCE|flows|pages)/[^)]+)\)#u',
        ']('.$prefix.'$1)',
        $text,
    ) ?? $text;
    $text = preg_replace(
        '#\]\((?:\.\./)*(00-URL-INDEX\.md|01-ROLES-AND-ACCESS\.md|02-GLOSSARY\.md|README\.md)\)#u',
        ']('.$prefix.'$1)',
        $text,
    ) ?? $text;

    // Plain path mentions without link
    $text = preg_replace('#`FUNC_LOGIC/[^`]+`#u', '`REFERENCE/BUSINESS-FLOWS-SUMMARY.md`', $text) ?? $text;
    $text = preg_replace('#`docs/[^`]+`#u', '`REFERENCE/ERP-SYSTEM-OVERVIEW.md`', $text) ?? $text;
    $text = preg_replace('#`SPECIFICATION/[^`]+`#u', '`REFERENCE/ERP-SYSTEM-OVERVIEW.md`', $text) ?? $text;

    // Remove dev source lines pointing outside corpus
    $text = preg_replace('/\n\*\*Source \(dev\):\*\* [^\n]+\n/u', "\n", $text) ?? $text;

    // Fix broken "Related URLs: - List" formatting
    $text = str_replace('Related URLs: - List:', "Related routes:\n\n- List:", $text);

    return $text;
}

function applyPhraseMap(string $text): string
{
    $map = phraseMap();
    uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));

    foreach ($map as $vi => $en) {
        $text = str_replace($vi, $en, $text);
    }

    return $text;
}

/**
 * @return array<string, string>
 */
function phraseMap(): array
{
    return require dirname(__FILE__).'/en-phrases.php';
}
