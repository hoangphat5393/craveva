<?php

use Illuminate\Support\Facades\Route;
use Modules\FuncNews\Http\Controllers\FuncNewsController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('func-news', [FuncNewsController::class, 'index'])->name('funcnews.index');
    Route::post('func-news/scan', [FuncNewsController::class, 'scan'])->name('funcnews.scan');
    Route::get('func-news/export', [FuncNewsController::class, 'export'])->name('funcnews.export');
    Route::post('func-news/import', [FuncNewsController::class, 'import'])->name('funcnews.import');
});
