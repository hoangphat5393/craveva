<?php

namespace Tests\Unit;

use Modules\Purchase\DataTables\PurchaseProductsDataTable;
use Modules\Purchase\Entities\PurchaseProduct;
use Tests\TestCase;

class PurchaseProductsDataTableTest extends TestCase
{
    public function test_query_includes_stock_on_hand_alias_for_sorting(): void
    {
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

        $dataTable = new PurchaseProductsDataTable;
        $query = $dataTable->query(new PurchaseProduct);

        $this->assertStringContainsString('as `stock_on_hand`', $query->toSql());
        $this->assertStringContainsString('`products`.`type`', $query->toSql());
    }

    public function test_columns_include_product_type(): void
    {
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

        $dataTable = new PurchaseProductsDataTable;
        $method = new \ReflectionMethod(PurchaseProductsDataTable::class, 'getColumns');
        $columns = $method->invoke($dataTable);

        $this->assertArrayHasKey(__('purchase::modules.product.type'), $columns);
    }
}
