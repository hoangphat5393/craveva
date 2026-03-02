<?php

use Illuminate\Support\Facades\Route;
use Modules\DeveloperTools\Http\Controllers\DeveloperToolsController;

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

Route::group(['middleware' => ['auth']], function () {
    // Explicit routes for DeveloperTools
    Route::get('developertools', [DeveloperToolsController::class, 'index'])->name('developertools.index');
    Route::post('developertools/create-credential', [DeveloperToolsController::class, 'store'])->name('developertools.store');
    Route::delete('developertools/revoke/{id}', [DeveloperToolsController::class, 'destroy'])->name('developertools.destroy');

    // CodeMap routes merged into DeveloperTools
    Route::get('developertools/codemap/view', [DeveloperToolsController::class, 'codeMap'])->name('developertools.codemap');
    Route::post('developertools/codemap/scan', [DeveloperToolsController::class, 'scanCodeMap'])->name('developertools.codemap.scan');
    Route::get('developertools/codemap/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('developertools.codemap.export');

    Route::get('funcnews', [DeveloperToolsController::class, 'codeMap'])->name('funcnews.index');
    Route::get('funcnews/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('funcnews.export');
});
