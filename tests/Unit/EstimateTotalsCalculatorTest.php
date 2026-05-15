<?php

declare(strict_types=1);

use App\Models\Estimate;
use App\Models\Tax;
use App\Services\Estimates\EstimateTotalsCalculator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

uses(DatabaseTransactions::class);

it('sums line amounts into sub_total and total without tax', function (): void {
    $calculator = new EstimateTotalsCalculator;

    $result = $calculator->calculate(
        [
            ['amount' => 2900.0, 'taxes' => []],
            ['amount' => 2900.0, 'taxes' => []],
            ['amount' => 2900.0, 'taxes' => []],
            ['amount' => 2900.0, 'taxes' => []],
        ],
        0.0,
        'percent',
        'after_discount',
    );

    expect($result['sub_total'])->toBe(11600.0);
    expect($result['total'])->toBe(11600.0);
});

it('applies percent discount before tax when calculate_tax is after_discount', function (): void {
    if (! Schema::hasTable('taxes')) {
        test()->markTestSkipped('Taxes table is not migrated.');

        return;
    }

    $tax = Tax::query()->first();
    if (! $tax instanceof Tax) {
        test()->markTestSkipped('No tax row available for calculator test.');

        return;
    }

    $calculator = new EstimateTotalsCalculator;

    $result = $calculator->calculate(
        [
            ['amount' => 100.0, 'taxes' => [(int) $tax->id]],
        ],
        10.0,
        'percent',
        'after_discount',
    );

    expect($result['sub_total'])->toBe(100.0);
    expect($result['discount_amount'])->toBe(10.0);
    expect($result['total'])->toBeGreaterThan(90.0);
});

it('detects when persisted estimate totals are out of sync', function (): void {
    $estimate = new Estimate;
    $estimate->sub_total = 5800;
    $estimate->total = 5800;
    $estimate->discount = 0;
    $estimate->discount_type = 'percent';
    $estimate->calculate_tax = 'after_discount';

    $estimate->setRelation('items', collect([
        (object) ['type' => 'item', 'amount' => 2900.0, 'taxes' => null, 'field_order' => 1],
        (object) ['type' => 'item', 'amount' => 2900.0, 'taxes' => null, 'field_order' => 2],
        (object) ['type' => 'item', 'amount' => 2900.0, 'taxes' => null, 'field_order' => 3],
        (object) ['type' => 'item', 'amount' => 2900.0, 'taxes' => null, 'field_order' => 4],
    ]));

    $calculator = new EstimateTotalsCalculator;

    expect($calculator->totalsAreOutOfSync($estimate))->toBeTrue();
});
