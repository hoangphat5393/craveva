<?php

use Illuminate\Support\Facades\Route;
use Modules\LineIntegration\Http\Controllers\LineIntegrationController;

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
    Route::resource('lineintegration', LineIntegrationController::class)->names('lineintegration');
});
