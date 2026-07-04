<?php

use App\Models\Company;
use App\Models\ThemeSetting;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('companies', function ($table): void {
        $table->increments('id');
        $table->string('company_name')->nullable();
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('theme_settings', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id')->nullable();
        $table->string('panel');
        $table->string('header_color')->nullable();
        $table->string('sidebar_color')->nullable();
        $table->string('sidebar_text_color')->nullable();
        $table->string('link_color')->nullable();
        $table->string('sidebar_theme')->nullable();
        $table->boolean('enable_rounded_theme')->default(false);
        $table->timestamps();
    });

    DB::table('companies')->insert([
        'company_name' => 'Test Company',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('theme_settings');
    Schema::dropIfExists('companies');
    session()->forget('company');
});

it('keeps company_id null for superadmin global theme when session has a company', function () {
    $company = Company::query()->first();
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
