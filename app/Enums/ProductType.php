<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductType: string
{
    case Goods = 'goods';

    case Service = 'service';

    case RawMaterial = 'raw_material';

    case SemiFinished = 'semi_finished';

    case Packaging = 'packaging';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Display order for product forms and filters (Service last).
     *
     * @return list<self>
     */
    public static function casesForUi(): array
    {
        return [
            self::Goods,
            self::RawMaterial,
            self::SemiFinished,
            self::Packaging,
            self::Service,
        ];
    }

    /**
     * Product types that may define alternate units of measure (case, pack, …) on the product form.
     *
     * @return list<string>
     */
    public static function alternateUnitConversionValues(): array
    {
        return [
            self::RawMaterial->value,
            self::SemiFinished->value,
        ];
    }

    public static function supportsAlternateUnitConversions(?string $type): bool
    {
        return $type !== null
            && in_array($type, self::alternateUnitConversionValues(), true);
    }

    /**
     * @return list<string>
     */
    public static function bomComponentValues(): array
    {
        return [
            self::RawMaterial->value,
            self::SemiFinished->value,
            self::Packaging->value,
        ];
    }

    /**
     * Production BOM line components (raw materials only).
     *
     * @return list<string>
     */
    public static function bomRawMaterialValues(): array
    {
        return [
            self::RawMaterial->value,
        ];
    }

    /**
     * BOM component dropdown order (matches {@see self::bomComponentValues()}).
     *
     * @return list<self>
     */
    public static function bomComponentCases(): array
    {
        return [
            self::RawMaterial,
            self::SemiFinished,
            self::Packaging,
        ];
    }

    public static function isService(?string $type): bool
    {
        return $type === self::Service->value;
    }

    /**
     * Product types that use cost-only pricing on the purchase product form (no selling price).
     *
     * @return list<string>
     */
    public static function costOnlyPurchasePricingValues(): array
    {
        return [
            self::RawMaterial->value,
            self::SemiFinished->value,
            self::Packaging->value,
        ];
    }

    /**
     * Product types that do not show cost / purchase information on the product form.
     *
     * @return list<string>
     */
    public static function sellOnlyPurchasePricingValues(): array
    {
        return [
            self::Service->value,
        ];
    }

    /**
     * Production inputs and packaging are not sold — hide selling price on the product form.
     */
    public static function hidesSellingPriceOnPurchaseForm(?string $type): bool
    {
        return $type !== null
            && in_array($type, self::costOnlyPurchasePricingValues(), true);
    }

    /**
     * Services are billed by selling price only — hide cost price and Purchase Information.
     */
    public static function hidesCostPriceOnPurchaseForm(?string $type): bool
    {
        return $type !== null
            && in_array($type, self::sellOnlyPurchasePricingValues(), true);
    }

    /**
     * Alternate UOM price column shows cost (not selling price) for cost-only product types.
     */
    public static function uomPriceColumnUsesCost(?string $type): bool
    {
        return self::hidesSellingPriceOnPurchaseForm($type);
    }

    public static function isStockable(?string $type): bool
    {
        return $type !== null && $type !== self::Service->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Goods => __('purchase::modules.product.manufacturedProduct'),
            self::Service => __('purchase::modules.product.service'),
            self::RawMaterial => __('purchase::modules.product.rawMaterial'),
            self::SemiFinished => __('purchase::modules.product.semiFinished'),
            self::Packaging => __('purchase::modules.product.packaging'),
        };
    }

    public static function labelFor(?string $type): string
    {
        $enum = $type !== null ? self::tryFrom($type) : null;

        return $enum?->label() ?? ($type ?? '—');
    }
}
