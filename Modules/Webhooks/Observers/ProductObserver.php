<?php

namespace Modules\Webhooks\Observers;

use App\Models\Product;
use Modules\Webhooks\Jobs\SendWebhook;

class ProductObserver
{
    public function created(Product $product)
    {
        \Illuminate\Support\Facades\Log::info('Modules\Webhooks\Observers\ProductObserver::created called for Product ID: ' . $product->id);
        \Illuminate\Support\Facades\Log::info('Product Company ID: ' . ($product->company_id ?? 'NULL'));
        $this->sendWebhook($product, 'created');
    }

    public function updated(Product $product)
    {
        $this->sendWebhook($product, 'updated');
    }

    public function deleted(Product $product)
    {
        $this->sendWebhook($product, 'deleted');
    }

    private function sendWebhook(Product $product, string $action)
    {
        // Eager load necessary relations if not already loaded
        if (!$product->relationLoaded('category')) {
            $product->load('category');
        }
        if (!$product->relationLoaded('subCategory')) {
            $product->load('subCategory');
        }
        if (!$product->relationLoaded('unit')) {
            $product->load('unit');
        }
        if (!$product->relationLoaded('tax')) {
            $product->load('tax');
        }

        $data = $product->toArray();

        // Add action to payload to distinguish events
        $data['event_action'] = $action;

        // Flatten some related data for easier webhook consumption
        $data['category_name'] = $product->category ? $product->category->category_name : null;
        $data['sub_category_name'] = $product->subCategory ? $product->subCategory->category_name : null;
        $data['unit_type'] = $product->unit ? $product->unit->unit_type : null;

        // Handle taxes specifically if needed (it's often a collection or complex structure)
        if ($product->tax) {
            $data['tax_info'] = $product->tax->toArray();
        }

        SendWebhook::dispatch($data, 'Product', $product->company_id)
            ->delay(5)
            ->onQueue('default');
    }
}
