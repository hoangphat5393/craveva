<?php

use Illuminate\Support\Facades\Route;
use Modules\Pricing\Http\Controllers\PricingController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('pricing/preview', [PricingController::class, 'preview']);
});
