<?php

declare(strict_types=1);

$openingStockHelpKeys = [
    'openingStockPopoverHelp',
    'openingStockFieldHelp',
    'openingStockFieldHelpExtended',
    'openingStockNoWarehouseAlert',
];

it('defines opening stock help keys in LanguagePack English', function () use ($openingStockHelpKeys): void {
    $translations = require base_path('Modules/LanguagePack/Languages/modules/Purchase/en/app.php');

    foreach ($openingStockHelpKeys as $key) {
        expect($translations)->toHaveKey($key)
            ->and($translations[$key])->toBeString()->not->toBeEmpty();
    }

    expect($translations['openingStockPopoverHelp'])->toContain('Opening balance');
});

it('defines opening stock help keys in LanguagePack Vietnamese', function () use ($openingStockHelpKeys): void {
    $translations = require base_path('Modules/LanguagePack/Languages/modules/Purchase/vi/app.php');

    foreach ($openingStockHelpKeys as $key) {
        expect($translations)->toHaveKey($key)
            ->and($translations[$key])->toBeString()->not->toBeEmpty();
    }

    expect($translations['openingStockPopoverHelp'])->toContain('Tồn đầu kỳ');
    expect($translations['openingStockFieldHelpExtended'])->toContain('Add Inventory');
});
