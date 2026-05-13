<?php

declare(strict_types=1);

use App\Models\OrderItems;
use Illuminate\Support\Facades\Blade;

it('renders empty when order line has product_id but no unit relation', function () {
    $item = new OrderItems([
        'order_id' => 1,
        'product_id' => 99,
        'unit_id' => null,
        'item_name' => 'Test',
        'type' => 'item',
        'quantity' => 1,
        'unit_price' => 100,
        'amount' => 100,
    ]);

    $html = Blade::render('{{ $item->unit?->unit_type }}', ['item' => $item]);

    expect($html)->toBe('');
});
