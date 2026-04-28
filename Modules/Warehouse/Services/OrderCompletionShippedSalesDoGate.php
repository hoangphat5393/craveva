<?php

namespace Modules\Warehouse\Services;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Modules\Purchase\Entities\SalesDoItem;

/**
 * When sales outbound mode is {@code shipment}, completing a sales order must not run ahead of
 * shipped quantities on Sales DO lines (aligned with FUNC_LOGIC sales flow).
 */
class OrderCompletionShippedSalesDoGate
{
    public function __construct(
        protected WarehouseFlowConfigService $flowConfig
    ) {}

    /**
     * @return string|null Translated error message, or null when completion is allowed.
     */
    public function blockingMessage(Order $order): ?string
    {
        $companyId = (int) ($order->company_id ?? 0);

        if (! $this->flowConfig->salesOutboundEnabled($companyId)) {
            return null;
        }

        if ($this->flowConfig->salesOutboundMode($companyId) !== 'shipment') {
            return null;
        }

        if (! Schema::hasTable('sales_dos') || ! Schema::hasTable('sales_do_items')) {
            return null;
        }

        $order->loadMissing(['items.product']);

        foreach ($order->items as $line) {
            if (! $this->lineRequiresShippedQuantity($line)) {
                continue;
            }

            $required = (float) $line->quantity;
            if ($required <= 0) {
                continue;
            }

            $shipped = $this->shippedQuantityForOrderItem((int) $order->id, (int) $line->id);
            if ($shipped + 0.0001 < $required) {
                return __('messages.orderCompleteRequiresShippedDo');
            }
        }

        return null;
    }

    public function isBlocked(Order $order): bool
    {
        return $this->blockingMessage($order) !== null;
    }

    protected function lineRequiresShippedQuantity(OrderItems $line): bool
    {
        if (! $line->product_id) {
            return false;
        }

        $product = $line->product;
        if (! $product instanceof Product) {
            $product = Product::withoutGlobalScopes()->find($line->product_id);
        }

        if (! $product) {
            return true;
        }

        if (($product->type ?? 'goods') === 'service') {
            return false;
        }

        if (isset($product->track_inventory) && ! $product->track_inventory) {
            return false;
        }

        return true;
    }

    protected function shippedQuantityForOrderItem(int $orderId, int $orderItemId): float
    {
        return (float) SalesDoItem::query()
            ->join('sales_dos', 'sales_dos.id', '=', 'sales_do_items.sales_do_id')
            ->where('sales_dos.order_id', $orderId)
            ->whereIn('sales_dos.status', ['shipped', 'delivered'])
            ->where('sales_do_items.order_item_id', $orderItemId)
            ->sum('sales_do_items.quantity_shipped');
    }
}
