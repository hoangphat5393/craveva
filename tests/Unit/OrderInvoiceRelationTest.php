<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderInvoiceRelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('orders', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('orders');
        parent::tearDown();
    }

    public function test_order_invoice_relation_returns_latest_invoice_and_supports_many(): void
    {
        $orderId = DB::table('orders')->insertGetId([
            'company_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('invoices')->insert([
            [
                'order_id' => $orderId,
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'order_id' => $orderId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $order = Order::findOrFail($orderId);
        $this->assertCount(2, $order->invoices);
        $this->assertSame($order->invoices->first()->id, $order->invoice?->id);
    }
}
