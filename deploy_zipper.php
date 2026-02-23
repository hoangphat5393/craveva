<?php

$rootDir = isset($argv[1]) ? $argv[1] : 'temp_deploy';
$zipFilename = isset($argv[2]) ? $argv[2] : 'deploy_staging.zip';

$rootPath = realpath($rootDir);
$zipFile = $zipFilename;

// Initialize archive object
$zip = new ZipArchive();
$zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    // Skip directories (they would be added automatically)
    if (!$file->isDir()) {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Replace backslashes with forward slashes for Zip
        $relativePath = str_replace('\\', '/', $relativePath);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);

        if ($files->key() < 5) {
            echo "Added: $relativePath\n";
        }
    }
}

// Zip archive will be created only after closing object
$zip->close();

echo "Zip created successfully: $zipFile\n";
