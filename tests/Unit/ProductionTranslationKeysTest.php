<?php

declare(strict_types=1);

/**
 * Ensures production::app.* keys used in the Production module resolve (not raw key strings).
 */
it('resolves production app translation keys used in module code', function (): void {
    $keys = collectProductionAppKeysUsedInModule();

    expect($keys)->not->toBeEmpty();

    foreach (['en', 'vi'] as $locale) {
        app()->setLocale($locale);

        foreach ($keys as $key) {
            $fullKey = 'production::app.' . $key;
            $value = __($fullKey);

            expect($value)->not->toBe($fullKey, "Missing translation [{$locale}] for {$fullKey}");
        }
    }
});

it('resolves production settings menu and heading', function (): void {
    app()->setLocale('en');
    expect(__('production::app.productionSettingsMenu'))->toBe('Production')
        ->and(__('production::app.productionSettingsHeading'))->toBe('Production Settings');

    app()->setLocale('vi');
    expect(__('production::app.productionSettingsMenu'))->toBe('Sản xuất');
});

it('resolves bomComponentQtyAndUom column label', function (): void {
    app()->setLocale('en');
    expect(__('production::app.bomComponentQtyAndUom'))->toBe('Qty / 1 Manufactured Product');

    app()->setLocale('vi');
    expect(__('production::app.bomComponentQtyAndUom'))->toBe('SL + ĐVT / 1 SP SX');
});

/**
 * @return list<string>
 */
function collectProductionAppKeysUsedInModule(): array
{
    $used = [];
    $root = base_path('Modules/Production');
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
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
                if (str_ends_with($key, '.')) {
                    continue;
                }
                $used[$key] = true;
            }
        }
    }

    $keys = array_keys($used);
    sort($keys);

    return $keys;
}
