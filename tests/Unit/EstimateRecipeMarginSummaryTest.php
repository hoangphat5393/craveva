<?php

declare(strict_types=1);

use App\Models\Estimate;
use App\Models\EstimateBomLine;
use App\Models\EstimateItem;
use App\Services\Estimates\EstimateRecipeMarginSummary;

it('calculates extended bom cost and gross margin from bom lines and commercial subtotal', function (): void {
    $estimate = new Estimate;
    $estimate->sub_total = 1000.0;
    $estimate->header_total_quantity = 100;

    $bomLine = new EstimateBomLine;
    $bomLine->line_total = 2.5;

    $estimate->setRelation('bomLines', collect([$bomLine]));
    $estimate->setRelation('items', collect());

    $summary = (new EstimateRecipeMarginSummary)->summarize($estimate);

    expect($summary['unit_bom_cost'])->toBe(2.5);
    expect($summary['order_quantity'])->toBe(100.0);
    expect($summary['extended_bom_cost'])->toBe(250.0);
    expect($summary['gross_margin_amount'])->toBe(750.0);
    expect($summary['gross_margin_percent'])->toBe(75.0);
});

it('falls back to sum of item quantities when header quantity is empty', function (): void {
    $estimate = new Estimate;
    $estimate->sub_total = 500.0;

    $item = new EstimateItem;
    $item->type = 'item';
    $item->quantity = 50;

    $estimate->setRelation('bomLines', collect([
        tap(new EstimateBomLine, fn (EstimateBomLine $line) => $line->line_total = 1.0),
    ]));
    $estimate->setRelation('items', collect([$item]));

    $summary = (new EstimateRecipeMarginSummary)->summarize($estimate);

    expect($summary['order_quantity'])->toBe(50.0);
    expect($summary['extended_bom_cost'])->toBe(50.0);
});
