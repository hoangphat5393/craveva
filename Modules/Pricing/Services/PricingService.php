<?php

namespace Modules\Pricing\Services;

use Modules\Pricing\Entities\ClientProductPricing;
use Modules\Pricing\Entities\PricingTierItem;
use Modules\Pricing\Entities\CompanyCustomerPricing;
use Modules\Pricing\Entities\CompanyCustomerProductPricing;
use Modules\Pricing\Services\VolumeDiscountService;
use App\Models\Product;
use App\Models\ClientDetails;
use App\Models\User;

class PricingService
{
    /**
     * Calculate the final price for a product based on 2-Stage Pricing Logic.
     * Stage 1: Determine Base Unit Price (Client > Corporate > Tier > Base)
     * Stage 2: Apply Volume Discount
     */
    public function calculate(int $productId, int $clientId, int $quantity = 1): array
    {
        $product = Product::findOrFail($productId);
        $sellerCompanyId = $product->company_id;
        $buyerUser = User::find($clientId);
        $buyerCompanyId = $buyerUser ? $buyerUser->company_id : null;
        $basePrice = (float) $product->price;

        // --- STAGE 1: Determine Unit Price ---

        // 1. Client Product Pricing (Highest Priority)
        $specific = ClientProductPricing::where('client_id', $clientId)
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($specific) {
            $unit = $this->applyDiscount($basePrice, $specific->discount_type, $specific->discount_value, $specific->custom_price);
            return $this->applyStage2($unit, $quantity, $productId, $sellerCompanyId, 'client_product_pricing');
        }

        // 2. Corporate Pricing (Client Contract)
        if ($sellerCompanyId && $clientId) {
            $corporatePricing = $this->getClientContractPricing($sellerCompanyId, $clientId, $productId);
            if ($corporatePricing) {
                return $this->applyStage2($corporatePricing['unit_price'], $quantity, $productId, $sellerCompanyId, $corporatePricing['source']);
            }
        }

        // 3 & 4. Pricing Tiers
        $clientDetail = ClientDetails::where('user_id', $clientId)->first();
        $tierId = $clientDetail ? $clientDetail->pricing_tier_id : null;

        if ($tierId) {
            $tier = \Modules\Pricing\Entities\PricingTier::find($tierId);
            // Check validity
            if (
                $tier && $tier->is_active &&
                ($tier->valid_from === null || now()->toDateString() >= $tier->valid_from) &&
                ($tier->valid_to === null || now()->toDateString() <= $tier->valid_to)
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
                'quantity' => $quantity
            ]
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
            'volume_discount' => round($volumeDiscountAmount, 2)
        ];
    }

    private function applyDiscount(float $base, ?string $type, $value, $customPrice): float
    {
        $price = $base;

        if (!is_null($customPrice) && $customPrice > 0) {
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
        // Wrapper for compatibility, though calculate() now uses clientId (User ID)
        // If we don't have clientId, we can't fully resolve user-specific pricing.
        // For now, if no clientId is available, we might skip user-specific checks.
        // But calculate() signature requires int $clientId.

        // This method seems legacy or internal.
        // If we must support it, we need a way to mock clientId or overload calculate.
        // For safety, let's just use 0 or a dummy if needed, but ideally we should use calculate().

        return $this->calculate($productId, 0, $quantity);
    }

    public function getApplicableTiers(?int $companyId, ?int $customerCompanyId): array
    {
        $query = \Modules\Pricing\Entities\PricingTier::where('is_active', true);

        if ($companyId) {
            $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            });
        } else {
            $query->whereNull('company_id');
        }

        return $query->get()->toArray();
    }

    public function applyVolumeDiscount(array $items, ?int $companyId = null): array
    {
        $service = app(VolumeDiscountService::class);

        return $service->calculate($items, $companyId);
    }

    public function getClientContractPricing(int $sellerCompanyId, int $clientId, int $productId): ?array
    {
        $relation = CompanyCustomerPricing::query()
            ->where('company_id', $sellerCompanyId)
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();

        if (!$relation) {
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

        if ($relation->custom_discount_type && $relation->custom_discount_value) {
            $product = Product::find($productId);

            if (!$product) {
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
            $tier = \Modules\Pricing\Entities\PricingTier::find($relation->pricing_tier_id);
            if (
                $tier && $tier->is_active &&
                ($tier->valid_from === null || now()->toDateString() >= $tier->valid_from) &&
                ($tier->valid_to === null || now()->toDateString() <= $tier->valid_to)
            ) {

                $product = Product::find($productId);
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
}
