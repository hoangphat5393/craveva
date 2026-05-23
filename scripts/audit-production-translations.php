<?php

declare(strict_types=1);

/**
 * Audit production::app.* keys used in code vs LanguagePack + module Resources/lang.
 */
$root = dirname(__DIR__);

function loadAppKeys(string $file): array
{
    if (! is_file($file)) {
        return [];
    }

    $data = include $file;

    return flattenKeys($data);
}

/** @return list<string> */
function flattenKeys(array $data, string $prefix = ''): array
{
    $keys = [];
    foreach ($data as $key => $value) {
        $full = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        if (is_array($value)) {
            $keys = array_merge($keys, flattenKeys($value, $full));
        } else {
            $keys[] = $full;
        }
    }

    return $keys;
}

/** @return list<string> */
function collectUsedKeys(string $dir): array
{
    $used = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        if (! preg_match('/\.(php|blade\.php)$/', $path)) {
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }
        if (preg_match_all("/(?:__|@lang)\(\s*['\"]production::app\.([a-zA-Z0-9_.]+)/", $content, $matches)) {
            foreach ($matches[1] as $key) {
                $used[$key] = true;
            }
        }
        if (preg_match_all('/production::app\.([a-zA-Z0-9_]+)/', $content, $matches2)) {
            foreach ($matches2[1] as $key) {
                if (! str_contains($key, '$') && ! str_ends_with($key, '_')) {
                    $used[$key] = true;
                }
            }
        }
    }

    $keys = array_keys($used);
    sort($keys);

    return $keys;
}

$used = collectUsedKeys($root.'/Modules/Production');
$lpEn = loadAppKeys($root.'/Modules/LanguagePack/Languages/modules/Production/en/app.php');
$lpVi = loadAppKeys($root.'/Modules/LanguagePack/Languages/modules/Production/vi/app.php');
$modEn = loadAppKeys($root.'/Modules/Production/Resources/lang/en/app.php');
$modVi = loadAppKeys($root.'/Modules/Production/Resources/lang/vi/app.php');

$missingLpEn = array_values(array_diff($used, $lpEn));
$missingLpVi = array_values(array_diff($used, $lpVi));
$missingModEn = array_values(array_diff($used, $modEn));
$missingModVi = array_values(array_diff($used, $modVi));

echo 'Used keys: '.count($used).PHP_EOL;
echo 'Missing LanguagePack EN: '.count($missingLpEn).PHP_EOL;
if ($missingLpEn !== []) {
    echo implode(PHP_EOL, $missingLpEn).PHP_EOL;
}
echo 'Missing LanguagePack VI: '.count($missingLpVi).PHP_EOL;
if ($missingLpVi !== []) {
    echo implode(PHP_EOL, $missingLpVi).PHP_EOL;
}
echo 'Missing Module lang EN: '.count($missingModEn).PHP_EOL;
if ($missingModEn !== []) {
    echo implode(PHP_EOL, $missingModEn).PHP_EOL;
}
echo 'Missing Module lang VI: '.count($missingModVi).PHP_EOL;
if ($missingModVi !== []) {
    echo implode(PHP_EOL, $missingModVi).PHP_EOL;
}

if ($missingModEn !== [] || $missingModVi !== []) {
    echo PHP_EOL.'Fix: copy LanguagePack -> module Resources/lang:'.PHP_EOL;
    echo '  Modules/LanguagePack/Languages/modules/Production/{en,vi}/app.php'.PHP_EOL;
    echo '  -> Modules/Production/Resources/lang/{en,vi}/app.php'.PHP_EOL;
}
