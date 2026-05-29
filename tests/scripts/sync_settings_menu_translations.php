<?php

declare(strict_types=1);

/**
 * Remove stale Translation Manager (ltm_translations) rows that override
 * LanguagePack file translations for UX-007 settings menu keys.
 *
 * Usage: php tests/scripts/sync_settings_menu_translations.php
 */

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$keys = [
    'menu.financeSettings',
    'menu.settingsMenuGroupCompany',
    'menu.settingsMenuGroupPersonal',
    'menu.settingsMenuGroupSales',
    'menu.settingsMenuGroupProcurement',
    'menu.settingsMenuGroupFinanceTax',
    'menu.settingsMenuGroupHumanResources',
    'menu.settingsMenuGroupProjectsSupport',
    'menu.settingsMenuGroupSystem',
    'menu.settingsMenuGroupAdminTechnical',
];

if (! DB::getSchemaBuilder()->hasTable('ltm_translations')) {
    echo "ltm_translations table not found.\n";

    exit(0);
}

$deleted = DB::table('ltm_translations')
    ->where('group', 'app')
    ->whereIn('key', $keys)
    ->delete();

echo "Deleted {$deleted} ltm_translations row(s) for settings menu keys.\n";

foreach (['en', 'vi'] as $locale) {
    $source = module_path('LanguagePack', "Languages/app/{$locale}/app.php");
    $target = lang_path("{$locale}/app.php");

    if (! File::isFile($source) || ! File::isFile($target)) {
        echo "Skip publish {$locale}: source or target app.php missing.\n";

        continue;
    }

    File::copy($source, $target);
    echo "Published app.php for locale {$locale}.\n";
}

Artisan::call('cache:clear');
echo trim(Artisan::output())."\n";
echo "Done. Hard-refresh browser; UI locale follows user profile (AccountBaseController).\n";
