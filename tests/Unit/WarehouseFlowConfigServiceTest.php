<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Warehouse\Entities\WarehouseCompanyFlowSetting;
use Modules\Warehouse\Services\WarehouseFlowConfigService;
use Tests\TestCase;

class WarehouseFlowConfigServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('companies', function ($table) {
            $table->increments('id');
        });

        Schema::create('warehouse_company_flow_settings', function ($table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('strict_unit_conversion')->default(false);
            $table->boolean('inbound_from_purchase_order_delivered')->default(true);
            $table->boolean('inbound_from_delivery_order_received')->default(false);
            $table->boolean('sales_outbound_enabled')->default(true);
            $table->string('sales_outbound_mode', 32)->default('shipment');
            $table->boolean('ai_order_webhook_check_stock')->default(true);
            $table->timestamps();
            $table->unique('company_id');
        });

        DB::table('companies')->insert([
            ['id' => 1],
            ['id' => 2],
        ]);

        Config::set('warehouse.allow_negative_stock', false);
        Config::set('warehouse.sales_outbound_mode', 'shipment');
        Config::set('warehouse.ai_order_webhook_check_stock', true);
    }

    public function test_company_without_row_uses_config_fallback(): void
    {
        $svc = new WarehouseFlowConfigService;

        $this->assertFalse($svc->allowNegativeStock(2));
        $this->assertSame('shipment', $svc->salesOutboundMode(2));
        $this->assertTrue($svc->aiOrderWebhookCheckStock(2));
    }

    public function test_company_with_row_uses_database_values(): void
    {
        WarehouseCompanyFlowSetting::query()->create([
            'company_id' => 1,
            'allow_negative_stock' => true,
            'strict_unit_conversion' => false,
            'inbound_from_purchase_order_delivered' => true,
            'inbound_from_delivery_order_received' => false,
            'sales_outbound_enabled' => true,
            'sales_outbound_mode' => 'invoice',
            'ai_order_webhook_check_stock' => false,
        ]);

        $svc = new WarehouseFlowConfigService;

        $this->assertTrue($svc->allowNegativeStock(1));
        $this->assertSame('invoice', $svc->salesOutboundMode(1));
        $this->assertFalse($svc->aiOrderWebhookCheckStock(1));
    }

    public function test_null_company_id_reads_config_only(): void
    {
        Config::set('warehouse.allow_negative_stock', true);

        $svc = new WarehouseFlowConfigService;

        $this->assertTrue($svc->allowNegativeStock(null));
    }
}
