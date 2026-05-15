<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite.database', ':memory:');
    Schema::create('products', function ($table): void {
        $table->id();
        $table->unsignedInteger('company_id');
        $table->string('name')->nullable();
        $table->string('type')->default('goods');
        $table->timestamps();
    });
});

it('scopes BOM output products to finished goods only', function (): void {
    Product::query()->insert([
        ['company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'name' => 'Sugar', 'type' => 'raw_material', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'name' => 'Bag', 'type' => 'packaging', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $outputIds = Product::query()->where('company_id', 1)->forBomOutput()->pluck('id')->all();
    $componentIds = Product::query()->where('company_id', 1)->forBomComponents()->pluck('id')->all();

    expect($outputIds)->toBe([1]);
    expect($componentIds)->toContain(2, 3);
    expect($componentIds)->not->toContain(1);
});
