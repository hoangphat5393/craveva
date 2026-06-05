<?php

declare(strict_types=1);

use App\Models\Estimate;
use App\Models\EstimateBomLine;
use App\Models\EstimateItem;
use App\Services\Estimates\EstimateVpMarginPolicy;
use Illuminate\Support\Facades\Config;

it('blocks vp approval when gross margin is below configured minimum', function (): void {
    Config::set('estimates_phase1.minimum_gross_margin_percent', 20.0);

    $estimate = new Estimate;
    $estimate->sub_total = 100.0;

    $bomLine = new EstimateBomLine;
    $bomLine->line_total = 9.0;

    $item = new EstimateItem;
    $item->type = 'item';
    $item->quantity = 10;

    $estimate->setRelation('bomLines', collect([$bomLine]));
    $estimate->setRelation('items', collect([$item]));

    $evaluation = (new EstimateVpMarginPolicy)->evaluateForVpApproval($estimate);

    expect($evaluation['allowed'])->toBeFalse();
    expect($evaluation['margin_percent'])->toBe(10.0);
});

it('allows vp approval when margin meets minimum', function (): void {
    Config::set('estimates_phase1.minimum_gross_margin_percent', 10.0);

    $estimate = new Estimate;
    $estimate->sub_total = 1000.0;

    $bomLine = new EstimateBomLine;
    $bomLine->line_total = 2.5;

    $item = new EstimateItem;
    $item->type = 'item';
    $item->quantity = 100;

    $estimate->setRelation('bomLines', collect([$bomLine]));
    $estimate->setRelation('items', collect([$item]));

    $evaluation = (new EstimateVpMarginPolicy)->evaluateForVpApproval($estimate);

    expect($evaluation['allowed'])->toBeTrue();
});
