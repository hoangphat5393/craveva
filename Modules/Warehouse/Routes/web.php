<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseController;
use Modules\Warehouse\Http\Controllers\WarehouseMovementController;
use Modules\Warehouse\Http\Controllers\WarehouseStockController;
use Modules\Warehouse\Http\Controllers\WarehouseTransferController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::group(['prefix' => 'warehouse'], function () {
        Route::get('import', [WarehouseController::class, 'importWarehouse'])->name('warehouse.import');
        Route::post('import', [WarehouseController::class, 'importStore'])->name('warehouse.import.store');
        Route::post('import/process', [WarehouseController::class, 'importProcess'])->name('warehouse.import.process');
    });

    Route::post('warehouse/update-order', [WarehouseController::class, 'updateOrder'])->name('warehouse.update-order');
    Route::post('warehouse/change-status', [WarehouseController::class, 'changeStatus'])->name('warehouse.change_status');
    Route::post('warehouse/apply-quick-action', [WarehouseController::class, 'applyQuickAction'])->name('warehouse.apply_quick_action');
    Route::resource('warehouse', WarehouseController::class)->names('warehouse');
    Route::get('warehouse-movements', [WarehouseMovementController::class, 'index'])->name('warehouse.movements.index');
    Route::resource('warehouse-stock', WarehouseStockController::class)->names('warehouse.stock');
    Route::get('warehouse-transfer', [WarehouseTransferController::class, 'create'])->name('warehouse.transfer.create');
    Route::post('warehouse-transfer', [WarehouseTransferController::class, 'store'])->name('warehouse.transfer.store');
});
