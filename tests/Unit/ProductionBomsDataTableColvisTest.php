<?php

use Modules\Production\DataTables\ProductionBomsDataTable;

it('scopes colvis to visible columns excluding action columns', function () {
    $reflection = new ReflectionClass(ProductionBomsDataTable::class);
    $method = $reflection->getMethod('html');
    $source = file_get_contents($method->getFileName());

    expect($source)->toContain("'extend' => 'colvis'")
        ->and($source)->toContain(':not(:last):not(.not-column-chooser)');
});

it('gates default-for-manufactured-product column behind production ui config', function () {
    $reflection = new ReflectionClass(ProductionBomsDataTable::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->toContain('show_bom_default_for_manufactured_product_ui')
        ->and($source)->toContain('showBomDefaultColumn');
});
