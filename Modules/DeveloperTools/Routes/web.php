<?php

use Illuminate\Support\Facades\Route;
use Modules\DeveloperTools\Http\Controllers\DeveloperToolsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
||
| Tenant panel routes use /account (same middleware as routes/web.php account
| group). Legacy URLs at site root redirect with 301.
|
*/

Route::middleware('web')->group(function () {
    Route::redirect('developertools', '/account/developertools', 301);
    Route::redirect('developertools/codemap/view', '/account/developertools/codemap/view', 301);
    Route::redirect('funcnews', '/account/funcnews', 301);
    Route::redirect('funcnews/export', '/account/funcnews/export', 301);
});

Route::group([
    'middleware' => ['auth', 'multi-company-select', 'email_verified'],
    'prefix' => 'account',
], function () {
    Route::get('developertools', [DeveloperToolsController::class, 'index'])->name('developertools.index');
    Route::post('developertools/create-credential', [DeveloperToolsController::class, 'store'])->name('developertools.store');
    Route::delete('developertools/revoke/{id}', [DeveloperToolsController::class, 'destroy'])->name('developertools.destroy');

    Route::get('developertools/codemap/view', [DeveloperToolsController::class, 'codeMap'])->name('developertools.codemap');
    Route::post('developertools/codemap/scan', [DeveloperToolsController::class, 'scanCodeMap'])->name('developertools.codemap.scan');
    Route::get('developertools/codemap/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('developertools.codemap.export');

    Route::get('funcnews', [DeveloperToolsController::class, 'codeMap'])->name('funcnews.index');
    Route::get('funcnews/export', [DeveloperToolsController::class, 'exportCodeMap'])->name('funcnews.export');
});
