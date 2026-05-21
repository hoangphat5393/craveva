<?php

declare(strict_types=1);

namespace Modules\Purchase\Http\Requests\Product\Concerns;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Validation\Rule;
use Modules\Purchase\Services\ProductSkuGenerator;

trait ResolvesProductSku
{
    protected function skuRulesForStore(int $companyId): array
    {
        if (ProductType::isService($this->input('type'))) {
            return ['nullable', 'string', 'max:255'];
        }

        return [
            'nullable',
            'string',
            'max:255',
            Rule::unique('products', 'sku')->where(function ($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            }),
        ];
    }

    protected function skuRulesForUpdate(int $companyId, int $productId): array
    {
        if (ProductType::isService($this->input('type'))) {
            return ['nullable', 'string', 'max:255'];
        }

        return [
            'nullable',
            'string',
            'max:255',
            Rule::unique('products', 'sku')
                ->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
                ->ignore($productId),
        ];
    }

    protected function mergeResolvedSku(): void
    {
        $generator = app(ProductSkuGenerator::class);
        $type = (string) $this->input('type', ProductType::Goods->value);
        $companyId = (int) company()->id;

        if ($this->route('purchase_product')) {
            $product = Product::find((int) $this->route('purchase_product'));
            if ($product === null) {
                return;
            }
            $sku = $generator->resolveForUpdate($product, $type, $this->input('sku'));
        } else {
            $sku = $generator->resolveForStore($companyId, $type, $this->input('sku'));
        }

        $this->merge(['sku' => $sku]);
    }
}
