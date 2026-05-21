<?php

declare(strict_types=1);

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    $rawMaterialIds = Product::query()->where('company_id', 1)->forBomRawMaterials()->pluck('id')->all();

    expect($outputIds)->toBe([1]);
    expect($componentIds)->toContain(2, 3);
    expect($componentIds)->not->toContain(1);
    expect($rawMaterialIds)->toBe([2]);
    expect($rawMaterialIds)->not->toContain(3);
});

it('rejects packaging as a production BOM component product id', function (): void {
    Product::query()->insert([
        ['company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'name' => 'Sugar', 'type' => 'raw_material', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'name' => 'Bag', 'type' => 'packaging', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $companyId = 1;
    $validator = Validator::make(
        [
            'items' => [
                ['component_product_id' => 3, 'quantity' => 1],
            ],
        ],
        [
            'items.*.component_product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereIn('type', ProductType::bomRawMaterialValues());
                }),
            ],
        ],
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('items.0.component_product_id'))->toBeTrue();
});

it('accepts raw material as a production BOM component product id', function (): void {
    Product::query()->insert([
        ['company_id' => 1, 'name' => 'FG', 'type' => 'goods', 'created_at' => now(), 'updated_at' => now()],
        ['company_id' => 1, 'name' => 'Sugar', 'type' => 'raw_material', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $companyId = 1;
    $validator = Validator::make(
        [
            'items' => [
                ['component_product_id' => 2, 'quantity' => 1],
            ],
        ],
        [
            'items.*.component_product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) use ($companyId): void {
                    $query->where('company_id', $companyId)->whereIn('type', ProductType::bomRawMaterialValues());
                }),
            ],
        ],
    );

    expect($validator->fails())->toBeFalse();
});
