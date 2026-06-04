<?php

declare(strict_types=1);

use App\Enums\ProductType;

it('forces purchase information for cost-only product types', function (): void {
    foreach (ProductType::costOnlyPurchasePricingValues() as $type) {
        expect(ProductType::forcesPurchaseInformationOnPurchaseForm($type))->toBeTrue()
            ->and(ProductType::hidesPurchaseInformationToggleOnPurchaseForm($type))->toBeTrue()
            ->and(ProductType::hidesB2bExtraPricingOnPurchaseForm($type))->toBeTrue()
            ->and(ProductType::usesTaxSectionAccordionOnPurchaseForm($type))->toBeTrue()
            ->and(ProductType::hidesProductMediaSectionOnPurchaseForm($type))->toBeTrue();
    }
});

it('uses collapsed b2b pricing only for manufactured goods', function (): void {
    expect(ProductType::usesCollapsedB2bExtraPricingOnPurchaseForm(ProductType::Goods->value))->toBeTrue()
        ->and(ProductType::usesCollapsedB2bExtraPricingOnPurchaseForm(ProductType::RawMaterial->value))->toBeFalse();
});

it('uses tax accordion on purchase form for all product types', function (): void {
    foreach (ProductType::values() as $type) {
        expect(ProductType::usesTaxSectionAccordionOnPurchaseForm($type))->toBeTrue();
    }
});

it('hides b2b extra pricing for service', function (): void {
    expect(ProductType::hidesB2bExtraPricingOnPurchaseForm(ProductType::Service->value))->toBeTrue();
});

it('hides inventory media and detail attributes for service', function (): void {
    expect(ProductType::hidesInventorySectionOnPurchaseForm(ProductType::Service->value))->toBeTrue()
        ->and(ProductType::hidesProductMediaSectionOnPurchaseForm(ProductType::Service->value))->toBeTrue()
        ->and(ProductType::hidesDescriptionAttributesOnPurchaseForm(ProductType::Service->value))->toBeTrue()
        ->and(ProductType::hidesInventorySectionOnPurchaseForm(ProductType::Goods->value))->toBeFalse();
});

it('clears unit id for service on save', function (): void {
    expect(ProductType::hidesUnitTypeOnPurchaseForm(ProductType::Service->value))->toBeTrue()
        ->and(ProductType::resolvePurchaseFormUnitId(ProductType::Service->value, 5))->toBeNull()
        ->and(ProductType::resolvePurchaseFormUnitId(ProductType::Goods->value, 5))->toBe(5);
});
