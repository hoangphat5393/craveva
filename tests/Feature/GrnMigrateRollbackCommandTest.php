<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
});

afterEach(function () {
    Schema::dropIfExists('grn_items');
    Schema::dropIfExists('grns');
});

it('rolls back migrated grn rows by manifest', function () {
    Schema::create('grns', function ($table) {
        $table->id();
        $table->unsignedBigInteger('legacy_delivery_order_id')->nullable()->unique();
    });
    Schema::create('grn_items', function ($table) {
        $table->id();
        $table->unsignedBigInteger('grn_id');
        $table->unsignedBigInteger('legacy_delivery_order_item_id')->nullable()->unique();
    });

    $headerId = DB::table('grns')->insertGetId(['legacy_delivery_order_id' => 11]);
    $itemId = DB::table('grn_items')->insertGetId([
        'grn_id' => $headerId,
        'legacy_delivery_order_item_id' => 101,
    ]);

    $manifestPath = base_path('storage/app/reports/grn-migrate-manifest-test.json');
    if (! is_dir(dirname($manifestPath))) {
        mkdir(dirname($manifestPath), 0777, true);
    }
    file_put_contents($manifestPath, json_encode([
        'created_header_ids' => [$headerId],
        'created_item_ids' => [$itemId],
    ], JSON_PRETTY_PRINT));

    $this->artisan('purchase:grn-migrate-rollback', [
        '--manifest' => 'storage/app/reports/grn-migrate-manifest-test.json',
        '--execute' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect(DB::table('grns')->count())->toBe(0);
    expect(DB::table('grn_items')->count())->toBe(0);
});
