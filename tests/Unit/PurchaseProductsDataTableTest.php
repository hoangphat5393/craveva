<?php

namespace Tests\Unit;

use Modules\Purchase\DataTables\PurchaseProductsDataTable;
use Modules\Purchase\Entities\PurchaseProduct;
use Tests\TestCase;

class PurchaseProductsDataTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $fakeUser = new class
        {
            public int $id = 1;

            public ?int $company_id = null;

            public function permission(string $permission): string
            {
                return 'all';
            }
        };

        session([
            'user' => $fakeUser,
            'user_roles' => ['admin'],
        ]);
    }

    public function test_query_includes_stock_on_hand_alias_for_sorting(): void
    {
        $dataTable = new PurchaseProductsDataTable;
        $query = $dataTable->query(new PurchaseProduct);
        $sql = str_replace('"', '`', $query->toSql());

        $this->assertStringContainsString('as `stock_on_hand`', $sql);
        $this->assertStringContainsString('`products`.`type`', $sql);
    }

    public function test_columns_include_product_type(): void
    {
        $dataTable = new class extends PurchaseProductsDataTable
        {
            protected function customFieldColumns(): array
            {
                return [];
            }
        };

        $method = new \ReflectionMethod(PurchaseProductsDataTable::class, 'getColumns');
        $columns = $method->invoke($dataTable);

        $this->assertArrayHasKey(__('purchase::modules.product.type'), $columns);
        $this->assertArrayHasKey(__('purchase::modules.product.dataTableImage'), $columns);
        $this->assertArrayHasKey(__('purchase::modules.product.dataTablePriceInclusiveTax'), $columns);
        $this->assertArrayHasKey(__('purchase::modules.product.dataTableAllowClientPurchase'), $columns);
        $this->assertSame('Image', $columns[__('purchase::modules.product.dataTableImage')]['title']);
        $this->assertSame('Price (Inclusive Tax)', $columns[__('purchase::modules.product.dataTablePriceInclusiveTax')]['title']);
        $this->assertSame('Client Purchase', $columns[__('purchase::modules.product.dataTableAllowClientPurchase')]['title']);
    }

    public function test_stock_on_hand_formatter_shows_ledger_quantity_even_when_track_inventory_is_off(): void
    {
        $dataTable = new PurchaseProductsDataTable;
        $method = new \ReflectionMethod(PurchaseProductsDataTable::class, 'formatStockOnHand');
        $method->setAccessible(true);

        $row = (object) [
            'track_inventory' => 0,
            'stock_on_hand' => '12.0000',
        ];

        $this->assertSame(12.0, $method->invoke($dataTable, $row));
    }

    public function test_stock_on_hand_formatter_shows_zero_for_tracked_products_without_ledger(): void
    {
        $dataTable = new PurchaseProductsDataTable;
        $method = new \ReflectionMethod(PurchaseProductsDataTable::class, 'formatStockOnHand');
        $method->setAccessible(true);

        $row = (object) [
            'track_inventory' => 1,
            'stock_on_hand' => null,
        ];

        $this->assertSame(0.0, $method->invoke($dataTable, $row));
    }

    public function test_stock_on_hand_formatter_keeps_non_tracked_products_without_ledger_as_blank(): void
    {
        $dataTable = new PurchaseProductsDataTable;
        $method = new \ReflectionMethod(PurchaseProductsDataTable::class, 'formatStockOnHand');
        $method->setAccessible(true);

        $row = (object) [
            'track_inventory' => 0,
            'stock_on_hand' => null,
        ];

        $this->assertSame('--', $method->invoke($dataTable, $row));
    }
}
