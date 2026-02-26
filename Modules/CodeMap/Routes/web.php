<?php

use Illuminate\Support\Facades\Route;
use Modules\CodeMap\Http\Controllers\CodeMapController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('code-map', [CodeMapController::class, 'index'])->name('codemap.index');
    Route::post('code-map/scan', [CodeMapController::class, 'scan'])->name('codemap.scan');
    Route::get('code-map/export', [CodeMapController::class, 'export'])->name('codemap.export');
    Route::post('code-map/import', [CodeMapController::class, 'import'])->name('codemap.import');
});
