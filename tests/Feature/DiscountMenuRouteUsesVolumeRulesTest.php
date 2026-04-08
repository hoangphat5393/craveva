<?php

use Illuminate\Support\Facades\Route;

it('exposes pricing volume rules and volume discount calculate routes; legacy discount routes are absent', function () {
    expect(Route::has('pricing.volume_rules.index'))->toBeTrue();
    expect(Route::has('pricing.volume_discount.calculate'))->toBeTrue();
    expect(Route::has('discount.index'))->toBeFalse();
    expect(Route::has('discount.calculate'))->toBeFalse();
});
