<?php

declare(strict_types=1);

use Modules\Warehouse\Entities\Warehouse;

it('renders warehouse ajax edit without duplicate getReadableApiError declaration', function (): void {
    $warehouse = new Warehouse([
        'name' => 'Test Warehouse',
        'code' => 'TW',
        'warehouse_type' => 'normal',
        'address' => 'Address',
        'description' => 'Notes',
        'status' => 'active',
        'is_default' => false,
    ]);
    $warehouse->id = 1;

    $html = view('warehouse::ajax.edit', ['warehouse' => $warehouse])->render();

    expect($html)->not->toContain('getReadableApiError');
    expect($html)->toContain('warehouseAjaxupdate-warehouse-form');
    expect($html)->toContain('handleApiFormError');
});
