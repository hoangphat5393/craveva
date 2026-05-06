<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionBatchController;
use Modules\Production\Http\Controllers\ProductionBomController;
use Modules\Production\Http\Controllers\ProductionFgQuantityPolicySettingController;
use Modules\Production\Http\Controllers\ProductionOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes — Production module (tenant /account)
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function (): void {
    Route::get('production/{path?}', function (Request $request, ?string $path = null) {
        $target = '/account/production/orders';
        if ($path !== null && $path !== '') {
            $target = '/account/production/'.$path;
        }
        $query = $request->getQueryString();

        return redirect($target.($query !== null && $query !== '' ? '?'.$query : ''), 301);
    })->where('path', '.*');
});

Route::group([
    'middleware' => ['auth', 'multi-company-select', 'email_verified'],
    'prefix' => 'account/production',
    'as' => 'production.',
], function (): void {
    Route::get('fg-quantity-policy', [ProductionFgQuantityPolicySettingController::class, 'index'])->name('fg-quantity-policy.index');
    Route::put('fg-quantity-policy', [ProductionFgQuantityPolicySettingController::class, 'update'])->name('fg-quantity-policy.update');

    Route::resource('boms', ProductionBomController::class);

    Route::resource('orders', ProductionOrderController::class)->except(['destroy']);

    Route::post('orders/{order}/release', [ProductionOrderController::class, 'release'])->name('orders.release');
    Route::post('orders/{order}/cancel', [ProductionOrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('batches/{batch}', [ProductionBatchController::class, 'show'])->name('batches.show');
    Route::get('batches/{batch}/trace', [ProductionBatchController::class, 'trace'])->name('batches.trace');
    Route::post('batches/{batch}/apply-planned-from-bom-snapshot', [ProductionBatchController::class, 'applyPlannedFromBomSnapshot'])->name('batches.apply-planned-from-bom-snapshot');
    Route::post('batches/{batch}/consumptions', [ProductionBatchController::class, 'storeConsumption'])->name('batches.consumptions.store');
    Route::post('batches/{batch}/consumptions/{consumption}/assign-warehouse-batch', [ProductionBatchController::class, 'assignConsumptionWarehouseBatch'])->name('batches.consumptions.assign-warehouse-batch');
    Route::post('batches/{batch}/post-consumptions', [ProductionBatchController::class, 'postConsumptions'])->name('batches.post-consumptions');
    Route::post('batches/{batch}/outputs', [ProductionBatchController::class, 'storeOutput'])->name('batches.outputs.store');
    Route::post('batches/{batch}/rework-orders', [ProductionBatchController::class, 'storeReworkOrder'])->name('batches.rework-orders.store');
    Route::post('batches/{batch}/rework-orders/{rework}/approve', [ProductionBatchController::class, 'approveReworkOrder'])->name('batches.rework-orders.approve');
    Route::post('batches/{batch}/rework-orders/{rework}/reject', [ProductionBatchController::class, 'rejectReworkOrder'])->name('batches.rework-orders.reject');
    Route::post('batches/{batch}/rework-orders/{rework}/complete', [ProductionBatchController::class, 'completeReworkOrder'])->name('batches.rework-orders.complete');
    Route::post('outputs/{output}/approve-variance', [ProductionBatchController::class, 'approveOutputVariance'])->name('outputs.approve-variance');
    Route::post('outputs/{output}/post-fg-receipt', [ProductionBatchController::class, 'postFgReceipt'])->name('outputs.post-fg-receipt');
});
