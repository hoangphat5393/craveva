<?php

use App\Models\Company;
use Illuminate\Support\Facades\Cache;

afterEach(function (): void {
    Cache::forget('global_setting');
});

it('company light_logo_url does not throw when global_setting is null in cache', function (): void {
    Cache::put('global_setting', null);

    $company = new Company;
    $company->light_logo = null;

    expect($company->light_logo_url)->toBeString()->not->toBeEmpty();
});

it('company defaultLogo does not throw when global_setting is null in cache', function (): void {
    Cache::put('global_setting', null);

    $company = new Company;
    $company->logo = null;

    expect($company->defaultLogo())->toBeString()->not->toBeEmpty();
});

it('company favicon_url does not throw when global_setting is null in cache', function (): void {
    Cache::put('global_setting', null);

    $company = new Company;
    $company->favicon = null;

    expect($company->favicon_url)->toBeString()->not->toBeEmpty();
});

it('company masked_default_logo does not throw when global_setting is null in cache', function (): void {
    Cache::put('global_setting', null);

    $company = new Company;
    $company->logo = null;

    expect($company->masked_default_logo)->toBeString()->not->toBeEmpty();
});
