<?php

namespace Modules\DeveloperTools\Services;

use Modules\DeveloperTools\Entities\FileDependency;
use Modules\DeveloperTools\Entities\FileRecord;

class FileScanner
{
    public function scanAndStore(): void
    {
        $paths = config('developertools.scan_paths', []);
        $allowed = config('developertools.allowed_extensions', []);

        foreach ($paths as $path) {
            $this->scanPath($path, $allowed);
        }
    }

    protected function scanPath(string $path, array $allowed): void
    {
        if (! file_exists($path)) {
            return;
        }

        if (is_file($path)) {
            $this->processFile($path, $allowed);

            return;
        }

        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($iter as $file) {
            if ($file->isFile()) {
                $this->processFile($file->getPathname(), $allowed);
            }
        }
    }

    protected function processFile(string $fullPath, array $allowed): void
    {
        $ext = $this->extension($fullPath);
        if (! $this->isAllowed($ext, $allowed)) {
            return;
        }

        $module = $this->inferModule($fullPath);
        $language = $this->inferLanguage($ext);
        $framework = $this->inferFramework($ext, $fullPath);
        $role = $this->inferRole($fullPath);
        $mtime = @filemtime($fullPath);
        $hash = @hash_file('sha1', $fullPath) ?: null;
        $version = $mtime ? 'mtime_'.date('YmdHis', $mtime) : null;

        $record = FileRecord::updateOrCreate(
            ['path' => $this->normalizePath($fullPath)],
            [
                'name' => basename($fullPath),
                'language' => $language,
                'framework' => $framework,
                'role' => $role,
                'module' => $module,
                'version' => $version,
                'last_modified_at' => $mtime ? date('Y-m-d H:i:s', $mtime) : null,
                'hash' => $hash,
                'extra' => null,
            ]
        );

        $deps = $this->extractDependencies($fullPath);
        FileDependency::where('file_id', $record->id)->delete();
        foreach ($deps as $depPath => $relation) {
            $dep = FileRecord::firstOrCreate(
                ['path' => $this->normalizePath($depPath)],
                ['name' => basename($depPath)]
            );
            FileDependency::create([
                'file_id' => $record->id,
                'depends_on_file_id' => $dep->id,
                'relation_type' => $relation,
            ]);
        }
    }

    protected function extension(string $path): string
    {
        if (str_ends_with($path, '.blade.php')) {
            return 'blade.php';
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    protected function isAllowed(string $ext, array $allowed): bool
    {
        return in_array($ext, $allowed, true);
    }

    protected function inferModule(string $path): ?string
    {
        if (preg_match('#Modules/([^/]+)/#', $path, $m)) {
            return $m[1];
        }

        return null;
    }

    protected function inferLanguage(string $ext): string
    {
        return match ($ext) {
            'php' => 'PHP',
            'blade.php' => 'PHP (Blade)',
            'js' => 'JavaScript',
            'css' => 'CSS',
            'json' => 'JSON',
            'md' => 'Markdown',
            default => strtoupper($ext),
        };
    }

    protected function inferFramework(string $ext, string $path): ?string
    {
        if ($ext === 'blade.php') {
            return 'Laravel';
        }
        if (str_contains($path, 'Modules') && $ext === 'php') {
            return 'Laravel Modules';
        }

        return null;
    }

    protected function inferRole(string $path): ?string
    {
        if (str_contains($path, 'Http/Controllers')) {
            return 'Controller';
        }
        if (str_contains($path, 'Entities') || str_contains($path, 'Models')) {
            return 'Model';
        }
        if (str_contains($path, 'Database/Migrations')) {
            return 'Migration';
        }
        if (str_contains($path, 'Resources/views')) {
            return 'View';
        }
        if (str_contains($path, 'Routes')) {
            return 'Route';
        }
        if (str_contains($path, 'Config')) {
            return 'Config';
        }

        return 'Other';
    }

    protected function extractDependencies(string $path): array
    {
        $content = @file_get_contents($path);
        if (! $content) {
            return [];
        }

        $deps = [];

        // Basic dependency detection logic
        return $deps;
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
