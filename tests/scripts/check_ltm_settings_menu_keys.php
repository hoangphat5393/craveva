<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$rows = DB::table('ltm_translations')
    ->where('group', 'app')
    ->where(function ($query): void {
        $query->where('key', 'like', 'menu.settingsMenuGroup%')
            ->orWhere('key', 'menu.financeSettings');
    })
    ->orderBy('locale')
    ->orderBy('key')
    ->get(['id', 'locale', 'key', 'value']);

$viRows = $rows->where('locale', 'vi')->values();
$enRows = $rows->where('locale', 'en')->values();

echo 'Total rows: '.$rows->count().PHP_EOL;
echo 'VI rows: '.json_encode($viRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'EN rows: '.json_encode($enRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
