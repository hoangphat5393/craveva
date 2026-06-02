<?php

use Modules\Production\DataTables\ProductionBomsDataTable;

it('scopes colvis to visible columns excluding export-only and action columns', function () {
    $reflection = new ReflectionClass(ProductionBomsDataTable::class);
    $method = $reflection->getMethod('html');
    $source = file_get_contents($method->getFileName());

    expect($source)->toContain("'extend' => 'colvis'")
        ->and($source)->toContain(':not(:last):not(.not-column-chooser)')
        ->and($source)->toContain('is_default_display')
        ->and($source)->not->toContain('is_default_export')
        ->and($source)->not->toMatch("/'is_default'\s*=>/");
});
