<?php

it('exposes de-abbreviated production batch labels in en and vi', function (): void {
    $keys = [
        'rawMaterialsUsed',
        'addRawMaterialUsedLine',
        'plannedQuantityLine',
        'plannedQuantityLineShadow',
        'rawMaterialsDeductedAt',
        'finishedGoodsPostedAt',
        'rawMaterialProduct',
        'rawMaterialBatch',
        'rawMaterialBatchId',
        'postRawMaterialUsage',
        'postRawMaterialUsageRequiresLines',
        'traceabilityRawMaterials',
        'rawMaterialsUsedTraceHeading',
        'outboundRawMaterialMovements',
        'inboundFinishedGoodsMovements',
        'bomComponentQty',
        'quantityPerUnit',
        'postFgReceipt',
        'requestRework',
    ];

    foreach (['en', 'vi'] as $locale) {
        app()->setLocale($locale);

        foreach ($keys as $key) {
            $value = __("production::app.{$key}");

            expect($value)->not->toBe("production::app.{$key}");
        }
    }

    app()->setLocale('en');

    expect(__('production::app.rawMaterialsUsed'))->not->toContain('(RM)')
        ->and(__('production::app.postRawMaterialUsageRequiresLines'))->not->toContain('RM consumption')
        ->and(__('production::app.bomComponentQty'))->toBe('Qty / 1 manufactured product')
        ->and(__('production::app.bomComponentQty'))->not->toContain('FG')
        ->and(__('production::app.requestRework'))->toBe('Send for rework');
});
