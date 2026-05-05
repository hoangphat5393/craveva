<?php

use Modules\Purchase\DataTables\PurchaseInventoryDataTable;

it('keeps warehouse filter wiring in inventory datatable', function () {
    $reflection = new ReflectionClass(PurchaseInventoryDataTable::class);
    $source = file_get_contents($reflection->getFileName());

    expect($source)->toContain('request->warehouseId')
        ->and($source)->toContain('purchase_stock_adjustments.warehouse_id');
});
