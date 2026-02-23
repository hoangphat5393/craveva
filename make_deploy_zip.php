<?php

$zipFile = 'deploy_staging.zip';
$rootPath = __DIR__;

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot open <$zipFile>\n");
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$excludeDirs = [
    '.git',
    'node_modules',
    'storage',
    'tests',
    'vendor', // Exclude vendor to keep zip small, assuming server has dependencies or they are updated separately
    '.idea',
    '.vscode',
    'FUNC_DEVELOPMENT'
];

$excludeFiles = [
    '.env',
    'deploy_staging.zip',
    '.DS_Store',
    'Thumbs.db'
];

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        // Check exclusions
        $exclude = false;
        foreach ($excludeDirs as $dir) {
            if (strpos($relativePath, $dir . '/') === 0 || strpos($relativePath, '/' . $dir . '/') !== false) {
                $exclude = true;
                break;
            }
        }
        
        if ($exclude) continue;

        if (in_array(basename($filePath), $excludeFiles)) continue;

        // Specific file exclusions
        if ($relativePath == 'deploy_zipper.php' || $relativePath == 'make_deploy_zip.php') continue;

        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

echo "Staging deployment zip created: $zipFile\n";
