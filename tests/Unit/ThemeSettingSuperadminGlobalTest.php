<?php

use App\Models\Company;
use App\Models\ThemeSetting;
use App\Scopes\CompanyScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('keeps company_id null for superadmin global theme when session has a company', function () {
    $company = Company::query()->first();
    if ($company === null) {
        test()->markTestSkipped('No company in database.');
    }

    session(['company' => $company]);

    ThemeSetting::withoutGlobalScope(CompanyScope::class)
        ->where('panel', 'superadmin')
        ->whereNull('company_id')
        ->delete();

    $theme = ThemeSetting::withoutGlobalScope(CompanyScope::class)->create([
        'panel' => 'superadmin',
        'company_id' => null,
        'header_color' => '#ed4040',
        'sidebar_color' => '#292929',
        'sidebar_text_color' => '#cbcbcb',
        'link_color' => '#ffffff',
        'sidebar_theme' => 'dark',
        'enable_rounded_theme' => 0,
    ]);

    expect($theme->refresh()->company_id)->toBeNull();
});

it('returns a non-null theme from forSuperadminGlobalTheme', function () {
    $theme = ThemeSetting::forSuperadminGlobalTheme();

    expect($theme)->toBeInstanceOf(ThemeSetting::class);
    expect($theme->panel)->toBe('superadmin');
});
