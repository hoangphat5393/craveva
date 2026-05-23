<?php

declare(strict_types=1);

use Modules\Production\Entities\ProductionBom;

it('builds compact order list label from code and version', function (): void {
    $bom = new ProductionBom([
        'id' => 12,
        'code' => 'banhkem2',
        'version' => '2',
    ]);

    expect($bom->listLabelForOrderIndex())->toBe('banhkem2 · 2');
});

it('falls back to code or version when only one is set', function (): void {
    expect((new ProductionBom(['id' => 1, 'code' => 'only-code', 'version' => '']))->listLabelForOrderIndex())
        ->toBe('only-code');

    expect((new ProductionBom(['id' => 2, 'code' => '', 'version' => 'v3']))->listLabelForOrderIndex())
        ->toBe('v3');
});
