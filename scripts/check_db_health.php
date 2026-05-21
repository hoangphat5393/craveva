<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
    'global_settings',
    'language_settings',
    'theme_settings',
    'companies',
    'product_sku_sequences',
    'product_unit_conversions',
    'migrations',
];

$failed = false;

foreach ($tables as $table) {
    try {
        $count = (int) DB::table($table)->count();
        echo $table.': '.$count.PHP_EOL;
        if ($table === 'global_settings' && $count < 1) {
            $failed = true;
        }
        if ($table === 'language_settings' && $count < 1) {
            $failed = true;
        }
    } catch (Throwable $e) {
        echo $table.': ERR '.$e->getMessage().PHP_EOL;
        $failed = true;
    }
}

try {
    cache()->forget('global_setting');
    $g = global_setting();
    echo 'global_setting() id: '.($g?->id ?? 'null').PHP_EOL;
    $key = $g?->ai_workspace_api_key;
    echo 'ai_workspace_api_key decrypt: '.(($key !== null && $key !== '') ? 'ok' : 'empty').PHP_EOL;
} catch (Throwable $e) {
    echo 'global_setting() ERR: '.$e->getMessage().PHP_EOL;
    $failed = true;
}

try {
    $superTheme = DB::table('theme_settings')->where('panel', 'superadmin')->first();
    echo 'theme_settings superadmin: '.($superTheme ? 'id='.$superTheme->id : 'MISSING').PHP_EOL;
} catch (Throwable $e) {
    echo 'theme_settings ERR: '.$e->getMessage().PHP_EOL;
    $failed = true;
}

exit($failed ? 1 : 0);
