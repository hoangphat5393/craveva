<?php

use Modules\Purchase\DataTables\PurchaseInventoryDataTable;

it('scopes colvis to data columns excluding checkbox and action columns', function () {
    $reflection = new ReflectionClass(PurchaseInventoryDataTable::class);
    $method = $reflection->getMethod('html');
    $source = file_get_contents($method->getFileName());

    expect($source)->toContain("'extend' => 'colvis'")
        ->and($source)->toContain(':not(:first):not(:last):not(.not-column-chooser)');
});
