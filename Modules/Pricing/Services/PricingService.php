<?php

namespace Modules\Pricing\Services;

use App\Models\ClientDetails;
use App\Models\Product;
use App\Scopes\CompanyScope;
use Illuminate\Support\Carbon;
use Modules\Pricing\Entities\ClientProductPricing;
use Modules\Pricing\Entities\CompanyCustomerPricing;
use Modules\Pricing\Entities\CompanyCustomerProductPricing;
use Modules\Pricing\Entities\PricingTier;
use Modules\Pricing\Entities\PricingTierItem;

class PricingService
{
    /**
     * @var array<int, PricingTier|null>
     */
    private array $tierCache = [];

    /**
     * Calculate the final price for a product based on 2-Stage Pricing Logic.
     * Stage 1: Determine Base Unit Price (Client > Corporate > Tier > Base)
     * Stage 2: Apply Volume Discount
     */
    public function calculate(int $productId, int $clientId, int $quantity = 1): array
    {
        $product = Product::findOrFail($productId);
        $sellerCompanyId = $product->company_id;
        $basePrice = (float) $product->price;
        $pricingNow = $this->pricingNow();
        $pricingToday = $pricingNow->toDateString();

        // --- STAGE 1: Determine Unit Price ---

        // 1. Client Product Pricing (Highest Priority)
        $specific = ClientProductPricing::where('client_id', $clientId)
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->where(function ($q) use ($pricingNow) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $pricingNow);
            })
            ->where(function ($q) use ($pricingNow) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $pricingNow);
            })
            ->first();

        if ($specific) {
            $unit = $this->applyDiscount($basePrice, $specific->discount_type, $specific->discount_value, $specific->custom_price);

            return $this->applyStage2($unit, $quantity, $productId, $sellerCompanyId, 'client_product_pricing');
        }

        // 2. Corporate Pricing (Client Contract)
        if ($sellerCompanyId && $clientId) {
            $corporatePricing = $this->getClientContractPricing($sellerCompanyId, $clientId, $productId, $product);
            if ($corporatePricing) {
                return $this->applyStage2($corporatePricing['unit_price'], $quantity, $productId, $sellerCompanyId, $corporatePricing['source']);
            }
        }

        // 3 & 4. Pricing Tiers
        $clientDetail = ClientDetails::where('user_id', $clientId)->first();
        $tierId = $clientDetail ? $clientDetail->pricing_tier_id : null;

        if ($tierId) {
            $tier = $this->resolvePricingTierById($tierId);
            // Check validity
            if (
                $tier && $tier->is_active &&
                ($tier->valid_from === null || $pricingToday >= $tier->valid_from) &&
                ($tier->valid_to === null || $pricingToday <= $tier->valid_to)
            ) {

                // Check if tier belongs to seller or is platform (null)
                // Note: If sellerCompanyId is null (platform product), we accept any tier?
                // Usually platform products accept platform tiers.
                // If product is owned by a company, we only accept tiers from that company OR platform tiers if allowed?
                // Assuming Strict: Product Owner must match Tier Owner OR Tier is Platform (null)

                if ($tier->company_id === null || $tier->company_id == $sellerCompanyId) {
                    // Check for Product specific item in Tier
                    $tierItem = PricingTierItem::where('pricing_tier_id', $tierId)
                        ->where('product_id', $productId)
                        ->where('is_active', true)
                        ->first();

                    if ($tierItem) {
                        $unit = $this->applyDiscount($basePrice, $tierItem->discount_type, $tierItem->discount_value, null);

                        return $this->applyStage2($unit, $quantity, $productId, $sellerCompanyId, 'pricing_tier_item', $tierId);
                    }

                    // Tier Level Discount
                    if ($tier->discount_type && $tier->discount_value !== null) {
                        $unit = $this->applyDiscount($basePrice, $tier->discount_type, $tier->discount_value, null);

                        return $this->applyStage2($unit, $quantity, $productId, $sellerCompanyId, 'pricing_tier', $tierId);
                    }
                }
            }
        }

        // 5. Base Price
        return $this->applyStage2($basePrice, $quantity, $productId, $sellerCompanyId, 'base_price');
    }

    /**
     * Stage 2: Apply Volume Discount on the determined Unit Price
     */
    private function applyStage2(float $unitPrice, int $quantity, int $productId, ?int $sellerCompanyId, string $appliedSource, ?int $tierId = null): array
    {
        // Construct item array for service
        $items = [
            [
                'product_id' => $productId,
                'price' => $unitPrice,
                'quantity' => $quantity,
            ],
        ];

        // Apply Volume Discount
        $volumeDiscountResult = $this->applyVolumeDiscount($items, $sellerCompanyId);
        $volumeDiscountAmount = $volumeDiscountResult['value'] ?? 0;

        $lineTotal = $unitPrice * $quantity;
        $finalTotal = max(0, $lineTotal - $volumeDiscountAmount);

        return [
            'unit_price' => round($unitPrice, 2), // The Stage 1 Unit Price
            'price' => round($finalTotal, 2),     // The Final Total Price
            'applied' => $appliedSource,
            'tier_id' => $tierId,
            'volume_discount' => round($volumeDiscountAmount, 2),
        ];
    }

    private function applyDiscount(float $base, ?string $type, $value, $customPrice): float
    {
        $price = $base;

        if (! is_null($customPrice) && $customPrice > 0) {
            $price = (float) $customPrice;
        }

        if ($type === 'percentage') {
            return max(0, $price - ($price * ((float) $value / 100)));
        }

        if ($type === 'fixed') {
            return max(0, $price - (float) $value);
        }

        if ($type === 'specific_price') {
            return (float) $value;
        }

        return $price;
    }

    public function calculatePrice(int $productId, ?int $companyId, ?int $customerCompanyId, int $quantity = 1): array
    {
        throw new \BadMethodCallException('Use calculate($productId, $clientUserId, $quantity) so client-specific pricing can be applied.');
    }

    public function getApplicableTiers(?int $companyId, ?int $customerCompanyId): array
    {
        $query = PricingTier::withoutGlobalScope(CompanyScope::class)
            ->where('is_active', true);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            });
        } else {
            $query->whereNull('company_id');
        }

        return $query->orderByDesc('priority')->orderBy('id')->get()->toArray();
    }

    public function applyVolumeDiscount(array $items, ?int $companyId = null): array
    {
        $service = app(VolumeDiscountService::class);

        return $service->calculate($items, $companyId);
    }

    public function getClientContractPricing(int $sellerCompanyId, int $clientId, int $productId, ?Product $product = null): ?array
    {
        $relation = CompanyCustomerPricing::query()
            ->where('company_id', $sellerCompanyId)
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();

        if (! $relation) {
            return null;
        }

        $today = $this->pricingNow()->toDateString();
        $validFrom = $relation->valid_from ? Carbon::parse($relation->valid_from)->toDateString() : null;
        $validTo = $relation->valid_to ? Carbon::parse($relation->valid_to)->toDateString() : null;

        if ($validFrom && $today < $validFrom) {
            return null;
        }

        if ($validTo && $today > $validTo) {
            return null;
        }

        $productPricing = CompanyCustomerProductPricing::query()
            ->where('company_customer_pricing_id', $relation->id)
            ->where('product_id', $productId)
            ->first();

        if ($productPricing && $productPricing->custom_price !== null) {
            return [
                'unit_price' => (float) $productPricing->custom_price,
                'source' => 'company_customer_product_pricing',
            ];
        }

        if ($relation->custom_discount_type !== null && $relation->custom_discount_value !== null) {
            $product ??= Product::find($productId);

            if (! $product) {
                return null;
            }

            $base = (float) $product->price;
            $unit = $this->applyDiscount($base, $relation->custom_discount_type === 'fixed_amount' ? 'fixed' : 'percentage', $relation->custom_discount_value, null);

            return [
                'unit_price' => $unit,
                'source' => 'company_customer_pricing',
            ];
        }

        // Check for assigned Tier in Corporate Contract
        if ($relation->pricing_tier_id) {
            $tier = $this->resolvePricingTierById($relation->pricing_tier_id);
            if (
                $tier && $tier->is_active &&
                ($tier->valid_from === null || $today >= $tier->valid_from) &&
                ($tier->valid_to === null || $today <= $tier->valid_to)
            ) {

                $product ??= Product::find($productId);
                if (! $product) {
                    return null;
                }
                $basePrice = (float) $product->price;

                $tierItem = PricingTierItem::where('pricing_tier_id', $tier->id)
                    ->where('product_id', $productId)
                    ->where('is_active', true)
                    ->first();

                if ($tierItem) {
                    $unit = $this->applyDiscount($basePrice, $tierItem->discount_type, $tierItem->discount_value, null);

                    return [
                        'unit_price' => $unit,
                        'source' => 'corporate_pricing_tier_item',
                    ];
                }

                if ($tier->discount_type && $tier->discount_value !== null) {
                    $unit = $this->applyDiscount($basePrice, $tier->discount_type, $tier->discount_value, null);

                    return [
                        'unit_price' => $unit,
                        'source' => 'corporate_pricing_tier',
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Load a tier by primary key without CompanyScope so platform tiers (company_id null)
     * and tiers referenced by FK still resolve; caller enforces seller/platform rules.
     */
    private function resolvePricingTierById(int $tierId): ?PricingTier
    {
        if (! array_key_exists($tierId, $this->tierCache)) {
            $this->tierCache[$tierId] = PricingTier::withoutGlobalScope(CompanyScope::class)->find($tierId);
        }

        return $this->tierCache[$tierId];
    }

    private function pricingNow(): Carbon
    {
        $company = function_exists('company') ? company() : null;
        $timezone = $company && $company->timezone
            ? $company->timezone
            : config('app.timezone');

        return now($timezone);
    }
}
