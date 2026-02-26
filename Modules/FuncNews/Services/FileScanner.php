<?php

namespace Modules\FuncNews\Services;

use Modules\FuncNews\Entities\FileRecord;
use Modules\FuncNews\Entities\FileDependency;

class FileScanner
{
    public function scanAndStore(): void
    {
        $paths = config('funcnews.scan_paths', []);
        $allowed = config('funcnews.allowed_extensions', []);

        foreach ($paths as $path) {
            $this->scanPath($path, $allowed);
        }
    }

    protected function scanPath(string $path, array $allowed): void
    {
        if (!file_exists($path)) {
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
        if (!$this->isAllowed($ext, $allowed)) {
            return;
        }

        $module = $this->inferModule($fullPath);
        $language = $this->inferLanguage($ext);
        $framework = $this->inferFramework($ext, $fullPath);
        $role = $this->inferRole($fullPath);
        $mtime = @filemtime($fullPath);
        $hash = @hash_file('sha1', $fullPath) ?: null;
        $version = $mtime ? 'mtime_' . date('YmdHis', $mtime) : null;

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
            'php', 'blade.php' => 'PHP',
            'js' => 'JavaScript',
            'css' => 'CSS',
            'json' => 'JSON',
            'md' => 'Markdown',
            default => 'Text',
        };
    }

    protected function inferFramework(string $ext, string $path): ?string
    {
        if ($ext === 'php' || $ext === 'blade.php') {
            return 'Laravel';
        }
        return null;
    }

    protected function inferRole(string $path): ?string
    {
        if (str_contains($path, '/Http/Controllers/')) return 'Controller';
        if (str_contains($path, '/Routes/')) return 'Routes';
        if (str_contains($path, '/Database/Migrations/')) return 'Migration';
        if (str_contains($path, '/Entities/')) return 'Model';
        if (str_contains($path, '/Resources/views/')) return 'View';
        if (str_contains($path, '/Providers/')) return 'ServiceProvider';
        if (str_contains($path, '/Config/')) return 'Config';
        if (str_contains($path, '/resources/views/')) return 'View';
        if (str_contains($path, '/routes/')) return 'Routes';
        return null;
    }

    protected function extractDependencies(string $path): array
    {
        $deps = [];
        $ext = $this->extension($path);
        $contents = @file_get_contents($path);
        if (!$contents) return $deps;

        if ($ext === 'php') {
            // Simple heuristic: use statements and view references
            if (preg_match_all('/use\\s+Modules\\\\([^\\\\]+)\\\\([^;]+);/m', $contents, $m, PREG_SET_ORDER)) {
                foreach ($m as $match) {
                    $module = $match[1];
                    $rest = $match[2];
                    $guessPath = base_path('Modules/' . $module . '/' . str_replace('\\', '/', $rest) . '.php');
                    $deps[$guessPath] = 'use';
                }
            }
            if (preg_match_all('/view\\([\\\'\\\"]([^\\\'\\\"]+)\\1?/', $contents, $m, PREG_SET_ORDER)) {
                foreach ($m as $match) {
                    $view = $match[1];
                    if (str_contains($view, '::')) {
                        [$module, $viewPath] = explode('::', $view);
                        $guess = base_path('Modules/' . ucfirst($module) . '/Resources/views/' . str_replace('.', '/', $viewPath) . '.blade.php');
                        $deps[$guess] = 'view';
                    }
                }
            }
        }

        if ($ext === 'blade.php') {
            if (preg_match_all('/@include\\([\\\'\\\"]([^\\\'\\\"]+)[\\\'\\\"]\\)/', $contents, $m, PREG_SET_ORDER)) {
                foreach ($m as $match) {
                    $viewPath = base_path('resources/views/' . str_replace('.', '/', $match[1]) . '.blade.php');
                    $deps[$viewPath] = 'include';
                }
            }
        }

        return $deps;
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
