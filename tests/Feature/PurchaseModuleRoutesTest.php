<?php

use Illuminate\Support\Facades\Route;

it('registers core Purchase module web route names', function () {
    $names = [
        'purchase-products.index',
        'purchase-order.index',
        'bills.index',
        'vendors.index',
        'vendor-payments.index',
        'vendor-credits.index',
        'purchase-inventory.index',
        'grn.index',
        'sales-do.index',
        'delivery-orders.index',
        'sales-shipments.index',
        'reports.index',
        'vendor-cateogory.index',
    ];

    foreach ($names as $name) {
        expect(Route::has($name))->toBeTrue("Missing route: {$name}");
    }
});

it('wires the WUP-08 warehouse movement report into purchase reports', function () {
    expect(file_get_contents(base_path('Modules/Purchase/Http/Controllers/ReportsController.php')))
        ->toContain("case 'warehouse-movement-report':")
        ->toContain('warehouseMovementReport()')
        ->toContain('purchase::reports.ajax.warehouse-movement-report')
        ->toContain('WarehouseMovementsDataTable')
        ->toContain("user()->permission('view_warehouse_stock')");

    expect(file_get_contents(base_path('Modules/Purchase/Resources/views/reports/index.blade.php')))
        ->toContain('tab=warehouse-movement-report')
        ->toContain("__('purchase::modules.reports.warehouseMovementReport')");

    expect(file_exists(base_path('Modules/Purchase/Resources/views/reports/ajax/warehouse-movement-report.blade.php')))->toBeTrue();
});

it('locks WUP-08 warehouse movement report filters export and reference mapping', function () {
    $view = file_get_contents(base_path('Modules/Purchase/Resources/views/reports/ajax/warehouse-movement-report.blade.php'));
    $dataTable = file_get_contents(base_path('Modules/Warehouse/DataTables/WarehouseMovementsDataTable.php'));

    expect($view)
        ->toContain('id="warehouseMovementDateRange"')
        ->toContain('id="warehouse-movement-report-warehouse"')
        ->toContain('id="warehouse-movement-report-type"')
        ->toContain('id="warehouse-movement-report-reference-type"')
        ->toContain('id="warehouse-movement-report-reference-id"')
        ->toContain('id="warehouse-movement-report-search"')
        ->toContain('data.startDate = startDate;')
        ->toContain('data.endDate = endDate;')
        ->toContain("data.warehouse_id = $('#warehouse-movement-report-warehouse').val();")
        ->toContain("data.movement_type = $('#warehouse-movement-report-type').val();")
        ->toContain("data.reference_type = $('#warehouse-movement-report-reference-type').val();")
        ->toContain("data.reference_id = $('#warehouse-movement-report-reference-id').val();")
        ->toContain("data.searchText = $('#warehouse-movement-report-search').val();");

    expect($dataTable)
        ->toContain("Button::make([\n                'extend' => 'excel'")
        ->toContain("where('company_id', (int) \$companyId)")
        ->toContain("where('movement_type', \$request->movement_type)")
        ->toContain("whereIn('reference_type', \$this->referenceTypeVariants")
        ->toContain("where('reference_id', (int) \$request->reference_id)")
        ->toContain("where('stock_movements.created_at', '>=', \$startDate)")
        ->toContain("where('stock_movements.created_at', '<=', \$endDate)")
        ->toContain("query->where('name', 'like', \$term)")
        ->toContain("'Modules\\\\Purchase\\\\Entities\\\\SalesDo'")
        ->toContain("'Modules\\\\Purchase\\\\Entities\\\\DeliveryOrder'")
        ->toContain("'Modules\\\\Production\\\\Entities\\\\ProductionBatch'");
});
