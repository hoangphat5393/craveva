<?php

namespace Tests\Unit;

use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Observers\DeliveryOrderObserver;
use Tests\TestCase;

class DeliveryOrderObserverGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('purchase_orders', function ($table) {
            $table->id();
            $table->string('delivery_status')->default('not_started');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_orders', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('type')->nullable();
            $table->string('delivery_number')->nullable();
            $table->date('delivery_date')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('inbound_stock_applied')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('purchase_orders');
        parent::tearDown();
    }

    public function test_delivery_order_observer_skips_inbound_when_po_already_delivered_and_both_flags_enabled(): void
    {
        Config::set('warehouse.inbound_from_purchase_order_delivered', true);
        Config::set('warehouse.inbound_from_delivery_order_received', true);

        $poId = DB::table('purchase_orders')->insertGetId([
            'delivery_status' => 'delivered',
            'warehouse_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $doId = DB::table('delivery_orders')->insertGetId([
            'company_id' => 1,
            'purchase_order_id' => $poId,
            'type' => 'inbound',
            'status' => 'received',
            'inbound_stock_applied' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $observer = new DeliveryOrderObserver;
        $observer->saved(DeliveryOrder::findOrFail($doId));

        $inboundApplied = (bool) DB::table('delivery_orders')->where('id', $doId)->value('inbound_stock_applied');
        $this->assertFalse($inboundApplied);
    }
}
