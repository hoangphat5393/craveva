<?php

/**
 * Zip a directory into a file. Uses forward slashes in zip entries for Linux compatibility.
 * Usage: php deploy_zipper.php <sourceDir> <outputZip>
 */
$sourceDir = $argv[1] ?? '';
$outputZip = $argv[2] ?? '';

if (!$sourceDir || !$outputZip || !is_dir($sourceDir)) {
    fwrite(STDERR, "Usage: php deploy_zipper.php <sourceDir> <outputZip>\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Cannot create zip: $outputZip\n");
    exit(1);
}

$baseLen = strlen(realpath($sourceDir)) + 1;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $path) {
    $fullPath = $path->getPathname();
    $relative = substr($fullPath, $baseLen);
    $relative = str_replace('\\', '/', $relative);
    if ($path->isDir()) {
        $zip->addEmptyDir($relative . '/');
    } else {
        $zip->addFile($fullPath, $relative);
    }
}

$zip->close();
echo "Created: $outputZip\n";
