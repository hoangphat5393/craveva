<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseCompanyFlowSettingController;
use Modules\Warehouse\Http\Controllers\WarehouseController;
use Modules\Warehouse\Http\Controllers\WarehouseMovementController;
use Modules\Warehouse\Http\Controllers\WarehouseStockController;
use Modules\Warehouse\Http\Controllers\WarehouseTransferController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Tenant panel routes use /account (same middleware as routes/web.php account
| group). Legacy URLs at site root redirect with 301.
|
*/

Route::middleware('web')->group(function () {
    Route::get('warehouse/{path?}', function (Request $request, ?string $path = null) {
        $target = '/account/warehouse';
        if ($path !== null && $path !== '') {
            $target .= '/'.$path;
        }
        $query = $request->getQueryString();

        return redirect($target.($query !== null && $query !== '' ? '?'.$query : ''), 301);
    })->where('path', '.*');

    Route::get('warehouse-stock/{path?}', function (Request $request, ?string $path = null) {
        $target = '/account/warehouse-stock';
        if ($path !== null && $path !== '') {
            $target .= '/'.$path;
        }
        $query = $request->getQueryString();

        return redirect($target.($query !== null && $query !== '' ? '?'.$query : ''), 301);
    })->where('path', '.*');

    Route::get('warehouse-movements', function (Request $request) {
        $query = $request->getQueryString();

        return redirect('/account/warehouse-movements'.($query !== null && $query !== '' ? '?'.$query : ''), 301);
    });

    Route::redirect('warehouse-transfer', '/account/warehouse-transfer', 301);
});

Route::group([
    'middleware' => ['auth', 'multi-company-select', 'email_verified'],
    'prefix' => 'account',
], function () {
    Route::group(['prefix' => 'warehouse'], function () {
        Route::get('import', [WarehouseController::class, 'importWarehouse'])->name('warehouse.import');
        Route::post('import', [WarehouseController::class, 'importStore'])->name('warehouse.import.store');
        Route::post('import/process', [WarehouseController::class, 'importProcess'])->name('warehouse.import.process');
    });

    Route::post('warehouse/update-order', [WarehouseController::class, 'updateOrder'])->name('warehouse.update-order');
    Route::post('warehouse/change-status', [WarehouseController::class, 'changeStatus'])->name('warehouse.change_status');
    Route::post('warehouse/apply-quick-action', [WarehouseController::class, 'applyQuickAction'])->name('warehouse.apply_quick_action');
    Route::get('warehouse/company-flow-settings', [WarehouseCompanyFlowSettingController::class, 'index'])
        ->name('warehouse.company-flow-settings.index');
    Route::put('warehouse/company-flow-settings', [WarehouseCompanyFlowSettingController::class, 'update'])
        ->name('warehouse.company-flow-settings.update');
    Route::resource('warehouse', WarehouseController::class)->names('warehouse');
    Route::get('warehouse-movements', [WarehouseMovementController::class, 'index'])->name('warehouse.movements.index');
    Route::resource('warehouse-stock', WarehouseStockController::class)->names('warehouse.stock');
    Route::get('warehouse-transfer', [WarehouseTransferController::class, 'create'])->name('warehouse.transfer.create');
    Route::post('warehouse-transfer', [WarehouseTransferController::class, 'store'])->name('warehouse.transfer.store');
});
