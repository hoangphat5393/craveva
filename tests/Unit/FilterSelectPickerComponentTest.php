<?php

use Illuminate\Support\Facades\Blade;

it('renders filter select with bootstrap-select picker attributes', function () {
    $html = Blade::render('<x-filters.select name="category_id" id="category_id"><option value="all">All</option></x-filters.select>');

    expect($html)
        ->toContain('class="form-control select-picker"')
        ->toContain('data-live-search="true"')
        ->toContain('data-container="body"')
        ->toContain('data-size="8"')
        ->toContain('name="category_id"')
        ->toContain('id="category_id"');
});

it('allows disabling live search on filter select', function () {
    $html = Blade::render('<x-filters.select :live-search="false" name="status" id="status"><option>All</option></x-filters.select>');

    expect($html)->not->toContain('data-live-search="true"');
});

it('purchase products filter uses filter select component', function () {
    $path = module_path('Purchase', 'Resources/views/purchase-products/index.blade.php');

    expect(file_get_contents($path))->toContain('<x-filters.select');
});
