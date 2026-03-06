<?php

namespace Tests\Unit;

use App\Models\Product;
use Modules\Webhooks\Enums\ProductVariable;
use Modules\Webhooks\Jobs\SendWebhook;
use Modules\Webhooks\Observers\ProductObserver;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProductWebhookTest extends TestCase
{
    public function test_product_variable_enum_keys()
    {
        $this->assertEquals('##NAME##', ProductVariable::name->value);
        $this->assertEquals('##PRICE##', ProductVariable::price->value);
    }

    public function test_product_observer_dispatches_job()
    {
        Queue::fake();

        $product = Product::factory()->make([
            'id' => 1,
            'name' => 'Test Product',
            'company_id' => 1,
        ]);

        $observer = new ProductObserver();
        $observer->created($product);

        Queue::assertPushed(SendWebhook::class, function ($job) use ($product) {
            // Check properties via reflection since they are protected
            $reflection = new \ReflectionClass($job);
            
            $webhookForProperty = $reflection->getProperty('webhookFor');
            $webhookForProperty->setAccessible(true);
            $webhookFor = $webhookForProperty->getValue($job);

            $companyIdProperty = $reflection->getProperty('companyId');
            $companyIdProperty->setAccessible(true);
            $companyId = $companyIdProperty->getValue($job);
            
            $dataProperty = $reflection->getProperty('data');
            $dataProperty->setAccessible(true);
            $data = $dataProperty->getValue($job);

            return $webhookFor === 'Product' 
                && $companyId === $product->company_id
                && $data['name'] === $product->name
                && $data['event_action'] === 'created';
        });
    }
}
