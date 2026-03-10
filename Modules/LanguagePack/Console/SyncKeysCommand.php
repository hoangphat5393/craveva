<?php

namespace Modules\LanguagePack\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Tự động quét code, trích xuất key dịch (__(), @lang...) và thêm key thiếu vào LanguagePack.
 *
 * Workflow phát triển module mới:
 * 1. php artisan languagepack:sync-keys   (quét code → thêm key mới vào LanguagePack)
 * 2. Dịch/bổ sung giá trị trong các file tại Modules/LanguagePack/Languages/
 * 3. php artisan languagepack:publish-translation  (publish ra app/modules)
 */
class SyncKeysCommand extends Command
{
    protected $signature = 'languagepack:sync-keys
                            {--paths= : Đường dẫn quét, phân cách bởi dấu phẩy (mặc định: app,resources,Modules)}
                            {--dry-run : Chỉ liệt kê key tìm thấy, không ghi file}
                            {--locale=eng : Locale mặc định để thêm key (eng hoặc en)}';

    protected $description = 'Quét code tìm key dịch (__(), @lang...) và thêm key thiếu vào LanguagePack';

    private array $transFunctions = [
        '__',
        'trans',
        '@lang',
        '@choice',
        'Lang::get',
        'Lang::trans',
    ];

    private array $foundKeys = [];

    private string $basePath;

    private string $languagePackPath;

    public function __construct()
    {
        parent::__construct();
        $this->basePath = base_path();
        $this->languagePackPath = module_path('LanguagePack', 'Languages');
    }

    public function handle(): int
    {
        $paths = $this->option('paths') ? explode(',', $this->option('paths')) : ['app', 'resources', 'Modules'];
        $dryRun = $this->option('dry-run');
        $locale = $this->option('locale') ?: 'eng';

        $this->info('Quét thư mục: '.implode(', ', $paths));
        $this->info('Locale mặc định: '.$locale);
        if ($dryRun) {
            $this->warn('Chế độ dry-run: không ghi file');
        }
        $this->newLine();

        foreach ($paths as $path) {
            $fullPath = $this->basePath.'/'.trim($path);
            if (File::isDirectory($fullPath)) {
                $this->scanDirectory($fullPath);
            }
        }

        if (empty($this->foundKeys)) {
            $this->info('Không tìm thấy key nào.');

            return self::SUCCESS;
        }

        $this->info('Tìm thấy '.count($this->foundKeys).' key.');
        $added = $this->mergeKeysIntoLanguagePack($locale, $dryRun);

        if ($dryRun) {
            $this->table(['Key', 'Vendor', 'File', 'Path'], array_map(fn ($k, $v) => [$k, $v['vendor'] ?? '-', $v['file'], $v['path']], array_keys($this->foundKeys), array_values($this->foundKeys)));
        } else {
            $this->info("Đã thêm {$added} key mới vào LanguagePack.");
        }

        return self::SUCCESS;
    }

