<?php

use Illuminate\Support\Facades\Route;

it('exposes pricing.volume_rules.index and does not define discount.index', function () {
    expect(Route::has('pricing.volume_rules.index'))->toBeTrue();
    expect(Route::has('discount.index'))->toBeFalse();
});
