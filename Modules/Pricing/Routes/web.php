<?php

use Illuminate\Support\Facades\Route;
use Modules\Pricing\Http\Controllers\ClientPricingController;
use Modules\Pricing\Http\Controllers\ClientTierController;
use Modules\Pricing\Http\Controllers\CompanyPricingController;
use Modules\Pricing\Http\Controllers\PricingImportController;
use Modules\Pricing\Http\Controllers\PricingTierController;
use Modules\Pricing\Http\Controllers\VolumeDiscountController;
use Modules\Pricing\Http\Controllers\VolumeRuleController;

Route::group(['middleware' => 'auth', 'prefix' => 'account/pricing'], function () {
    Route::post('tiers/apply-quick-action', [PricingTierController::class, 'applyQuickAction'])->name('pricing.tiers.apply_quick_action');
    Route::post('tiers/change-status', [PricingTierController::class, 'changeStatus'])->name('pricing.tiers.change_status');
    Route::get('tiers', [PricingTierController::class, 'index'])->name('pricing.tiers.index');
    Route::get('tiers/create', [PricingTierController::class, 'create'])->name('pricing.tiers.create');
    Route::post('tiers', [PricingTierController::class, 'store'])->name('pricing.tiers.store');
    Route::get('tiers/{id}/edit', [PricingTierController::class, 'edit'])->name('pricing.tiers.edit');
    Route::put('tiers/{id}', [PricingTierController::class, 'update'])->name('pricing.tiers.update');
    Route::delete('tiers/{id}', [PricingTierController::class, 'destroy'])->name('pricing.tiers.destroy');
    Route::get('tiers/{id}', [PricingTierController::class, 'show'])->name('pricing.tiers.show');

    // Tier Items
    Route::post('tiers/{id}/items', [PricingTierController::class, 'storeItem'])->name('pricing.tiers.items.store');
    Route::delete('tiers/items/{itemId}', [PricingTierController::class, 'destroyItem'])->name('pricing.tiers.items.destroy');
    Route::post('tiers/items/apply-quick-action', [PricingTierController::class, 'applyItemsQuickAction'])->name('pricing.tiers.items.apply_quick_action');

    Route::get('client-tiers', [ClientTierController::class, 'index'])->name('pricing.client_tiers.index');
    Route::get('client-tiers/{id}/edit', [ClientTierController::class, 'edit'])->name('pricing.client_tiers.edit');
    Route::put('client-tiers/{id}', [ClientTierController::class, 'update'])->name('pricing.client_tiers.update');

    Route::get('client-pricing', [ClientPricingController::class, 'index'])->name('pricing.client_pricing.index');
    Route::post('client-pricing/apply-quick-action', [ClientPricingController::class, 'applyQuickAction'])->name('pricing.client_pricing.apply_quick_action');
    Route::post('client-pricing/change-status', [ClientPricingController::class, 'changeStatus'])->name('pricing.client_pricing.change_status');
    Route::get('client-pricing/create', [ClientPricingController::class, 'create'])->name('pricing.client_pricing.create');
    Route::post('client-pricing', [ClientPricingController::class, 'store'])->name('pricing.client_pricing.store');
    Route::get('client-pricing/{id}/edit', [ClientPricingController::class, 'edit'])->name('pricing.client_pricing.edit');
    Route::put('client-pricing/{id}', [ClientPricingController::class, 'update'])->name('pricing.client_pricing.update');
    Route::delete('client-pricing/{id}', [ClientPricingController::class, 'destroy'])->name('pricing.client_pricing.destroy');

    Route::get('company-pricing', [CompanyPricingController::class, 'index'])->name('pricing.company_pricing.index');
    Route::post('company-pricing/apply-quick-action', [CompanyPricingController::class, 'applyQuickAction'])->name('pricing.company_pricing.apply_quick_action');
    Route::post('company-pricing/change-status', [CompanyPricingController::class, 'changeStatus'])->name('pricing.company_pricing.change_status');
    Route::get('company-pricing/create', [CompanyPricingController::class, 'create'])->name('pricing.company_pricing.create');
    Route::post('company-pricing', [CompanyPricingController::class, 'store'])->name('pricing.company_pricing.store');
    Route::get('company-pricing/{id}/edit', [CompanyPricingController::class, 'edit'])->name('pricing.company_pricing.edit');
    Route::put('company-pricing/{id}', [CompanyPricingController::class, 'update'])->name('pricing.company_pricing.update');
    Route::delete('company-pricing/{id}', [CompanyPricingController::class, 'destroy'])->name('pricing.company_pricing.destroy');

    Route::get('import', [PricingImportController::class, 'import'])->name('pricing.import.index');
    Route::post('import/store', [PricingImportController::class, 'importStore'])->name('pricing.import.store');
    Route::post('import/process', [PricingImportController::class, 'importProcess'])->name('pricing.import.process');

    Route::post('discount/calculate', [VolumeDiscountController::class, 'calculate'])->name('discount.calculate');

    Route::post('volume-rules/apply-quick-action', [VolumeRuleController::class, 'applyQuickAction'])->name('pricing.volume_rules.apply_quick_action');
    Route::post('volume-rules/change-status', [VolumeRuleController::class, 'changeStatus'])->name('pricing.volume_rules.change_status');
    Route::get('volume-rules', [VolumeRuleController::class, 'index'])->name('pricing.volume_rules.index');
    Route::get('volume-rules/create', [VolumeRuleController::class, 'create'])->name('pricing.volume_rules.create');
    Route::post('volume-rules', [VolumeRuleController::class, 'store'])->name('pricing.volume_rules.store');
    Route::get('volume-rules/{id}/edit', [VolumeRuleController::class, 'edit'])->name('pricing.volume_rules.edit');
    Route::put('volume-rules/{id}', [VolumeRuleController::class, 'update'])->name('pricing.volume_rules.update');
    Route::delete('volume-rules/{id}', [VolumeRuleController::class, 'destroy'])->name('pricing.volume_rules.destroy');
});
