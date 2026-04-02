@php
    $purchaseViewVendorPermission = user()->permission('view_vendor');
    $purchaseViewOrderPermission = user()->permission('view_purchase_order');
    $purchaseViewBillPermission = user()->permission('view_bill');
    $purchaseViewCreditPermission = user()->permission('view_vendor_credit');
    $purchaseViewInventoryPermission = user()->permission('view_inventory');
    $purchaseViewOrderReportPermission = user()->permission('view_order_report');
    $purchaseViewPaymentPermission = user()->permission('view_vendor_payment');
    $canViewGrn = \Modules\Purchase\Support\FlowPermission::allowsAlias('grn.view');
    $canViewSalesDo = \Modules\Purchase\Support\FlowPermission::allowsAlias('sales_do.view');
    $flowNamingMode = config('purchase.flow_naming_mode', 'compat_v2');
    $grnLabel = $flowNamingMode === 'legacy' ? __('purchase::app.menu.deliveryOrders') : __('purchase::app.menu.goodsReceivedNote');
    $salesDoLabel = $flowNamingMode === 'legacy' ? __('purchase::app.menu.salesShipments') : __('purchase::app.menu.saleDeliveryOrder');
    $grnRouteName = $flowNamingMode === 'legacy' ? 'delivery-orders.index' : 'grn.index';
    $salesDoRouteName = $flowNamingMode === 'legacy' ? 'sales-shipments.index' : 'sales-do.index';
    // Warehouse module: migration chỉ gán quyền vào role "admin"; employee thường chưa có view_warehouses → hiển thị kèm quyền Inventory.
    $viewWarehouses = user()->permission('view_warehouses');
    $addWarehouses = user()->permission('add_warehouses');
    $viewWarehouseStock = user()->permission('view_warehouse_stock');
    $addWarehouseStock = user()->permission('add_warehouse_stock');
    $manageWarehouseTransfer = user()->permission('manage_warehouse_transfer');
    $canSeeWarehouseMaster = ($viewWarehouses && $viewWarehouses != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $canSeeWarehouseStockUi = ($viewWarehouseStock && $viewWarehouseStock != 'none') || ($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '');
    $operationsMenuActive = request()->routeIs('orders.*', 'vendors.*', 'purchase-products.*', 'purchase-order.*', 'delivery-orders.*', 'sales-shipments.*', 'bills.*', 'vendor-payments.*', 'vendor-credits.*', 'purchase-inventory.*', 'warehouse.*', 'warehouse.stock.*', 'warehouse.transfer.*', 'warehouse.movements.*');
@endphp
@if (in_array(\Modules\Purchase\Entities\PurchaseManagementSetting::MODULE_NAME, user_modules()) &&
        ($purchaseViewVendorPermission != 'none' ||
            $purchaseViewOrderPermission != 'none' ||
            $purchaseViewBillPermission != 'none' ||
            $purchaseViewCreditPermission != 'none' ||
            $purchaseViewInventoryPermission != 'none' ||
            $purchaseViewOrderReportPermission != 'none' ||
            $purchaseViewPaymentPermission != 'none' ||
            $canViewSalesDo ||
            $canViewGrn ||
            (in_array('products', user_modules()) && $sidebarUserPermissions['view_product'] != 'none') ||
            (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 'none')))

    <x-menu-item icon="wallet" :text="__('app.menu.operations')" :addon="App::environment('demo')" :active="$operationsMenuActive">
        <x-slot name="iconPath">
            <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.6.6 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.6.6 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.6.6 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535z" />
            <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.6.6 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0z" />
        </x-slot>

        <div class="accordionItemContent pb-2">

            <x-sub-menu-item :link="route('vendors.index')" :text="__('purchase::app.menu.vendor')" :permission="$purchaseViewVendorPermission != 'none' && $purchaseViewVendorPermission != ''" />

            <!-- NAV ITEM - PRODUCTS -->
            @if (in_array('products', user_modules()) && $sidebarUserPermissions['view_product'] != 5 && $sidebarUserPermissions['view_product'] != 'none')
                <x-sub-menu-item :link="route('purchase-products.index')" :text="__('purchase::app.menu.products')" />
            @endif

            @if (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none')
                <x-sub-menu-item :link="route('orders.index')" :text="__('app.menu.orders')" />
            @endif

            <!-- NAV ITEM - SALES SHIPMENTS -->
            <x-sub-menu-item :link="route($salesDoRouteName)" :text="$salesDoLabel" :permission="$canViewSalesDo" />

            <!-- NAV ITEM - ORDERS -->
            <x-sub-menu-item :link="route('purchase-order.index')" :text="__('purchase::app.menu.purchaseOrder')" :permission="$purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != ''" />

            <!-- NAV ITEM - DELIVERY ORDERS -->
            <x-sub-menu-item :link="route($grnRouteName)" :text="$grnLabel" :permission="$canViewGrn" />

            <!-- NAV ITEM - BILLS -->
            <x-sub-menu-item :link="route('bills.index')" :text="__('purchase::app.menu.bills')" :permission="$purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != ''" />

            <!-- NAV ITEM - PAYMENTS -->
            <x-sub-menu-item :link="route('vendor-payments.index')" :text="__('purchase::app.purchaseOrder.vendorPayments')" :permission="$purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != ''" />

            <x-sub-menu-item :link="route('vendor-credits.index')" :text="__('purchase::app.menu.vendorCredits')" :permission="$purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != ''" />

            <!-- NAV ITEM - INVENTORY -->
            <x-sub-menu-item :link="route('purchase-inventory.index')" :text="__('purchase::app.menu.inventory')" :permission="$purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != ''" />

            <!-- NAV ITEM - WAREHOUSE (master + stock; module warehouse + quyền Inventory hoặc quyền warehouse riêng) -->
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseMaster)
                <x-sub-menu-item :link="route('warehouse.index')" :text="__('warehouse::app.warehouses')" :permission="true" :active="request()->routeIs('warehouse.index', 'warehouse.show', 'warehouse.edit', 'warehouse.create')" />
            @endif
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                <x-sub-menu-item :link="route('warehouse.stock.index')" :text="__('warehouse::app.adjustStock')" :permission="true" :active="request()->routeIs('warehouse.stock.*', 'warehouse.transfer.*')" />
            @endif
            @if (in_array('warehouse', user_modules()) && $canSeeWarehouseStockUi)
                <x-sub-menu-item :link="route('warehouse.movements.index')" :text="__('warehouse::app.stockMovements')" :permission="true" :active="request()->routeIs('warehouse.movements.*')" />
            @endif
            <x-sub-menu-item :link="route('reports.index')" :text="__('purchase::app.menu.reports')" :permission="$purchaseViewOrderReportPermission != 'none' && $purchaseViewOrderReportPermission != '' && false" />

        </div>

    </x-menu-item>

@endif
