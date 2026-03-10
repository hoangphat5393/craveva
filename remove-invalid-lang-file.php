<?php
/**
 * One-time script: remove invalid file "(Added By " in LanguagePack eng folder
 * that causes "git add" to fail. Only deletes that single file; path must be inside project.
 */
$base = realpath(__DIR__);
$engDir = $base . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'LanguagePack' . DIRECTORY_SEPARATOR . 'Languages' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'eng';

if (!is_dir($engDir)) {
    echo "Eng dir not found.\n";
    exit(1);
}

$removed = 0;
$it = new \FilesystemIterator($engDir, \FilesystemIterator::SKIP_DOTS);
foreach ($it as $fileinfo) {
    $name = $fileinfo->getFilename();
    // Invalid file that breaks git: name starts with "(" and is short (e.g. "(Added By " or "(Added By")
    if (!isset($name[0]) || $name[0] !== '(' || strlen($name) > 20) {
        continue;
    }
    $fullPath = $fileinfo->getPathname();
    $pathNormalized = str_replace('\\', '/', $fullPath);
    $baseNormalized = str_replace('\\', '/', $base);
    if (strpos($pathNormalized, $baseNormalized) !== 0) {
        continue;
    }
    echo "Removing: $fullPath\n";
    if (@unlink($fullPath)) {
        echo "Removed (file).\n";
        $removed++;
    } elseif (@rmdir($fullPath)) {
        echo "Removed (dir).\n";
        $removed++;
    } else {
        echo "Unlink/rmdir failed (Windows may need manual delete).\n";
        echo "Manual fix: open folder\n  " . $engDir . "\n  and delete the file whose name starts with \"(Added By\". Then run: git add .\n";
        exit(1);
    }
}

echo $removed ? "Done. Removed $removed file(s).\n" : "No matching file found.\n";
exit(0);