    private function scanDirectory(string $dir): void
    {
        $files = File::allFiles($dir);
        $patterns = $this->buildRegexPatterns();

        foreach ($files as $file) {
            if (! in_array($file->getExtension(), ['php', 'blade.php'])) {
                continue;
            }

            $content = File::get($file->getPathname());
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $key = isset($m[1]) ? trim($m[1], '\'"') : '';
                        if ($this->isValidKey($key)) {
                            $this->foundKeys[$key] = $this->parseKey($key);
                        }
                    }
                }
            }
        }
    }

    private function buildRegexPatterns(): array
    {
        $capture = '([\'"][^\'"]*[\'"])';

        return [
            '/__\s*\(\s*'.$capture.'\s*\)/',
            '/trans\s*\(\s*'.$capture.'\s*\)/',
            '/@lang\s*\(\s*'.$capture.'\s*\)/',
            '/Lang::get\s*\(\s*'.$capture.'\s*\)/',
            '/Lang::trans\s*\(\s*'.$capture.'\s*\)/',
        ];
    }

    private function isValidKey(string $key): bool
    {
        if (strlen($key) < 2) {
            return false;
        }

        $key = trim($key, '\'"');
        if (empty($key) || Str::contains($key, '${') || Str::contains($key, '{{')) {
            return false;
        }

        return true;
    }

    /**
     * Parse key thành vendor, file, path.
     * Ví dụ:
     *   purchase::modules.inventory.unit → vendor=purchase, file=modules, path=inventory.unit
     *   app.normal → vendor=null, file=app, path=normal
     */
    private function parseKey(string $rawKey): array
    {
        $rawKey = trim($rawKey, '\'"');
        $vendor = null;
        $group = null;
        $path = $rawKey;

        if (Str::contains($rawKey, '::')) {
            [$vendor, $path] = explode('::', $rawKey, 2);
        }

        if (Str::contains($path, '.')) {
            $parts = explode('.', $path);
            $group = $parts[0];
            $path = implode('.', array_slice($parts, 1));
        } else {
            $group = $path;
            $path = '';
        }

        $file = $group ?: 'app';
        $pathValue = $path ?: $group;

        return [
            'vendor' => $vendor,
            'file' => $file,
            'path' => is_string($pathValue) ? $pathValue : (is_array($pathValue) ? implode('.', $pathValue) : ''),
        ];
    }

    private function mergeKeysIntoLanguagePack(string $locale, bool $dryRun): int
    {
        $added = 0;

        foreach ($this->foundKeys as $fullKey => $parsed) {
            $vendor = $parsed['vendor'];
            $file = $parsed['file'].'.php';
            $path = $parsed['path'];
            $path = is_array($path) ? implode('.', $path) : (string) $path;
            $path = trim($path, '.');
            if ($path === '') {
                continue;
            }

            if ($vendor) {
                $moduleDir = $this->resolveModuleDirName($vendor);
                if (! $moduleDir) {
                    continue;
                }
                $targetDir = $this->languagePackPath.'/modules/'.$moduleDir.'/'.$locale;
                if (! File::isDirectory($targetDir)) {
                    $targetDir = $this->languagePackPath.'/modules/'.$moduleDir.'/en';
                }
                if (! File::isDirectory($targetDir)) {
                    $targetDir = $this->languagePackPath.'/modules/'.$moduleDir.'/eng';
                }
                if (! File::isDirectory($targetDir)) {
                    continue;
                }
            } else {
                $targetDir = $this->languagePackPath.'/app/'.$locale;
                if (! File::isDirectory($targetDir)) {
                    $targetDir = $this->languagePackPath.'/app/'.($locale === 'eng' ? 'en' : 'eng');
                }
                if (! File::isDirectory($targetDir)) {
                    continue;
                }
            }

            $targetFile = $targetDir.'/'.$file;
            if (! File::exists($targetFile)) {
                if (! $dryRun) {
                    File::ensureDirectoryExists($targetDir);
                    $this->createNewLangFile($targetFile, $path, $fullKey);
                    $added++;
                }
                continue;
            }

            $data = include $targetFile;
            if (! is_array($data)) {
                $data = [];
            }

            $pathKey = $path;
            $pathArr = explode('.', $path);
            $existing = Arr::get($data, $pathKey);

            if ($existing !== null) {
                continue;
            }

            $defaultValue = $this->humanizeKey(end($pathArr));

            if (! $dryRun) {
                Arr::set($data, $pathKey, $defaultValue);
                $this->writePhpArray($targetFile, $data);
                $added++;
            }
        }

        return $added;
    }

    private function humanizeKey(string $key): string
    {
        return Str::title(str_replace(['_', '-'], ' ', $key));
    }

    private function resolveModuleDirName(string $vendor): ?string
    {
        $modulesPath = $this->languagePackPath.'/modules';
        if (! File::isDirectory($modulesPath)) {
            return null;
        }
        $vendorLower = strtolower($vendor);
        foreach (File::directories($modulesPath) as $dir) {
            if (strtolower(basename($dir)) === $vendorLower) {
                return basename($dir);
            }
        }

        return null;
    }

    private function createNewLangFile(string $path, string $keyPath, string $fullKey): void
    {
        $pathArr = explode('.', $keyPath);
        $data = [];
        Arr::set($data, $keyPath, $this->humanizeKey(end($pathArr)));
        $this->writePhpArray($path, $data);
    }

    private function writePhpArray(string $path, array $data): void
    {
        $content = "<?php\n\nreturn ".$this->formatArray($data).";\n";
        File::put($path, $content);
    }

    private function formatArray(array $arr, int $indent = 0): string
    {
        $pad = str_repeat('    ', $indent);
        $lines = ["[\n"];

        foreach ($arr as $k => $v) {
            $key = is_numeric($k) ? $k : "'".addslashes($k)."'";
            if (is_array($v)) {
                $lines[] = $pad.'    '.$key.' => '.$this->formatArray($v, $indent + 1).",\n";
            } else {
                $lines[] = $pad.'    '.$key.' => '."'".addslashes((string) $v)."'".",\n";
            }
        }

        $lines[] = $pad.']';

        return implode('', $lines);
    }
}
